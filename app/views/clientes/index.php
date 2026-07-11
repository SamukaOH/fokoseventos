<?php
$clientes = Database::fetchAll(
    "SELECT c.*, COUNT(d.id) as total_demandas,
            SUM(CASE WHEN d.status NOT IN ('finalizado','cancelado') THEN 1 ELSE 0 END) as demandas_ativas
     FROM clientes c
     LEFT JOIN demandas d ON d.cliente_id = c.id
     GROUP BY c.id ORDER BY c.nome"
);
$clientesJson = json_encode($clientes, JSON_UNESCAPED_UNICODE|JSON_HEX_APOS);
?>

<div style="display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:24px">
  <button class="btn btn-primary" onclick="Modal.open('modal-novo-cliente')">
    <i class="fa-solid fa-plus"></i> Novo Cliente
  </button>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(255,214,0,.12);color:var(--yellow)"><i class="fa-solid fa-building-user"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= count($clientes) ?></div><div class="stat-card-label">Total de Clientes</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(59,130,246,.12);color:#3b82f6"><i class="fa-solid fa-clipboard-list"></i></div>
    <div class="stat-card-info">
      <div class="stat-card-value"><?= array_sum(array_column($clientes,'total_demandas')) ?></div>
      <div class="stat-card-label">Total de Demandas</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(34,197,94,.12);color:#22c55e"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-card-info">
      <div class="stat-card-value"><?= count(array_filter($clientes, fn($c) => $c['demandas_ativas'] > 0)) ?></div>
      <div class="stat-card-label">Com demanda ativa</div>
    </div>
  </div>
</div>

<!-- Busca -->
<div style="margin-bottom:16px">
  <div class="search-wrap" style="max-width:360px">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" class="search-input" placeholder="Buscar cliente..." id="busca-cliente" oninput="filtrarClientes()">
  </div>
</div>

<!-- Tabela -->
<div class="table-wrap">
  <table class="table" id="tabela-clientes">
    <thead>
      <tr>
        <th>Nome</th>
        <th class="hide-mobile">Demandas</th>
        <th style="width:28px"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($clientes as $c): ?>
      <tr class="cliente-row row-click" onclick="verCliente(<?= $c['id'] ?>)">
        <td>
          <strong><?= htmlspecialchars($c['nome']) ?></strong>
          <?php if($c['email']): ?><br><small style="color:var(--text3)"><?= htmlspecialchars($c['email']) ?></small><?php endif; ?>
        </td>
        <td class="hide-mobile">
          <span style="font-weight:600"><?= $c['total_demandas'] ?></span>
          <?php if($c['demandas_ativas'] > 0): ?>
            <span class="badge badge-em_rota" style="margin-left:6px"><?= $c['demandas_ativas'] ?> ativas</span>
          <?php endif; ?>
        </td>
        <td><i class="fa-solid fa-chevron-right"></i></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($clientes)): ?>
      <tr><td colspan="3" style="text-align:center;padding:40px;color:var(--text3)">Nenhum cliente cadastrado</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Ver Cliente -->
<div class="modal-overlay" id="modal-ver-cliente">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fa-solid fa-building-user" style="color:var(--yellow)"></i> <span id="vc-nome">Cliente</span></h3>
      <button class="modal-close" data-close-modal="modal-ver-cliente"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="vc-body"></div>
    <div class="modal-footer" style="justify-content:space-between">
      <button class="btn btn-danger btn-sm" id="vc-btn-del"><i class="fa-solid fa-trash"></i> Excluir</button>
      <div style="display:flex;gap:8px">
        <button class="btn btn-ghost" data-close-modal="modal-ver-cliente">Fechar</button>
        <button class="btn btn-primary" id="vc-btn-edit"><i class="fa-solid fa-pen"></i> Editar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Novo/Editar Cliente -->
<div class="modal-overlay" id="modal-novo-cliente">
  <div class="modal">
    <div class="modal-header">
      <h3 id="cliente-modal-title">Novo Cliente</h3>
      <button class="modal-close" data-close-modal="modal-novo-cliente"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="cliente-id">
      <div class="form-row">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Nome *</label>
          <input type="text" class="form-control" id="cliente-nome" placeholder="Nome do cliente ou empresa">
        </div>
        <div class="form-group">
          <label class="form-label">E-mail</label>
          <input type="email" class="form-control" id="cliente-email" placeholder="email@exemplo.com">
        </div>
        <div class="form-group">
          <label class="form-label">CPF / CNPJ</label>
          <input type="text" class="form-control" id="cliente-cpf" placeholder="000.000.000-00">
        </div>
        <div class="form-group">
          <label class="form-label">Telefone</label>
          <input type="text" class="form-control" id="cliente-telefone" placeholder="(11) 99999-0000">
        </div>
        <div class="form-group">
          <label class="form-label">WhatsApp</label>
          <input type="text" class="form-control" id="cliente-whatsapp" placeholder="(11) 99999-0000">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Endereço</label>
          <input type="text" class="form-control" id="cliente-endereco" placeholder="Rua, número, bairro, cidade">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Observações</label>
          <textarea class="form-control" id="cliente-obs" rows="2" placeholder="Notas sobre o cliente..."></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-novo-cliente">Cancelar</button>
      <button class="btn btn-primary" id="btn-salvar-cliente" onclick="salvarCliente()">
        <i class="fa-solid fa-check"></i> Salvar
      </button>
    </div>
  </div>
