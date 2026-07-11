<!-- RELATÓRIOS -->
<div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:24px;flex-wrap:wrap;gap:12px">
  <div style="display:flex;gap:8px;align-items:center">
    <select class="form-control" id="rel-ano" style="width:110px" onchange="carregarRelatorio()">
      <?php for($y=date('Y'); $y>=2023; $y--): ?>
      <option value="<?= $y ?>" <?= $y==date('Y')?'selected':'' ?>><?= $y ?></option>
      <?php endfor; ?>
    </select>
    <button class="btn btn-ghost btn-sm btn-icon" onclick="carregarRelatorio()" title="Atualizar">
      <i class="fa-solid fa-rotate-right"></i>
    </button>
  </div>
</div>

<!-- KPIs do ano -->
<div class="cards-grid" style="margin-bottom:20px">
  <div class="stat-card green">
    <div class="stat-card-top"><div class="stat-icon green"><i class="fa-solid fa-arrow-trend-up"></i></div></div>
    <div class="stat-value" id="rel-rec-ano">–</div><div class="stat-label">Receita Anual</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-top"><div class="stat-icon" style="background:rgba(255,59,48,.1);color:#ff3b30"><i class="fa-solid fa-arrow-trend-down"></i></div></div>
    <div class="stat-value" id="rel-desp-ano" style="color:#ff3b30">–</div><div class="stat-label">Despesas Anual</div>
  </div>
  <div class="stat-card yellow">
    <div class="stat-card-top"><div class="stat-icon yellow"><i class="fa-solid fa-scale-balanced"></i></div></div>
    <div class="stat-value" id="rel-lucro-ano">–</div><div class="stat-label">Lucro Anual</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-top"><div class="stat-icon blue"><i class="fa-solid fa-calendar-check"></i></div></div>
    <div class="stat-value" id="rel-eventos-fat">–</div><div class="stat-label">Eventos Faturados</div>
  </div>
</div>

<div class="rel-grid-2">
  <!-- Evolução mensal anual -->
  <div class="card" style="padding:20px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:16px">Receitas × Despesas — Mensal</div>
    <canvas id="chart-anual" height="200"></canvas>
  </div>
  <!-- Lucro por mês -->
  <div class="card" style="padding:20px">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:16px">Lucro Líquido por Mês</div>
    <canvas id="chart-lucro" height="200"></canvas>
  </div>
</div>

<div class="rel-grid-side">
  <!-- Top clientes -->
  <div class="card" style="padding:0;overflow:hidden">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text3)">
      <i class="fa-solid fa-star" style="color:var(--yellow);margin-right:6px"></i>Top Clientes do Ano
    </div>
    <div id="rel-top-clientes" style="padding:4px 0"></div>
  </div>

  <!-- Por categoria -->
  <div class="card" style="padding:0;overflow:hidden">
    <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text3)">Por Categoria</div>
    <div id="rel-categorias" style="padding:12px 18px"></div>
  </div>
</div>

<!-- Pendentes de pagamento -->
<div class="card" style="padding:0;overflow:hidden">
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text3)">
    <i class="fa-solid fa-clock" style="color:var(--yellow);margin-right:6px"></i>A Receber (Pendentes)
  </div>
  <div id="rel-pendentes" style="overflow-x:auto"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
var chartAnual=null, chartLucro=null;
document.addEventListener('DOMContentLoaded', carregarRelatorio);
function moeda(v){ return 'R$ '+parseFloat(v||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}); }

