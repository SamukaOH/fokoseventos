<!-- DASHBOARD -->

<!-- KPIs linha 1 -->
<div class="dash-grid-top">
  <div class="dash-kpi">
    <img src="<?= APP_URL ?>/public/assets/img/logo.png" alt="Fokos Eventos" class="hero-logo">
    <div class="dash-kpi-top">
      <div class="dash-kpi-icon" style="background:rgba(255,214,0,.12);color:var(--yellow)"><i class="fa-solid fa-clipboard-list"></i></div>
    </div>
    <div class="dash-kpi-val" id="k-dem-ativas">–</div>
    <div class="dash-kpi-lbl">Demandas Ativas</div>
  </div>
  <div class="dash-kpi">
    <div class="dash-kpi-top">
      <div class="dash-kpi-icon" style="background:rgba(100,210,255,.1);color:#64d2ff"><i class="fa-solid fa-calendar-day"></i></div>
    </div>
    <div class="dash-kpi-val" id="k-hoje">–</div>
    <div class="dash-kpi-lbl">Eventos Hoje</div>
  </div>
  <div class="dash-kpi">
    <div class="dash-kpi-top">
      <div class="dash-kpi-icon" style="background:rgba(34,197,94,.1);color:#22c55e"><i class="fa-solid fa-arrow-trend-up"></i></div>
    </div>
    <div class="dash-kpi-val" id="k-rec-mes" style="color:#22c55e">–</div>
    <div class="dash-kpi-lbl">Receitas do Mês</div>
  </div>
</div>

<!-- KPIs linha 2 -->
<div class="dash-grid-mid">
  <div class="dash-kpi">
    <div class="dash-kpi-top">
      <div class="dash-kpi-icon" style="background:rgba(255,214,0,.1);color:var(--yellow)"><i class="fa-solid fa-scale-balanced"></i></div>
    </div>
    <div class="dash-kpi-val" id="k-lucro">–</div>
    <div class="dash-kpi-lbl">Lucro do Mês</div>
  </div>
  <div class="dash-kpi">
    <div class="dash-kpi-top">
      <div class="dash-kpi-icon" style="background:rgba(100,210,255,.1);color:#64d2ff"><i class="fa-solid fa-clock"></i></div>
    </div>
    <div class="dash-kpi-val" id="k-receber">–</div>
    <div class="dash-kpi-lbl">A Receber</div>
  </div>
  <div class="dash-kpi">
    <div class="dash-kpi-top">
      <div class="dash-kpi-icon" style="background:rgba(255,214,0,.1);color:var(--yellow)"><i class="fa-solid fa-trophy"></i></div>
    </div>
    <div class="dash-kpi-val" id="k-rec-ano" style="color:var(--yellow)">–</div>
    <div class="dash-kpi-lbl">Receitas do Ano</div>
  </div>
  <div class="dash-kpi">
    <div class="dash-kpi-top">
      <div class="dash-kpi-icon" style="background:rgba(34,197,94,.1);color:#22c55e"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    <div class="dash-kpi-val" id="k-fin">–</div>
    <div class="dash-kpi-lbl">Finalizados</div>
  </div>
</div>

<!-- Gráfico + Próximos eventos -->
<!-- Próximos Eventos + Próximas Retiradas lado a lado, mesmo tamanho -->
<div class="dash-section" style="grid-template-columns:1fr 1fr">
  <div class="dash-card">
    <div class="dash-card-header"><i class="fa-solid fa-calendar"></i> Próximos Eventos</div>
    <div id="ev-lista" style="max-height:300px;overflow-y:auto">
      <div style="padding:30px;text-align:center;color:var(--text3);font-size:13px"><i class="fa-solid fa-spinner fa-spin"></i></div>
    </div>
  </div>
  <div class="dash-card">
    <div class="dash-card-header"><i class="fa-solid fa-rotate-left"></i> Próximas Retiradas</div>
    <div id="ret-lista" style="max-height:300px;overflow-y:auto">
      <div style="padding:20px 0;text-align:center;color:var(--text3);font-size:13px">–</div>
    </div>
  </div>
</div>

<div class="dash-card" id="alerta-card" style="display:none;border-color:rgba(255,92,81,.3);margin-bottom:16px">
  <div style="padding:14px 16px">
    <div style="font-size:12px;font-weight:700;color:var(--danger);margin-bottom:4px"><i class="fa-solid fa-triangle-exclamation"></i> Atenção</div>
    <div id="alerta-txt" style="font-size:12px;color:var(--text3)"></div>
  </div>
</div>

<!-- Últimas demandas -->
<div class="dash-section-bottom" style="grid-template-columns:1fr">
  <div class="dash-card">
    <div class="dash-card-header"><i class="fa-solid fa-history"></i> Últimas Demandas</div>
    <div id="ult-lista" style="overflow-x:auto">
      <div style="padding:30px;text-align:center;color:var(--text3);font-size:13px"><i class="fa-solid fa-spinner fa-spin"></i></div>
    </div>
  </div>
