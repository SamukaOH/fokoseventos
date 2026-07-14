<?php
// Preços padrão pré-inseridos via PHP caso tabela vazia
$tiposPHP = Database::fetchAll("SELECT * FROM letreiros_tipos ORDER BY id");
$tamsPHP   = Database::fetchAll("SELECT * FROM letreiros_tamanhos ORDER BY altura_cm");
?>

<!-- FINANCEIRO -->
<div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:24px;flex-wrap:wrap;gap:12px">
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <button class="btn btn-ghost btn-sm" id="btn-open-precos">
      <i class="fa-solid fa-tags"></i> Tabela de Preços
    </button>
    <button class="btn btn-primary btn-sm" id="btn-open-lancamento">
      <i class="fa-solid fa-plus"></i> Novo Lançamento
    </button>
  </div>
</div>

<!-- Filtros período -->
<div style="display:flex;gap:10px;align-items:center;margin-bottom:20px;flex-wrap:wrap">
  <div style="display:flex;border:1px solid var(--border);border-radius:10px;overflow:hidden">
    <button class="btn-modo active" id="btn-modo-mensal" id="btn-modo-mensal">Mensal</button>
    <button class="btn-modo" id="btn-modo-anual" id="btn-modo-anual">Anual</button>
  </div>
  <div id="filtro-mensal" style="display:flex;gap:8px">
    <input type="month" class="form-control" id="filtro-mes" value="<?= date('Y-m') ?>" id="filtro-periodo">
  </div>
  <div id="filtro-anual" style="display:none">
    <select class="form-control" id="filtro-ano" style="width:120px" id="filtro-periodo">
      <?php for($y=date('Y'); $y>=2023; $y--): ?>
      <option value="<?= $y ?>" <?= $y==date('Y')?'selected':'' ?>><?= $y ?></option>
      <?php endfor; ?>
    </select>
  </div>
</div>

<!-- Cards de resumo -->
<div class="cards-grid" style="margin-bottom:20px">
  <div class="stat-card green">
    <div class="stat-card-top"><div class="stat-icon green"><i class="fa-solid fa-arrow-trend-up"></i></div></div>
    <div class="stat-value" id="fin-receitas">R$ 0</div>
    <div class="stat-label">Receitas</div>
  </div>
  <div class="stat-card" style="border-color:rgba(255,59,48,.3)">
    <div class="stat-card-top"><div class="stat-icon" style="background:rgba(255,59,48,.1);color:#ff3b30"><i class="fa-solid fa-arrow-trend-down"></i></div></div>
    <div class="stat-value" id="fin-despesas" style="color:#ff3b30">R$ 0</div>
    <div class="stat-label">Despesas</div>
  </div>
  <div class="stat-card yellow">
    <div class="stat-card-top"><div class="stat-icon yellow"><i class="fa-solid fa-scale-balanced"></i></div></div>
    <div class="stat-value" id="fin-lucro">R$ 0</div>
    <div class="stat-label">Lucro Líquido</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-top"><div class="stat-icon blue"><i class="fa-solid fa-clock"></i></div></div>
    <div class="stat-value" id="fin-pendentes">0</div>
    <div class="stat-label">A Receber</div>
  </div>
</div>

<!-- Gráficos -->
<div class="rel-grid-2">
  <div class="card" style="padding:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3)">Evolução 12 meses</div>
    </div>
    <canvas id="chart-evolucao" height="120"></canvas>
  </div>
  <div class="card" style="padding:20px">
    <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:16px;display:flex;align-items:center;gap:8px">
      <i class="fa-solid fa-chart-pie" style="color:var(--yellow)"></i> Receitas × Despesas (12 meses)
    </div>
    <div id="pizza-wrap" style="position:relative;min-height:200px;max-width:340px;margin:0 auto">
      <canvas id="chart-pizza"></canvas>
    </div>
  </div>
</div>

<!-- Tabela de lançamentos -->
<div class="card">
  <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
    <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3)">Lançamentos</div>
    <span id="fin-count" style="font-size:12px;color:var(--text3)">—</span>
  </div>
  <div id="fin-tabela" style="overflow-x:auto">
    <div style="padding:40px;text-align:center;color:var(--text3)"><i class="fa-solid fa-spinner fa-spin"></i> Carregando...</div>
  </div>
