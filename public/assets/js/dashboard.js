// ============================================================
// FOKOS EVENTOS — Dashboard JS
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
  loadDashboardData();
  setInterval(loadDashboardData, 60000);
});

async function loadDashboardData() {
  const data = await Api.get('/api/dashboard');
  if (!data) return;
  renderCards(data);
  renderProximosEventos(data.proximosEventos || []);
  renderUltimas(data.ultimasDemandas || []);
  renderFaturamentoChart(data.faturamentoMeses || []);
  renderStatusChart(data.statusDemandas || []);
}

function renderCards(d) {
  const map = {
    'card-demandas-ativas':  d.demandasAtivas,
    'card-demandas-hoje':    d.demandasHoje,
    'card-demandas-fin':     d.demandasFinalizadas,
    'card-motoristas':       d.motoristasAtivos,
    'card-estoque-baixo':    d.estoqueBaixo,
    'card-faturamento':      moeda(d.faturamentoMes),
    'card-despesas':         moeda(d.despesasMes),
    'card-lucro':            moeda(d.lucro),
  };
  Object.entries(map).forEach(function(entry) {
    var el = document.getElementById(entry[0]);
    if (el) el.textContent = entry[1];
  });
}

var MESES = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
var MESES_SHORT = ['JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ'];

function renderProximosEventos(eventos) {
  var el = document.getElementById('proximos-eventos');
  if (!el) return;

  if (!eventos.length) {
    el.innerHTML = '<div style="padding:30px;text-align:center;color:var(--text3)"><i class="fa-solid fa-calendar-xmark" style="font-size:24px;margin-bottom:8px;display:block"></i>Sem eventos próximos</div>';
    return;
  }

  el.innerHTML = eventos.map(function(ev) {
    var dt = new Date(ev.data_evento + 'T00:00:00');
    var dia = dt.getDate();
    var mes = MESES_SHORT[dt.getMonth()];
    var horario = ev.horario ? ev.horario.substring(0,5) : '--:--';
    var cliente = ev.cliente_nome || 'Sem cliente';

    return '<div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);overflow:hidden">'
      + '<div style="width:40px;height:40px;border-radius:8px;background:var(--yellow-glow);display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0">'
      + '<span style="font-size:15px;font-weight:800;color:var(--yellow);line-height:1">' + dia + '</span>'
      + '<span style="font-size:9px;color:var(--text3);text-transform:uppercase">' + mes + '</span>'
      + '</div>'
      + '<div style="flex:1;min-width:0">'
      + '<div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + escHtml(ev.titulo) + '</div>'
      + '<div style="font-size:11px;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + escHtml(cliente) + ' · ' + horario + '</div>'
      + '</div>'
      + '<span style="font-size:10px;padding:3px 8px;border-radius:20px;background:rgba(255,255,255,.07);color:var(--text2);flex-shrink:0;white-space:nowrap">' + statusLabel(ev.status) + '</span>'
      + '</div>';
  }).join('');
}

function renderUltimas(demandas) {
  var tbody = document.getElementById('ultimas-demandas');
  if (!tbody) return;

  if (!demandas.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="padding:30px;text-align:center;color:var(--text2)">Nenhuma demanda</td></tr>';
    return;
  }

  tbody.innerHTML = demandas.map(function(d) {
    return '<tr style="cursor:pointer" onclick="window.location.href=APP_URL+\'/demandas\'">'
      + '<td data-label="Demanda"><strong>' + escHtml(d.titulo) + '</strong><br><small style="color:var(--text2)">' + escHtml(d.cliente_nome || '–') + '</small></td>'
      + '<td data-label="Status">' + badgeStatus(d.status) + '</td>'
      + '<td data-label="Prioridade" class="hide-mobile">' + badgePrio(d.prioridade) + '</td>'
      + '<td data-label="Motorista" class="hide-mobile"><span style="font-size:12px">' + escHtml(d.motorista_nome || '–') + '</span></td>'
      + '<td data-label="Data" class="hide-mobile" style="color:var(--text2);font-size:12px">' + dataBR(d.data_evento) + '</td>'
      + '</tr>';
  }).join('');
}

function renderFaturamentoChart(dados) {
  var ctx = document.getElementById('chart-faturamento');
  if (!ctx) return;
  if (window._chartFat) { window._chartFat.destroy(); }

  var labels = dados.map(function(d) {
    var p = d.mes.split('-');
    return MESES[parseInt(p[1]) - 1];
  });
  var valores = dados.map(function(d) { return parseFloat(d.total) || 0; });

  window._chartFat = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Faturamento',
        data: valores,
        borderColor: '#FFD600',
        backgroundColor: 'rgba(255,214,0,.08)',
        borderWidth: 2.5,
        pointBackgroundColor: '#FFD600',
        pointRadius: 4,
        pointHoverRadius: 6,
        tension: .4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1f2330',
          borderColor: 'rgba(255,255,255,.1)',
          borderWidth: 1,
          callbacks: {
            label: function(c) { return ' R$ ' + c.raw.toLocaleString('pt-BR', {minimumFractionDigits:2}); }
          }
        }
      },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#5a5f74', font: { size: 11 } } },
        y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#5a5f74', font: { size: 11 }, callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); } } }
      }
    }
  });
}

function renderStatusChart(statusData) {
  var ctx = document.getElementById('chart-status');
  if (!ctx) return;
  if (window._chartStatus) { window._chartStatus.destroy(); }

  var colors = {
    pendente: '#f59e0b', preparacao: '#3b82f6', em_rota: '#a855f7',
    entregue: '#22c55e', montado: '#10b981', finalizado: '#6b7280', cancelado: '#ef4444'
  };

  window._chartStatus = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: statusData.map(function(s) { return statusLabel(s.status); }),
      datasets: [{
        data: statusData.map(function(s) { return s.total; }),
        backgroundColor: statusData.map(function(s) { return colors[s.status] || '#888'; }),
        borderWidth: 0,
        hoverOffset: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '72%',
      plugins: {
        legend: { position: 'bottom', labels: { color: '#8a8fa8', font: { size: 11 }, padding: 12, boxWidth: 10 } },
        tooltip: { backgroundColor: '#1f2330', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1 }
      }
    }
  });
}