</div>

<script>
var CLIENTES = <?= $clientesJson ?>;

function verCliente(id) {
  var c = CLIENTES.find(function(x){ return x.id == id; });
  if (!c) return;
  var wa = c.whatsapp ? String(c.whatsapp).replace(/\D/g,'') : '';
  document.getElementById('vc-nome').textContent = c.nome;
  document.getElementById('vc-body').innerHTML =
    '<div class="detail-grid">'
    +'<div class="detail-item"><div class="detail-item-lbl">Telefone</div><div class="detail-item-val">'+escHtml(c.telefone||'—')+'</div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">WhatsApp</div><div class="detail-item-val">'+(wa?'<a href="https://wa.me/55'+wa+'" target="_blank"><i class="fa-brands fa-whatsapp"></i> '+escHtml(c.whatsapp)+'</a>':'—')+'</div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">E-mail</div><div class="detail-item-val">'+escHtml(c.email||'—')+'</div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">CPF / CNPJ</div><div class="detail-item-val">'+escHtml(c.cpf_cnpj||'—')+'</div></div>'
    +'<div class="detail-item full"><div class="detail-item-lbl">Endereço</div><div class="detail-item-val">'+escHtml(c.endereco||'—')+'</div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">Demandas</div><div class="detail-item-val">'+(c.total_demandas||0)+(c.demandas_ativas>0?' <span class="badge badge-em_rota">'+c.demandas_ativas+' ativas</span>':'')+'</div></div>'
    +(c.observacoes?'<div class="detail-item full"><div class="detail-item-lbl">Observações</div><div class="detail-item-val">'+escHtml(c.observacoes)+'</div></div>':'')
    +'</div>';
  document.getElementById('vc-btn-edit').onclick = function(){ Modal.close('modal-ver-cliente'); editarCliente(c.id); };
  document.getElementById('vc-btn-del').onclick  = function(){ Modal.close('modal-ver-cliente'); deletarCliente(c.id, c.nome); };
  Modal.open('modal-ver-cliente');
}

function filtrarClientes() {
  var busca = document.getElementById('busca-cliente').value.toLowerCase();
  document.querySelectorAll('.cliente-row').forEach(function(row) {
    row.style.display = row.textContent.toLowerCase().includes(busca) ? '' : 'none';
  });
}

function editarCliente(id) {
  Api.get('/api/clientes/' + id).then(function(data) {
    if (!data) return;
    var c = data.cliente;
    document.getElementById('cliente-id').value       = c.id;
    document.getElementById('cliente-nome').value     = c.nome || '';
    document.getElementById('cliente-email').value    = c.email || '';
    document.getElementById('cliente-cpf').value      = c.cpf_cnpj || '';
    document.getElementById('cliente-telefone').value = c.telefone || '';
    document.getElementById('cliente-whatsapp').value = c.whatsapp || '';
    document.getElementById('cliente-endereco').value = c.endereco || '';
    document.getElementById('cliente-obs').value      = c.observacoes || '';
    document.getElementById('cliente-modal-title').textContent = 'Editar Cliente';
    Modal.open('modal-novo-cliente');
  });
}

async function salvarCliente() {
  var nome = document.getElementById('cliente-nome').value.trim();
  if (!nome) return Toast.warning('Atenção', 'Nome é obrigatório.');
  var btn = document.getElementById('btn-salvar-cliente');
  setLoadingBtn(btn, true, 'Salvando...');
  var fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('nome',      document.getElementById('cliente-nome').value);
  fd.append('email',     document.getElementById('cliente-email').value);
  fd.append('cpf_cnpj',  document.getElementById('cliente-cpf').value);
  fd.append('telefone',  document.getElementById('cliente-telefone').value);
  fd.append('whatsapp',  document.getElementById('cliente-whatsapp').value);
  fd.append('endereco',  document.getElementById('cliente-endereco').value);
  fd.append('observacoes', document.getElementById('cliente-obs').value);
  var id = document.getElementById('cliente-id').value;
  if (id) fd.append('id', id);
  try {
    await Api.post('/api/clientes', fd);
    Toast.success('Salvo!', 'Cliente salvo com sucesso.');
    Modal.close('modal-novo-cliente');
    setTimeout(function() { location.reload(); }, 800);
  } catch(e) {
    Toast.error('Erro', e.message);
  } finally {
    setLoadingBtn(btn, false, '<i class="fa-solid fa-check"></i> Salvar');
  }
}

async function deletarCliente(id, nome) {
  Modal.confirm('Excluir cliente', 'Deseja excluir "' + nome + '"? As demandas vinculadas não serão excluídas.', async function() {
    var fd = new FormData();
    fd.append('_csrf', CSRF);
    try {
      await Api.post('/api/clientes/' + id + '/delete', fd);
      Toast.success('Excluído', 'Cliente removido.');
      setTimeout(function() { location.reload(); }, 800);
    } catch(e) { Toast.error('Erro', e.message); }
  });
}
</script>