</div>

<!-- ═══ MODAL NOVO LANÇAMENTO ═══ -->
<div class="modal-overlay" id="modal-lancamento">
  <div class="modal" style="max-width:640px">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fa-solid fa-plus" style="color:var(--yellow)"></i> Novo Lançamento</h3>
      <button class="modal-close" data-close-modal="modal-lancamento"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="max-height:80vh;overflow-y:auto">

      <!-- Tipo -->
      <div class="tipo-seg">
        <button class="btn-tipo active" id="btn-tipo-receita" onclick="setTipo('receita')" style="--c:#3DDC84">
          <i class="fa-solid fa-arrow-trend-up"></i> Receita
        </button>
        <button class="btn-tipo" id="btn-tipo-despesa" onclick="setTipo('despesa')" style="--c:#FF5C51">
          <i class="fa-solid fa-arrow-trend-down"></i> Despesa
        </button>
      </div>

      <!-- Vincular demanda -->
      <div class="form-group">
        <label class="form-label">Demanda (opcional)</label>
        <select class="form-control" id="lan-demanda" onchange="onDemandaChange()">
          <option value="">— Lançamento avulso —</option>
        </select>
      </div>

      <!-- Botão orçamento automático -->
      <div id="btn-orcamento-wrap" style="display:none">
        <button class="btn btn-ghost btn-sm" onclick="calcularOrcamento()" style="width:100%;border-style:dashed">
          <i class="fa-solid fa-calculator"></i> Gerar orçamento automático pelos letreiros
        </button>
      </div>

      <!-- Linhas do orçamento -->
      <div id="linhas-orcamento" style="display:none;background:var(--bg3);border-radius:10px;padding:14px;font-size:13px">
        <div style="font-weight:600;margin-bottom:8px;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--text3)">Itens do orçamento</div>
        <div id="linhas-orcamento-itens"></div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Descrição *</label>
          <input type="text" class="form-control" id="lan-descricao" placeholder="Ex: Evento João Silva — letreiros + frete">
        </div>
        <div class="form-group">
          <label class="form-label">Categoria</label>
          <select class="form-control" id="lan-categoria">
            <option>Letreiros</option><option>Frete</option><option>Motorista</option>
            <option>Manutenção</option><option>Material</option><option>Outros</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Data</label>
          <input type="date" class="form-control" id="lan-data" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Subtotal (R$)</label>
          <input type="number" class="form-control" id="lan-subtotal" step="0.01" min="0" placeholder="0,00" oninput="calcularValorFinal()">
        </div>
        <div class="form-group">
          <label class="form-label">Desconto</label>
          <div style="display:flex;gap:6px">
            <select class="form-control" id="lan-desc-tipo" style="flex:1;min-width:0" onchange="calcularValorFinal()">
              <option value="">Sem desconto</option>
              <option value="valor">R$ fixo</option>
              <option value="percentual">%</option>
            </select>
            <input type="number" class="form-control" id="lan-desc-valor" step="0.01" min="0" placeholder="0" style="width:84px;flex-shrink:0" oninput="calcularValorFinal()">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Frete (R$)</label>
          <input type="number" class="form-control" id="lan-frete" step="0.01" min="0" placeholder="0,00" oninput="calcularValorFinal()">
        </div>
        <div class="form-group">
          <label class="form-label">Valor Motorista (R$)</label>
          <input type="number" class="form-control" id="lan-motorista" step="0.01" min="0" placeholder="0,00">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select class="form-control" id="lan-status">
            <option value="pendente">Pendente</option>
            <option value="pago">Pago</option>
          </select>
        </div>
      </div>

      <!-- Valor final calculado -->
      <div class="modal-total" style="flex-direction:column;align-items:stretch;gap:4px">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span class="modal-total-lbl">Valor Final</span>
          <span class="modal-total-val" id="lan-valor-final">R$ 0,00</span>
        </div>
        <div id="lan-calc-detalhe" style="font-size:11px;color:var(--text3)"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-lancamento">Cancelar</button>
      <button class="btn btn-primary" id="btn-salvar-lan" onclick="salvarLancamento()">
        <i class="fa-solid fa-check"></i> Salvar Lançamento
      </button>
    </div>
  </div>