async function carregarRelatorio() {
  var ano = document.getElementById('rel-ano').value;
  try {
    var r = await fetch(APP_URL+'/api/financeiro/dashboard?ano='+ano, {headers:{'X-Requested-With':'XMLHttpRequest'}});
    var d = await r.json();

    // KPIs
    var rec  = parseFloat(d.anoStat?.receitas||0);
    var desp = parseFloat(d.anoStat?.despesas||0);
    document.getElementById('rel-rec-ano').textContent       = moeda(rec);
    document.getElementById('rel-desp-ano').textContent      = moeda(desp);
    document.getElementById('rel-lucro-ano').textContent     = moeda(rec-desp);
    document.getElementById('rel-eventos-fat').textContent   = d.anoStat?.demandas_faturadas ?? '–';
    document.getElementById('rel-lucro-ano').style.color     = (rec-desp)>=0?'var(--green)':'#ff3b30';

    // Gráfico barras
    var evo = d.evolucao||[];
    if(chartAnual) chartAnual.destroy();
    chartAnual = new Chart(document.getElementById('chart-anual').getContext('2d'),{
      type:'bar',
      data:{
        labels:evo.map(e=>e.mes),
        datasets:[
          {label:'Receitas',data:evo.map(e=>parseFloat(e.receitas)||0),backgroundColor:'rgba(61,220,132,.75)',hoverBackgroundColor:'#3DDC84',borderRadius:8,borderSkipped:false,maxBarThickness:22},
          {label:'Despesas',data:evo.map(e=>parseFloat(e.despesas)||0),backgroundColor:'rgba(255,92,81,.55)',hoverBackgroundColor:'#FF5C51',borderRadius:8,borderSkipped:false,maxBarThickness:22},
        ]
      },
      options:{
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

    // Gráfico lucro linha
    if(chartLucro) chartLucro.destroy();
    chartLucro = new Chart(document.getElementById('chart-lucro').getContext('2d'),{
      type:'line',
      data:{
        labels:evo.map(e=>e.mes),
        datasets:[{
          label:'Lucro',
          data:evo.map(e=>(parseFloat(e.receitas)||0)-(parseFloat(e.despesas)||0)),
          borderColor:'#FFD600',backgroundColor:(function(){var g=document.getElementById('chart-lucro').getContext('2d').createLinearGradient(0,0,0,240);g.addColorStop(0,'rgba(255,214,0,.28)');g.addColorStop(1,'rgba(255,214,0,0)');return g;})(),
          borderWidth:2.5,pointRadius:0,pointHoverRadius:5,pointBackgroundColor:'#FFD600',pointBorderColor:'#0D0E12',pointBorderWidth:2,tension:.45,fill:true
        }]
      },
      options:(function(){var o={
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
    };o.plugins.legend.display=false;return o;})()
    });

    // Top clientes
    var top = d.topClientes||[];
    document.getElementById('rel-top-clientes').innerHTML = top.length
      ? top.map(function(c,i){
          return '<div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border)">'
            +'<div style="width:26px;height:26px;border-radius:50%;background:var(--yellow);color:#000;font-weight:800;font-size:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0">'+(i+1)+'</div>'
            +'<div style="flex:1;font-weight:500">'+escHtml(c.nome)+'</div>'
            +'<div style="font-weight:700;color:var(--green)">'+moeda(c.total)+'</div>'
            +'<div style="font-size:11px;color:var(--text3)">'+c.eventos+'ev.</div>'
            +'</div>';
        }).join('')
      : '<div style="padding:20px;text-align:center;color:var(--text3);font-size:13px">Sem dados.</div>';

    // Categorias
    var cats = d.porCategoria||[];
    document.getElementById('rel-categorias').innerHTML = cats.map(function(c){
      return '<div style="padding:8px 0;border-bottom:1px solid var(--border)">'
        +'<div style="display:flex;justify-content:space-between;margin-bottom:3px">'
        +'<span style="font-size:12px;font-weight:600">'+escHtml(c.categoria)+'</span>'
        +'<span style="font-size:12px;color:var(--green)">'+moeda(c.receitas)+'</span></div>'
        +(c.despesas>0?'<div style="font-size:11px;color:#ff3b30">Despesas: '+moeda(c.despesas)+'</div>':'')
        +'</div>';
    }).join('') || '<div style="font-size:12px;color:var(--text3)">Sem dados.</div>';

    // Pendentes
    var pend = d.pendentes||[];
    document.getElementById('rel-pendentes').innerHTML = pend.length
      ? '<table class="data-table"><thead><tr><th>Demanda</th><th>Cliente</th><th>Data</th><th style="text-align:right">Valor</th></tr></thead><tbody>'
        + pend.map(function(p){
            var dt = p.data_evento ? p.data_evento.split('-').reverse().join('/') : '—';
            return '<tr><td style="font-weight:500">'+escHtml(p.titulo)+'</td>'
              +'<td style="color:var(--text3);font-size:12px">'+escHtml(p.cliente_nome||'—')+'</td>'
              +'<td style="color:var(--text3);font-size:12px">'+dt+'</td>'
              +'<td style="text-align:right;font-weight:700;color:var(--yellow)">'+moeda(p.valor)+'</td></tr>';
          }).join('')
        +'</tbody></table>'
      : '<div style="padding:20px;text-align:center;color:var(--text3);font-size:13px">Tudo em dia! Nenhum valor pendente.</div>';

  } catch(e) { console.error(e); }
}

carregarRelatorio();
document.getElementById('rel-ano').addEventListener('change', carregarRelatorio);
</script>