</div>

<script>
// Garantir variáveis globais caso app.js ainda não carregou
if (typeof APP_URL === 'undefined') APP_URL = document.querySelector('meta[name="app-url"]')?.content || '';
if (typeof escHtml  === 'undefined') escHtml  = function(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); };
</script>
<script>
function verUltDemanda(i) {
  var dm = (window._ULTS||[])[i]; if (!dm) return;
  document.getElementById('ud-titulo').textContent = dm.titulo || '—';
  document.getElementById('ud-body').innerHTML =
    '<div class="detail-grid">'
    +'<div class="detail-item"><div class="detail-item-lbl">Status</div><div class="detail-item-val">'+badge(dm.status)+'</div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">Data do Evento</div><div class="detail-item-val">'+dBR(dm.data_evento)+(dm.horario?' às '+String(dm.horario).substring(0,5):'')+'</div></div>'
    +'<div class="detail-item full"><div class="detail-item-lbl">Cliente</div><div class="detail-item-val">'+esc(dm.cliente_nome||'—')+'</div></div>'
    +(dm.letreiro_texto?'<div class="detail-item full"><div class="detail-item-lbl">Letreiro</div><div class="detail-item-val" style="font-family:\'Bebas Neue\',sans-serif;font-size:20px;color:var(--yellow);letter-spacing:.05em">'+esc(dm.letreiro_texto)+'</div></div>':'')
    +(dm.local_evento?'<div class="detail-item full"><div class="detail-item-lbl">Local</div><div class="detail-item-val">'+esc(dm.local_evento)+'</div></div>':'')
    +(dm.motorista_nome?'<div class="detail-item"><div class="detail-item-lbl">Motorista</div><div class="detail-item-val">'+esc(dm.motorista_nome)+'</div></div>':'')
    +(dm.valor_total>0?'<div class="detail-item"><div class="detail-item-lbl">Valor</div><div class="detail-item-val" style="color:var(--green);font-weight:700">R$ '+parseFloat(dm.valor_total).toLocaleString('pt-BR',{minimumFractionDigits:2})+'</div></div>':'')
    +'</div>';
  Modal.open('modal-ult-demanda');
}
var SC = {pendente:'#888',preparacao:'#3b82f6',em_rota:'#64d2ff',em_retirada:'#f97316',entregue:'#22c55e',devolvido:'#64d2ff',finalizado:'#555',cancelado:'#444'};
var SL = {pendente:'Pendente',preparacao:'Preparação',em_rota:'Em Rota',em_retirada:'Em Retirada',entregue:'Entregue',devolvido:'Devolvido',finalizado:'Finalizado',cancelado:'Cancelado'};