</div>

<!-- ═══ MODAL TABELA DE PREÇOS ═══ -->
<div class="modal-overlay" id="modal-precos">
  <div class="modal" style="max-width:600px">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fa-solid fa-tags" style="color:var(--yellow)"></i> Tabela de Preços</h3>
      <button class="modal-close" data-close-modal="modal-precos"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="max-height:75vh;overflow-y:auto">
      <p style="font-size:13px;color:var(--text3);margin-bottom:16px">Preços utilizados no cálculo automático de orçamentos.</p>
      <div id="precos-lista">
        <div style="text-align:center;padding:20px;color:var(--text3)"><i class="fa-solid fa-spinner fa-spin"></i></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-precos">Fechar</button>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
var MODO     = 'mensal';
var DADOS    = {};
var DEMANDAS = [];
var TIPO_LAN = 'receita';
var chartInst = null;

function moeda(v) {
  return 'R$ ' + parseFloat(v||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
}
function setModo(m) {
  MODO = m;
  document.getElementById('btn-modo-mensal').classList.toggle('active', m==='mensal');
  document.getElementById('btn-modo-anual').classList.toggle('active', m==='anual');
  document.getElementById('filtro-mensal').style.display = m==='mensal'?'flex':'none';
  document.getElementById('filtro-anual').style.display  = m==='anual'?'block':'none';
  carregarDados();
}
function setTipo(t) {
  TIPO_LAN = t;
  document.getElementById('btn-tipo-receita').classList.toggle('active', t==='receita');
  document.getElementById('btn-tipo-despesa').classList.toggle('active', t==='despesa');
}

// Helper: parseia JSON; se sessão expirou, vai pro login (sem loop de reload)
async function safeJson(r) {
  var txt = await r.text();
  var json;
  try { json = JSON.parse(txt); }
  catch(e) {
    // Resposta não-JSON = provavelmente HTML de login. Ir pro login uma vez.
    window.location.href = APP_URL + '/';
    throw new Error('redirect-login');
  }
  // Sessão expirada explícita
  if (json && json.redirect && json.erro && json.erro.toLowerCase().indexOf('sess') >= 0) {
    window.location.href = json.redirect;
    throw new Error('redirect-login');
  }
  return json;
}

async function carregarDados() {
  document.getElementById('fin-tabela').innerHTML = '<div style="padding:40px;text-align:center;color:var(--text3)"><i class="fa-solid fa-spinner fa-spin"></i> Carregando...</div>';
  var params = 'modo='+MODO;
  if (MODO==='mensal') params += '&mes='+document.getElementById('filtro-mes').value;
  else                 params += '&ano='+document.getElementById('filtro-ano').value;

  try {
    var r = await fetch(APP_URL+'/fin.php?acao=lista&'+params, {headers:{'X-Requested-With':'XMLHttpRequest'}});
    if (!r.ok) throw new Error('HTTP '+r.status);
    var json = await safeJson(r);
    if (json.erro) throw new Error(json.erro);
    DADOS = json;
    renderCards();
    renderTabela();
    renderGrafico();
  renderPizza();
    renderDemandas(DADOS.demandas||[]);
    carregarPrecos();
  } catch(e) {
    if(e && e.message === 'redirect-login') return;
    console.error('Financeiro erro:', e);
    document.getElementById('fin-tabela').innerHTML = '<div style="padding:40px;text-align:center;color:#ff3b30"><i class="fa-solid fa-triangle-exclamation"></i> Erro ao carregar: '+e.message+'</div>';
  }
}

function renderCards() {
  document.getElementById('fin-receitas').textContent  = moeda(DADOS.total_receitas);
  document.getElementById('fin-despesas').textContent  = moeda(DADOS.total_despesas);
  document.getElementById('fin-lucro').textContent     = moeda(DADOS.lucro);
  var lucroEl = document.getElementById('fin-lucro');
  lucroEl.style.color = DADOS.lucro >= 0 ? 'var(--green)' : '#ff3b30';
  // Pendentes: contar receitas pendentes
  var pend = (DADOS.lancamentos||[]).filter(l=>l.tipo==='receita'&&l.status==='pendente');
  var valPend = pend.reduce((s,l)=>s+(parseFloat(l.valor)||0),0);
  document.getElementById('fin-pendentes').textContent = moeda(valPend);
  document.getElementById('fin-count').textContent = (DADOS.lancamentos||[]).length + ' lançamentos';
}

function renderTabela() {
  var el = document.getElementById('fin-tabela');
  var lista = DADOS.lancamentos || [];
  if (!lista.length) {
    el.innerHTML = '<div style="padding:40px;text-align:center;color:var(--text3)">Nenhum lançamento no período.</div>';
    return;
  }
  var html = '<table class="data-table"><thead><tr>'
    +'<th>Descrição</th><th class="hide-mobile">Tipo</th><th style="text-align:right">Valor</th><th style="width:28px"></th>'
    +'</tr></thead><tbody>';
  lista.forEach(function(l) {
    var data = l.data_lancamento || (l.criado_em||'').substring(0,10);
    var dataBR = data ? data.split('-').reverse().join('/') : '—';
    html += '<tr class="row-click" onclick="verLancamento('+l.id+')">'
      +'<td><div style="font-weight:500">'+escHtml(l.descricao)+'</div>'
      +'<div style="font-size:11px;color:var(--text3)">'+dataBR+(l.categoria?' · '+escHtml(l.categoria):'')+'</div>'
      +'</td>'
      +'<td class="hide-mobile"><span class="fin-badge-'+l.tipo+'">'+( l.tipo==='receita'?'Receita':'Despesa')+'</span></td>'
      +'<td style="text-align:right;font-weight:700;color:'+(l.tipo==='receita'?'var(--green)':'var(--danger)')+'">'+moeda(l.valor)+'</td>'
      +'<td><i class="fa-solid fa-chevron-right"></i></td>'
      +'</tr>';
  });
  html += '</tbody></table>';
  el.innerHTML = html;
}

function verLancamento(id) {
  var l = (DADOS.lancamentos||[]).find(function(x){ return x.id == id; });
  if (!l) return;
  var data = l.data_lancamento || (l.criado_em||'').substring(0,10);
  var dataBR = data ? data.split('-').reverse().join('/') : '—';
  document.getElementById('vl-titulo').textContent = l.descricao || 'Lançamento';
  document.getElementById('vl-body').innerHTML =
    '<div class="detail-grid">'
    +'<div class="detail-item"><div class="detail-item-lbl">Tipo</div><div class="detail-item-val"><span class="fin-badge-'+l.tipo+'">'+(l.tipo==='receita'?'Receita':'Despesa')+'</span></div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">Status</div><div class="detail-item-val"><span class="fin-badge-'+(l.status||'pendente')+'">'+(l.status==='pago'?'Pago':'Pendente')+'</span></div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">Data</div><div class="detail-item-val">'+dataBR+'</div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">Categoria</div><div class="detail-item-val">'+escHtml(l.categoria||'—')+'</div></div>'
    +'<div class="detail-item full"><div class="detail-item-lbl">Demanda vinculada</div><div class="detail-item-val">'+(l.demanda_titulo?escHtml(l.demanda_titulo):'— Lançamento avulso —')+'</div></div>'
    +(l.subtotal&&l.subtotal!=l.valor?'<div class="detail-item"><div class="detail-item-lbl">Subtotal</div><div class="detail-item-val">'+moeda(l.subtotal)+'</div></div>':'')
    +(l.desconto_valor>0?'<div class="detail-item"><div class="detail-item-lbl">Desconto</div><div class="detail-item-val">'+moeda(l.desconto_valor)+'</div></div>':'')
    +(l.frete>0?'<div class="detail-item"><div class="detail-item-lbl">Frete</div><div class="detail-item-val">'+moeda(l.frete)+'</div></div>':'')
    +'<div class="detail-item full"><div class="detail-item-lbl">Valor final</div><div class="detail-item-val" style="font-size:20px;font-weight:800;color:'+(l.tipo==='receita'?'var(--green)':'var(--danger)')+'">'+moeda(l.valor)+'</div></div>'
    +'</div>';
  document.getElementById('vl-btn-del').onclick = function(){ Modal.close('modal-ver-lancamento'); deletarLan(l.id); };
  Modal.open('modal-ver-lancamento');
}

var pizzaInst = null;
function renderPizza() {
  var evo = DADOS.evolucao || [];
  var canvas = document.getElementById('chart-pizza');
  if (!canvas) return;
  if (pizzaInst) { pizzaInst.destroy(); pizzaInst = null; }
  if (!evo.length) {
    document.getElementById('pizza-wrap').innerHTML = '<div style="padding:60px 10px;text-align:center;color:var(--text3);font-size:13px">Sem dados ainda.</div>';
    return;
  }
  var rec  = evo.reduce(function(a,e){ return a + (parseFloat(e.receitas)||0); }, 0);
  var desp = evo.reduce(function(a,e){ return a + (parseFloat(e.despesas)||0); }, 0);
  var lucro = rec - desp;
  var fmt = function(v){ return 'R$ ' + v.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}); };
  var centro = {
    id:'fokosCentro',
    afterDraw:function(chart){
      var c = chart.ctx, a = chart.chartArea;
      var cx = (a.left+a.right)/2, cy = (a.top+a.bottom)/2;
      c.save(); c.textAlign='center';
      c.fillStyle = '#5C6170'; c.font = '600 10px Inter';
      c.fillText('LUCRO', cx, cy - 10);
      c.fillStyle = lucro >= 0 ? '#3DDC84' : '#FF5C51';
      c.font = '700 15px Sora';
      c.fillText(fmt(lucro), cx, cy + 9);
      c.restore();
    }
  };
  pizzaInst = new Chart(canvas.getContext('2d'), {
    type:'doughnut',
    data:{ labels:['Receitas','Despesas'],
      datasets:[{ data:[rec,desp],
        backgroundColor:['#3DDC84','#FF5C51'], hoverBackgroundColor:['#5FE79D','#FF7A71'],
        borderColor:'#171922', borderWidth:4, borderRadius:10, hoverOffset:6 }]
    },
    plugins:[ChartDataLabels, centro],
    options:{
      responsive:true, maintainAspectRatio:true, cutout:'64%',
      devicePixelRatio: Math.max(window.devicePixelRatio || 1, 2),
      layout:{padding:14},
      plugins:{
        legend:{position:'bottom',labels:{color:'#9CA1B2',font:{family:'Inter',size:11,weight:'600'},usePointStyle:true,pointStyle:'circle',boxWidth:6,boxHeight:6,padding:16}},
        tooltip:{backgroundColor:'#14161C',borderColor:'#303542',borderWidth:1,titleColor:'#F2F3F7',titleFont:{family:'Sora',size:12,weight:'700'},bodyColor:'#9CA1B2',bodyFont:{family:'Inter',size:11.5},padding:12,cornerRadius:12,usePointStyle:true,boxWidth:6,boxHeight:6,
          callbacks:{label:function(c){ return ' ' + c.label + ': ' + fmt(c.parsed); }}},
        datalabels:{
          color:'#0D0E12', font:{family:'Sora',size:11,weight:'700'},
          backgroundColor:function(c){ return c.dataset.backgroundColor[c.dataIndex]; },
          borderRadius:8, padding:{top:5,bottom:5,left:8,right:8},
          anchor:'end', align:'end', offset:2, clamp:true,
          formatter:function(v){
            var total = rec + desp;
            var pct = total > 0 ? Math.round(v/total*100) : 0;
            return fmt(v) + ' · ' + pct + '%';
          },
          display:function(c){ return c.dataset.data[c.dataIndex] > 0; }
        }
      }
    }
  });
}

