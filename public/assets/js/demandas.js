// ============================================================
// FOKOS EVENTOS — Demandas Module
// ============================================================

window.DemandasModule = (() => {
  let viewMode = 'kanban'; // kanban | list

  const statusCols = [
    { key:'pendente',   label:'Pendente',       color:'#f59e0b' },
    { key:'preparacao', label:'Em Preparação',  color:'#3b82f6' },
    { key:'em_rota',    label:'Em Rota',        color:'#a855f7' },
    { key:'entregue',   label:'Entregue',       color:'#06b6d4' },
    { key:'montado',    label:'Montado',        color:'#10b981' },
    { key:'finalizado', label:'Finalizado',     color:'#22c55e' },
    { key:'cancelado',  label:'Cancelado',      color:'#ef4444' },
  ];

  async function load(filters = {}) {
    const params = new URLSearchParams(filters).toString();
    const data   = await Api.get('/api/demandas' + (params ? '?'+params : ''));
    if (!data) return;
    if (viewMode === 'kanban') renderKanban(data.demandas);
    else renderList(data.demandas);
  }

  function renderKanban(demandas) {
    const board = document.getElementById('kanban-board');
    if (!board) return;

    const groups = {};
    statusCols.forEach(s => groups[s.key] = []);
    demandas.forEach(d => { if (groups[d.status]) groups[d.status].push(d); });

    board.innerHTML = statusCols.map(col => `
      <div class="kanban-col" data-status="${col.key}">
        <div class="kanban-col-header">
          <div class="kanban-col-title">
            <div class="kanban-dot" style="background:${col.color}"></div>
            ${col.label}
          </div>
          <span class="kanban-count">${groups[col.key].length}</span>
        </div>
        <div class="kanban-cards" id="col-${col.key}">
          ${groups[col.key].map(d => kanbanCard(d)).join('') || `<div style="padding:20px;text-align:center;color:var(--text3);font-size:12px">Vazio</div>`}
        </div>
      </div>`).join('');

    // Click on card
    board.querySelectorAll('.kanban-card').forEach(el => {
      el.addEventListener('click', () => abrirDetalhe(el.dataset.id));
    });
  }

  function kanbanCard(d) {
    const prioBorder = d.prioridade === 'urgente' ? 'border-left:3px solid #ef4444;' : d.prioridade === 'alta' ? 'border-left:3px solid #f59e0b;' : '';
    return `
      <div class="kanban-card" data-id="${d.id}" style="${prioBorder}">
        <div class="kanban-card-title">${d.titulo}</div>
        <div class="kanban-card-meta">
          ${badgePrio(d.prioridade)}
          <span class="kanban-card-date"><i class="fa-solid fa-calendar-day"></i>${dataBR(d.data_evento)}</span>
        </div>
        <div style="margin-top:8px;display:flex;align-items:center;gap:6px">
          ${d.cliente_nome ? `<span style="font-size:11px;color:var(--text2)"><i class="fa-solid fa-building" style="margin-right:3px"></i>${d.cliente_nome}</span>` : ''}
          ${d.motorista_nome ? `<span style="font-size:11px;color:var(--text2);margin-left:auto"><i class="fa-solid fa-truck-fast" style="margin-right:3px"></i>${d.motorista_nome}</span>` : ''}
        </div>
      </div>`;
  }

  function renderList(demandas) {
    const tbody = document.getElementById('demandas-tbody');
    if (!tbody) return;
    if (!demandas.length) {
      tbody.innerHTML = '<tr><td colspan="7" style="padding:40px;text-align:center;color:var(--text2)"><i class="fa-solid fa-inbox" style="font-size:32px;display:block;margin-bottom:12px;opacity:.3"></i>Nenhuma demanda encontrada</td></tr>';
      return;
    }
    tbody.innerHTML = demandas.map(d => `
      <tr onclick="DemandasModule.abrirDetalhe(${d.id})" style="cursor:pointer">
        <td><strong style="font-size:14px">${d.titulo}</strong></td>
        <td><span style="font-size:13px">${d.cliente_nome||'–'}</span></td>
        <td>${badgeStatus(d.status)}</td>
        <td>${badgePrio(d.prioridade)}</td>
        <td style="font-size:13px">${d.motorista_nome||'<span style="color:var(--text3)">–</span>'}</td>
        <td style="font-size:13px;color:var(--text2)">${dataBR(d.data_evento)} ${d.horario ? d.horario.substring(0,5) : ''}</td>
        <td>
          <div style="display:flex;gap:6px">
            <button class="btn btn-secondary btn-sm btn-icon" onclick="event.stopPropagation();DemandasModule.abrirDetalhe(${d.id})" title="Ver"><i class="fa-solid fa-eye"></i></button>
            <button class="btn btn-danger btn-sm btn-icon" onclick="event.stopPropagation();DemandasModule.deletar(${d.id},'${d.titulo}')" title="Excluir"><i class="fa-solid fa-trash"></i></button>
          </div>
        </td>
      </tr>`).join('');
  }

  async function abrirDetalhe(id) {
    const modal = document.getElementById('modal-demanda-detalhe');
    if (!modal) return;
    const body = modal.querySelector('.modal-body');
    body.innerHTML = '<div style="text-align:center;padding:40px"><div class="loader-ring" style="margin:0 auto"></div></div>';
    Modal.open('modal-demanda-detalhe');

    const data = await Api.get(`/api/demandas/${id}`);
    if (!data?.demanda) { body.innerHTML = '<p style="color:var(--danger);padding:20px">Erro ao carregar.</p>'; return; }
    const d = data.demanda;

    body.innerHTML = `
      <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px">
        <div>
          <h2 style="font-size:20px;font-weight:800;margin-bottom:4px">${d.titulo}</h2>
          <div style="display:flex;gap:8px;flex-wrap:wrap">${badgeStatus(d.status)}${badgePrio(d.prioridade)}</div>
        </div>
        <div style="display:flex;gap:8px">
          <a href="https://wa.me/55${(d.whatsapp||'').replace(/\D/g,'')}" target="_blank" class="btn btn-success btn-sm"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
          <a href="https://maps.google.com/?q=${encodeURIComponent(d.endereco||'')}" target="_blank" class="btn btn-secondary btn-sm"><i class="fa-solid fa-map-location-dot"></i> Maps</a>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
        ${infoBox('fa-building','Cliente',d.cliente_nome||'–')}
        ${infoBox('fa-user','Responsável',d.responsavel||'–')}
        ${infoBox('fa-phone','Telefone',d.telefone||'–')}
        ${infoBox('fa-calendar','Data',dataBR(d.data_evento)+' '+( d.horario?d.horario.substring(0,5):''))}
        ${infoBox('fa-location-dot','Endereço',d.endereco||'–')}
        ${infoBox('fa-truck-fast','Motorista',d.motorista_nome||'Não atribuído')}
      </div>

      ${d.observacoes ? `<div class="info-box" style="margin-bottom:12px"><div class="info-label"><i class="fa-solid fa-note-sticky"></i> Observações</div><div style="font-size:13px;color:var(--text2)">${d.observacoes}</div></div>` : ''}

      ${d.materiais.length ? `
        <div style="margin-bottom:16px">
          <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text2);margin-bottom:10px"><i class="fa-solid fa-boxes-stacked"></i> Materiais</div>
          <div style="display:flex;flex-direction:column;gap:6px">
            ${d.materiais.map(m => `<div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--bg3);border-radius:8px;font-size:13px"><span>${m.produto_nome}</span><span style="color:var(--yellow);font-weight:600">×${m.quantidade}</span></div>`).join('')}
          </div>
        </div>` : ''}

      ${d.fotos.length ? `
        <div>
          <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text2);margin-bottom:10px"><i class="fa-solid fa-images"></i> Fotos</div>
          <div style="display:flex;flex-wrap:wrap;gap:8px">
            ${d.fotos.map(f => `<img src="${APP_URL}/public/assets/uploads/${f.arquivo}" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid var(--border);cursor:pointer" onclick="window.open(this.src,'_blank')">`).join('')}
          </div>
        </div>` : ''}

      <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
        <div style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text2);margin-bottom:10px">Atualizar Status</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          ${statusCols.map(s => `
            <button class="btn btn-sm btn-secondary ${d.status===s.key?'active':''}" 
              style="${d.status===s.key?'background:var(--yellow-glow);color:var(--yellow);border-color:rgba(255,214,0,.3)':''}"
              onclick="DemandasModule.mudarStatus(${d.id},'${s.key}',this)">${s.label}</button>`).join('')}
        </div>
      </div>`;
  }

  function infoBox(icon, label, val) {
    return `<div style="background:var(--bg3);border-radius:10px;padding:12px 14px">
      <div style="font-size:11px;color:var(--text3);margin-bottom:4px;display:flex;align-items:center;gap:6px"><i class="fa-solid ${icon}"></i>${label}</div>
      <div style="font-size:14px;font-weight:600">${val}</div>
    </div>`;
  }

  async function mudarStatus(id, status, btn) {
    const fd = new FormData();
    fd.append('_csrf', CSRF);
    fd.append('id', id);
    fd.append('status', status);
    setLoadingBtn(btn, true);
    const data = await Api.postForm('/api/demandas/status', fd);
    setLoadingBtn(btn, false, statusLabel(status));
    if (data?.sucesso) {
      Toast.success('Status atualizado!');
      btn.closest('.modal-body').querySelectorAll('button').forEach(b => {
        b.style.background = b.style.color = b.style.borderColor = '';
      });
      btn.style.background = 'var(--yellow-glow)';
      btn.style.color = 'var(--yellow)';
      btn.style.borderColor = 'rgba(255,214,0,.3)';
      load();
    } else Toast.error('Erro', data?.erro || 'Tente novamente.');
  }

  async function deletar(id, titulo) {
    Modal.confirm('Excluir demanda', `Tem certeza que deseja excluir "${titulo}"? Esta ação não pode ser desfeita.`, async () => {
      const fd = new FormData();
      fd.append('_csrf', CSRF);
      const data = await Api.postForm(`/api/demandas/${id}/delete`, fd);
      if (data?.sucesso) { Toast.success('Excluída!'); load(); }
      else Toast.error('Erro', data?.erro);
    });
  }

  function setView(mode) {
    viewMode = mode;
    const kanbanEl = document.getElementById('kanban-board');
    const tableEl  = document.getElementById('demandas-table');
    const btnK = document.getElementById('btn-view-kanban');
    const btnL = document.getElementById('btn-view-list');
    if (mode === 'kanban') {
      kanbanEl?.style && (kanbanEl.style.display = '');
      tableEl?.style  && (tableEl.style.display  = 'none');
      btnK?.classList.add('active');
      btnL?.classList.remove('active');
    } else {
      kanbanEl?.style && (kanbanEl.style.display = 'none');
      tableEl?.style  && (tableEl.style.display  = '');
      btnK?.classList.remove('active');
      btnL?.classList.add('active');
    }
    load();
  }

  return { load, abrirDetalhe, mudarStatus, deletar, setView };
})();