function R(v){ return 'R$\u00a0'+parseFloat(v||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function set(id,v){ var e=document.getElementById(id); if(e) e.textContent=v; }
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function dBR(s){ if(!s) return '—'; var p=String(s).substring(0,10).split('-'); return p.length===3?p[2]+'/'+p[1]+'/'+p[0]:'—'; }
function badge(st){ var c=SC[st]||'#888',l=SL[st]||st; return '<span class="sbadge" style="background:'+c+'20;color:'+c+';border:1px solid '+c+'44">'+l+'</span>'; }

async function loadDash() {
  try {
    var r = await fetch(APP_URL+'/api/dashboard', {headers:{'X-Requested-With':'XMLHttpRequest'}});
    if (!r.ok) throw new Error('HTTP '+r.status);
    var d = await r.json();
    if (d.erro) throw new Error(d.erro);

    // KPIs operacionais
    set('k-dem-ativas', d.demandasAtivas   != null ? d.demandasAtivas   : '–');
    set('k-hoje',       d.demandasHoje     != null ? d.demandasHoje     : '–');
    set('k-fin',        d.demandasFinalizadas != null ? d.demandasFinalizadas : '–');

    // KPIs financeiros
    var fm = d.finMes || {}, fa = d.finAno || {};
    set('k-rec-mes', fm.receitas  != null ? R(fm.receitas)  : '–');
    set('k-lucro',   d.lucroMes   != null ? R(d.lucroMes)   : '–');
    set('k-receber', fm.a_receber != null ? R(fm.a_receber) : '–');
    set('k-rec-ano', fa.receitas  != null ? R(fa.receitas)  : '–');

    var el = document.getElementById('k-lucro');
    if (el && d.lucroMes != null) el.style.color = d.lucroMes >= 0 ? '#22c55e' : '#ff3b30';

    // Gráfico

    // Próximos eventos
    var evEl = document.getElementById('ev-lista');
    var evs  = d.proximosEventos || [];
    if (!evs.length) {
      evEl.innerHTML = '<div style="padding:30px;text-align:center;color:var(--text3);font-size:13px">Nenhum evento próximo.</div>';
    } else {
      evEl.innerHTML = evs.map(function(ev){
        var dt  = (ev.data_evento||'').substring(0,10);
        var dia = dt ? dt.substring(8,10) : '–';
        var mes = dt ? dt.substring(5,7) : '';
        return '<div class="ev-item">'
          +'<div class="ev-dia"><div class="ev-dia-num">'+dia+'</div><div class="ev-dia-mes">'+mes+'</div></div>'
          +'<div class="ev-info"><div class="ev-titulo">'+esc(ev.titulo||'—')+'</div>'
          +'<div class="ev-cliente">'+esc(ev.cliente_nome||'—')+(ev.horario?' · '+String(ev.horario).substring(0,5):'')+'</div></div>'
          +badge(ev.status)+'</div>';
      }).join('');
    }

    // Próximas retiradas
    var retEl = document.getElementById('ret-lista');
    var rets  = d.proximasRetiradas || [];
    if (!rets.length) {
      retEl.innerHTML = '<div style="padding:24px 16px;text-align:center;color:var(--text3);font-size:13px">Nenhuma retirada agendada.</div>';
    } else {
      retEl.innerHTML = rets.map(function(r){
        var dt = r.data_retirada ? r.data_retirada.split('-') : null;
        var meses = ['JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ'];
        return '<div class="ev-item">'
          +'<div class="ev-dia"><div class="ev-dia-num">'+(dt?parseInt(dt[2],10):'–')+'</div><div class="ev-dia-mes">'+(dt?meses[parseInt(dt[1],10)-1]:'')+'</div></div>'
          +'<div class="ev-info">'
          +'<div class="ev-titulo">'+esc(r.titulo)+'</div>'
          +'<div class="ev-cliente">'+esc(r.cliente_nome||'—')+(r.horario_retirada?' · '+String(r.horario_retirada).substring(0,5):'')+(r.motorista_nome?' · '+esc(r.motorista_nome):'')+'</div>'
          +'</div>'
          +badge(r.status)
          +'</div>';
      }).join('');
    }

    // Últimas demandas
    var ultEl = document.getElementById('ult-lista');
    var ults  = d.ultimasDemandas || [];
    if (!ults.length) {
      ultEl.innerHTML = '<div style="padding:30px;text-align:center;color:var(--text3);font-size:13px">Nenhuma demanda.</div>';
    } else {
      window._ULTS = ults;
      var html = '<table class="data-table"><thead><tr><th>Demanda</th><th>Status</th><th style="width:28px"></th></tr></thead><tbody>';
      ults.forEach(function(dm, i){
        html += '<tr class="row-click" onclick="verUltDemanda('+i+')">'
          +'<td><div style="font-weight:500">'+esc(dm.titulo||'—')+'</div>'
          +'<div style="color:var(--text3);font-size:11px">'+esc(dm.cliente_nome||'—')+' · '+dBR(dm.data_evento)+'</div></td>'
          +'<td>'+badge(dm.status)+'</td>'
          +'<td><i class="fa-solid fa-chevron-right"></i></td>'
          +'</tr>';
      });
      html += '</tbody></table>';
      ultEl.innerHTML = html;
    }

    // Alerta
    if ((d.semLancamento||0) > 0) {
      document.getElementById('alerta-card').style.display = 'block';
      set('alerta-txt', d.semLancamento+' demanda(s) finalizada(s) sem lançamento financeiro nos últimos 30 dias.');
    }

  } catch(e) {
    console.error('Dashboard erro:', e.message);
    // Mostrar erro visível para debug
    var errEl = document.getElementById('k-dem-ativas');
    if(errEl) errEl.textContent = 'ERR';
    document.getElementById('ev-lista').innerHTML = '<div style="padding:16px;color:#ff3b30;font-size:12px">Erro API: '+e.message+'</div>';
  }
}


window.addEventListener('load', function(){
  if (typeof APP_URL === 'undefined') {
    window.APP_URL = document.querySelector('meta[name="app-url"]')?.content || '';
  }
  loadDash();
  setInterval(loadDash, 120000);
});
</script>
<!-- Modal detalhes (últimas demandas) -->
<div class="modal-overlay" id="modal-ult-demanda">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fa-solid fa-clipboard-list" style="color:var(--yellow)"></i> <span id="ud-titulo">Demanda</span></h3>
      <button class="modal-close" data-close-modal="modal-ult-demanda"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="ud-body"></div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-ult-demanda">Fechar</button>
      <a class="btn btn-primary" href="<?= APP_URL ?>/demandas"><i class="fa-solid fa-arrow-right"></i> Ir para Demandas</a>
    </div>
  </div>
</div>