function renderGrafico() {
  var evo = DADOS.evolucao || [];
  if (chartInst) chartInst.destroy();
  var ctx = document.getElementById('chart-evolucao').getContext('2d');
  chartInst = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: evo.map(function(e){ return e.mes; }),
      datasets: [
        {label:'Receitas', data:evo.map(function(e){ return parseFloat(e.receitas)||0; }), backgroundColor:'rgba(61,220,132,.75)', hoverBackgroundColor:'#3DDC84', borderRadius:8, borderSkipped:false, maxBarThickness:26},
        {label:'Despesas', data:evo.map(function(e){ return parseFloat(e.despesas)||0; }), backgroundColor:'rgba(255,92,81,.55)', hoverBackgroundColor:'#FF5C51', borderRadius:8, borderSkipped:false, maxBarThickness:26},
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:true, devicePixelRatio: Math.max(window.devicePixelRatio || 1, 2),
      interaction:{mode:'index',intersect:false},
      plugins:{
        legend:{position:'top',align:'end',labels:{color:'#9CA1B2',font:{family:'Inter',size:11,weight:'600'},usePointStyle:true,pointStyle:'circle',boxWidth:6,boxHeight:6,padding:14}},
        tooltip:{backgroundColor:'#14161C',borderColor:'#303542',borderWidth:1,titleColor:'#F2F3F7',titleFont:{family:'Sora',size:12,weight:'700'},bodyColor:'#9CA1B2',bodyFont:{family:'Inter',size:11.5},padding:12,cornerRadius:12,usePointStyle:true,boxWidth:6,boxHeight:6}
      },
      scales:{
        x:{ticks:{color:'#5C6170',font:{family:'Inter',size:10.5}},grid:{display:false},border:{display:false}},
        y:{beginAtZero:true,ticks:{color:'#5C6170',font:{family:'Inter',size:10.5},maxTicksLimit:5,callback:function(v){return 'R$'+(v>=1000?(v/1000).toLocaleString('pt-BR')+'k':v);}},grid:{color:'rgba(255,255,255,.05)'},border:{display:false}}
      }
    }
  });
}

function renderDemandas(lista) {
  DEMANDAS = lista;
  var sel = document.getElementById('lan-demanda');
  var cur = sel.value;
  sel.innerHTML = '<option value="">— Lançamento avulso —</option>';
  lista.forEach(function(d) {
    var dt = d.data_evento ? d.data_evento.split('-').reverse().join('/') : '';
    sel.innerHTML += '<option value="'+d.id+'">'+(dt?dt+' — ':'')+escHtml(d.titulo)+(d.cliente_nome?' ('+escHtml(d.cliente_nome)+')':'')+'</option>';
  });
  if (cur) sel.value = cur;
}

function onDemandaChange() {
  var id = document.getElementById('lan-demanda').value;
  document.getElementById('btn-orcamento-wrap').style.display = id ? 'block' : 'none';
  document.getElementById('linhas-orcamento').style.display = 'none';
}

async function calcularOrcamento() {
  var id = document.getElementById('lan-demanda').value;
  if (!id) return;
  try {
    var r = await fetch(APP_URL+'/fin.php?acao=orcamento&id='+id, {headers:{'X-Requested-With':'XMLHttpRequest'}});
    var data = await safeJson(r);
    if (data.erro) { alert(data.erro); return; }
    var linhas = data.linhas || [];
    var html = linhas.map(function(l) {
      return '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px">'
        +'<span style="color:'+(l.sem_preco?'#ff3b30':'var(--text)')+'">'+escHtml(l.descricao)+' × '+l.qtd+'</span>'
        +'<span style="font-weight:600">'+(l.sem_preco?'<span style="color:#ff3b30">Sem preço</span>':moeda(l.total))+'</span>'
        +'</div>';
    }).join('');
    html += '<div style="display:flex;justify-content:space-between;padding:10px 0 0;font-weight:700;font-size:14px">'
      +'<span>Subtotal</span><span style="color:var(--yellow)">'+moeda(data.subtotal)+'</span></div>';

    document.getElementById('linhas-orcamento-itens').innerHTML = html || '<p style="color:var(--text3);font-size:13px">Sem letreiros nesta demanda.</p>';
    document.getElementById('linhas-orcamento').style.display = 'block';
    document.getElementById('lan-subtotal').value = parseFloat(data.subtotal).toFixed(2);
    if (data.demanda && !document.getElementById('lan-descricao').value) {
      document.getElementById('lan-descricao').value = 'Evento — '+(data.demanda.titulo||'');
    }
    calcularValorFinal();
  } catch(e) { alert('Erro ao calcular orçamento'); }
}

function calcularValorFinal() {
  var sub  = parseFloat(document.getElementById('lan-subtotal').value)||0;
  var dTipo = document.getElementById('lan-desc-tipo').value;
  var dVal  = parseFloat(document.getElementById('lan-desc-valor').value)||0;
  var frete = parseFloat(document.getElementById('lan-frete').value)||0;
  var desc  = 0;
  if (dTipo==='percentual') desc = sub * dVal / 100;
  else if (dTipo==='valor') desc = dVal;
  var total = Math.max(0, sub - desc + frete);
  document.getElementById('lan-valor-final').textContent = moeda(total);
  var det = [];
  if (sub)   det.push('Subtotal: '+moeda(sub));
  if (desc)  det.push('Desconto: −'+moeda(desc));
  if (frete) det.push('Frete: +'+moeda(frete));
  document.getElementById('lan-calc-detalhe').textContent = det.join(' · ');
}

async function salvarLancamento() {
  var btn = document.getElementById('btn-salvar-lan');
  btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
  var fd = new FormData();
  fd.append('_csrf',       CSRF);
  fd.append('tipo',        TIPO_LAN);
  fd.append('descricao',   document.getElementById('lan-descricao').value);
  fd.append('categoria',   document.getElementById('lan-categoria').value);
  fd.append('demanda_id',  document.getElementById('lan-demanda').value);
  fd.append('subtotal',    document.getElementById('lan-subtotal').value||0);
  fd.append('desconto_tipo',  document.getElementById('lan-desc-tipo').value);
  fd.append('desconto_valor', document.getElementById('lan-desc-valor').value||0);
  fd.append('frete',          document.getElementById('lan-frete').value||0);
  fd.append('valor_motorista',document.getElementById('lan-motorista').value||0);
  fd.append('data_lancamento',document.getElementById('lan-data').value);
  fd.append('status',         document.getElementById('lan-status').value);
  fd.append('orcamento_auto', document.getElementById('linhas-orcamento').style.display!=='none'?1:0);
  try {
    var r = await fetch(APP_URL+'/fin-post.php?acao=criar', {method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}});
    var data = await safeJson(r);
    if (data.sucesso) {
      Modal.close('modal-lancamento');
      Toast.success('Lançamento salvo! Valor final: '+moeda(data.valor_final));
      carregarDados();
      limparFormLan();
    } else { Toast.error(data.erro||'Erro ao salvar'); }
  } catch(e) { Toast.error('Erro de conexão'); }
  btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-check"></i> Salvar Lançamento';
}

function limparFormLan() {
  ['lan-descricao','lan-subtotal','lan-desc-valor','lan-frete','lan-motorista'].forEach(function(id){
    document.getElementById(id).value='';
  });
  document.getElementById('lan-demanda').value='';
  document.getElementById('lan-desc-tipo').value='';
  document.getElementById('lan-status').value='pendente';
  document.getElementById('lan-data').value=new Date().toISOString().split('T')[0];
  document.getElementById('linhas-orcamento').style.display='none';
  document.getElementById('btn-orcamento-wrap').style.display='none';
  document.getElementById('lan-valor-final').textContent='R$ 0,00';
  document.getElementById('lan-calc-detalhe').textContent='';
  setTipo('receita');
}

async function deletarLan(id) {
  if (!confirm('Excluir este lançamento?')) return;
  var fd = new FormData(); fd.append('_csrf',CSRF);
  try {
    var r = await fetch(APP_URL+'/fin-post.php?acao=delete&id='+id,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}});
    var data = await safeJson(r);
    if (data.sucesso) { Toast.success('Removido!'); carregarDados(); }
  } catch(e) {}
}

async function carregarPrecos() {
  try {
    var r = await fetch(APP_URL+'/fin.php?acao=precos',{headers:{'X-Requested-With':'XMLHttpRequest'}});
    var data = await safeJson(r);
    var lista = data.precos||[];
    var html = lista.length
      ? lista.map(function(p){
          return '<div class="preco-row">'
            +'<div><div style="font-weight:500;font-size:13px">'+escHtml(p.descricao||p.tipo_nome+' '+p.tamanho_nome)+'</div>'
            +'<div style="font-size:11px;color:var(--text3)">'+escHtml(p.tipo_nome)+' · '+escHtml(p.tamanho_nome)+'</div></div>'
            +'<input type="number" class="form-control" style="font-size:13px" value="'+parseFloat(p.preco_unitario).toFixed(2)+'" id="preco-'+p.id+'" step="0.01">'
            +'<button class="btn btn-ghost btn-sm" onclick="salvarPreco('+p.id+')"><i class="fa-solid fa-check"></i></button>'
            +'</div>';
        }).join('')
      : '<p style="color:var(--text3);font-size:13px">Nenhum preço configurado. Execute a migração SQL.</p>';
    document.getElementById('precos-lista').innerHTML = html;
  } catch(e) {}
}

async function salvarPreco(id) {
  var fd = new FormData(); fd.append('_csrf',CSRF);
  fd.append('preco_unitario', document.getElementById('preco-'+id).value);
  try {
    var r = await fetch(APP_URL+'/fin-post.php?acao=preco&id='+id,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}});
    var data = await safeJson(r);
    if (data.sucesso) Toast.success('Preço atualizado!');
  } catch(e) {}
}

// Inicializar após scripts do layout carregarem
// Bind eventos após tudo carregar
window.addEventListener('load', function(){
  if (typeof APP_URL === 'undefined') window.APP_URL = document.querySelector('meta[name="app-url"]')?.content || '';
  if (typeof CSRF    === 'undefined') window.CSRF    = document.querySelector('meta[name="csrf"]')?.content    || '';
  if (typeof Modal   === 'undefined') window.Modal   = { open: function(id){ var e=document.getElementById(id); if(e){e.classList.add('open');document.body.style.overflow='hidden';} }, close: function(id){ var e=document.getElementById(id); if(e){e.classList.remove('open');document.body.style.overflow='';} } };
  if (typeof Toast   === 'undefined') window.Toast   = { success: function(m){ alert(m); }, error: function(m){ alert(m); } };
  if (typeof escHtml === 'undefined') window.escHtml = function(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); };

  var bp = document.getElementById('btn-open-precos');
  var bl = document.getElementById('btn-open-lancamento');
  var bm = document.getElementById('btn-modo-mensal');
  var ba = document.getElementById('btn-modo-anual');
  var fp = document.getElementById('filtro-periodo');
  if(bp) bp.addEventListener('click', function(){ Modal.open('modal-precos'); carregarPrecos(); });
  if(bl) bl.addEventListener('click', function(){ Modal.open('modal-lancamento'); });
  if(bm) bm.addEventListener('click', function(){ setModo('mensal'); });
  if(ba) ba.addEventListener('click', function(){ setModo('anual'); });
  if(fp) fp.addEventListener('change', carregarDados);
  carregarDados();
});
</script>
<!-- Modal Ver Lançamento -->
<div class="modal-overlay" id="modal-ver-lancamento">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fa-solid fa-file-invoice-dollar" style="color:var(--yellow)"></i> <span id="vl-titulo">Lançamento</span></h3>
      <button class="modal-close" data-close-modal="modal-ver-lancamento"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="vl-body"></div>
    <div class="modal-footer" style="justify-content:space-between">
      <button class="btn btn-danger btn-sm" id="vl-btn-del"><i class="fa-solid fa-trash"></i> Excluir</button>
      <button class="btn btn-ghost" data-close-modal="modal-ver-lancamento">Fechar</button>
    </div>
  </div>
</div>
