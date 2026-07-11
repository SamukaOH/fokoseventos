<?php
$demandas = Database::fetchAll(
    "SELECT d.id, d.titulo, d.data_evento, d.horario, d.horario_retirada, d.status,
            c.nome as cliente_nome,
            ue.nome as motorista_entrega_nome,
            ur.nome as motorista_retirada_nome
     FROM demandas d
     LEFT JOIN clientes c ON c.id = d.cliente_id
     LEFT JOIN usuarios ue ON ue.id = d.motorista_id
     LEFT JOIN usuarios ur ON ur.id = d.motorista_retirada_id
     WHERE d.data_evento IS NOT NULL AND d.status != 'cancelado'
     ORDER BY d.data_evento ASC, d.horario ASC"
);
$demandasJson = json_encode($demandas, JSON_UNESCAPED_UNICODE);
?>


<!-- Header -->
<div class="cal-header">
  <div class="cal-nav">
    <button class="cal-nav-btn" id="cal-btn-prev"><i class="fa-solid fa-chevron-left"></i></button>
    <div class="cal-mes-titulo" id="cal-titulo">—</div>
    <button class="cal-nav-btn" id="cal-btn-next"><i class="fa-solid fa-chevron-right"></i></button>
  </div>
  <button class="cal-hoje-btn" id="cal-btn-hoje">Hoje</button>
</div>

<!-- Legenda -->
<div class="cal-legenda">
  <?php foreach([
    ['#f59e0b','Pendente'],
    ['#3b82f6','Em Preparação'],
    ['#a855f7','Em Rota'],
    ['#22c55e','Entregue'],
    ['#f97316','Em Retirada'],
    ['#64d2ff','Devolvido'],
    ['#6b7280','Finalizado'],
  ] as $l): ?>
  <div class="cal-leg-item">
    <div class="cal-leg-dot" style="background:<?= $l[0] ?>"></div>
    <?= $l[1] ?>
  </div>
  <?php endforeach; ?>
</div>

<!-- Grid -->
<div class="cal-grid-wrap">
  <div class="cal-weekdays">
    <?php foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $d): ?>
    <div class="cal-weekday"><?= $d ?></div>
    <?php endforeach; ?>
  </div>
  <div class="cal-days" id="cal-grid"></div>
</div>

<!-- Aviso inicial (some quando abre painel) -->
<div id="cal-aviso" style="margin-top:16px;background:var(--bg2);border:1px solid var(--border);border-radius:14px;padding:24px;text-align:center;color:var(--text3)">
  <i class="fa-solid fa-hand-pointer" style="font-size:28px;opacity:.3;display:block;margin-bottom:10px"></i>
  <div style="font-size:14px;font-weight:500;color:var(--text2);margin-bottom:4px">Selecione um dia</div>
  <div style="font-size:12px">Clique em qualquer data do calendário para ver os eventos</div>
</div>

<!-- Painel do dia -->
<div class="cal-panel" id="cal-panel">
  <div class="cal-panel-header">
    <div class="cal-panel-title" id="cal-panel-titulo">—</div>
    <button class="cal-panel-close" id="cal-panel-close"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="cal-panel-body" id="cal-panel-body"></div>
</div>

<script>
var DEMANDAS_CAL = <?= $demandasJson ?>;
var MESES_PT = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
var DIAS_PT  = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];

var STATUS_COR = {
  pendente:'#f59e0b', preparacao:'#3b82f6', em_rota:'#a855f7',
  entregue:'#22c55e', em_retirada:'#f97316', devolvido:'#64d2ff',
  finalizado:'#6b7280', cancelado:'#ef4444'
};
var STATUS_LABEL = {
  pendente:'Pendente', preparacao:'Em Preparação', em_rota:'Em Rota',
  entregue:'Entregue', em_retirada:'Em Retirada', devolvido:'Devolvido',
  finalizado:'Finalizado', cancelado:'Cancelado'
};

window.Cal = {
  ano: new Date().getFullYear(),
  mes: new Date().getMonth(),

  render: function() {
    var hoje = new Date();
    document.getElementById('cal-titulo').textContent = MESES_PT[this.mes] + ' ' + this.ano;

    var primeiroDia  = new Date(this.ano, this.mes, 1).getDay();
    var diasNoMes    = new Date(this.ano, this.mes + 1, 0).getDate();
    var diasMesAntes = new Date(this.ano, this.mes, 0).getDate();

    // Agrupar demandas por data YYYY-MM-DD
    var porDia = {};
    DEMANDAS_CAL.forEach(function(d) {
      if (!d.data_evento) return;
      var partes = d.data_evento.split('-');
      if (parseInt(partes[0]) === Cal.ano && parseInt(partes[1]) - 1 === Cal.mes) {
        var dia = parseInt(partes[2]);
        if (!porDia[dia]) porDia[dia] = [];
        porDia[dia].push(d);
      }
    });

    var html = '';

    // Células do mês anterior
    for (var i = primeiroDia - 1; i >= 0; i--) {
      html += '<div class="cal-cell outro-mes"><div class="cal-dia-num" style="color:var(--text3);opacity:.4">' + (diasMesAntes - i) + '</div></div>';
    }

    // Dias do mês atual
    for (var d = 1; d <= diasNoMes; d++) {
      var isHoje = (d === hoje.getDate() && this.mes === hoje.getMonth() && this.ano === hoje.getFullYear());
      var eventos = porDia[d] || [];
      var dd = d;
      var ano = this.ano; var mes = this.mes;

      html += '<div class="cal-cell' + (isHoje ? ' hoje' : '') + '" onclick="Cal.abrirDia(' + d + ')">';
      html += '<div class="cal-dia-num">' + d + '</div>';

      // Desktop: mostrar título dos eventos
      var evDesktop = eventos.slice(0, 3).map(function(ev) {
        var cor = STATUS_COR[ev.status] || '#888';
        return '<div class="cal-evento cal-ev-desktop" style="background:' + cor + '18;color:' + cor + ';border-left-color:' + cor + '">'
          + escHtml(ev.titulo) + '</div>';
      }).join('');
      if (eventos.length > 3) evDesktop += '<div class="cal-mais cal-ev-desktop">+' + (eventos.length - 3) + ' mais</div>';

      // Mobile: só bolinha com quantidade
      var evMobile = '';
      if (eventos.length > 0) {
        // pegar a cor do primeiro evento (mais urgente)
        var corPrimeiro = STATUS_COR[eventos[0].status] || '#FFD600';
        evMobile = '<div class="cal-ev-mobile">'
          + '<div style="width:20px;height:20px;border-radius:50%;background:' + corPrimeiro + ';display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#000;margin:0 auto">'
          + eventos.length + '</div></div>';
      }

      html += evDesktop + evMobile;
      html += '</div>';
    }

    // Completar grid
    var total = primeiroDia + diasNoMes;
    var resto = total % 7 === 0 ? 0 : 7 - (total % 7);
    for (var i = 1; i <= resto; i++) {
      html += '<div class="cal-cell outro-mes"><div class="cal-dia-num" style="color:var(--text3);opacity:.4">' + i + '</div></div>';
    }

    document.getElementById('cal-grid').innerHTML = html;
    fecharPainel();
  },

  abrirDia: function(dia) {
    var eventos = DEMANDAS_CAL.filter(function(d) {
      if (!d.data_evento) return false;
      var p = d.data_evento.split('-');
      return parseInt(p[0]) === Cal.ano && parseInt(p[1]) - 1 === Cal.mes && parseInt(p[2]) === dia;
    });

    var mesNome = MESES_PT[this.mes];
    document.getElementById('cal-panel-titulo').textContent = dia + ' de ' + mesNome + ' de ' + this.ano;

    var body = document.getElementById('cal-panel-body');
    if (!eventos.length) {
      body.innerHTML = '<p style="color:var(--text3);font-size:13px;text-align:center;padding:20px 0">Nenhum evento neste dia.</p>';
    } else {
      body.innerHTML = eventos.map(function(ev) {
        var cor   = STATUS_COR[ev.status] || '#888';
        var label = STATUS_LABEL[ev.status] || ev.status;
        var horaE = ev.horario          ? ev.horario.substring(0,5)          : null;
        var horaR = ev.horario_retirada ? ev.horario_retirada.substring(0,5) : null;

        var horariosHtml = '<div style="display:flex;flex-direction:column;gap:4px;min-width:90px">';
        if (horaE) {
          horariosHtml += '<div style="display:flex;align-items:center;gap:5px">'
            + '<i class="fa-solid fa-truck-fast" style="color:#3b82f6;font-size:10px;width:12px"></i>'
            + '<span style="font-size:11px;color:#3b82f6;font-weight:600">Entrega</span>'
            + '<span style="font-family:Sora,sans-serif;font-size:13px;font-weight:700;color:var(--yellow);margin-left:auto">' + horaE + '</span>'
            + '</div>';
        }
        if (horaR) {
          horariosHtml += '<div style="display:flex;align-items:center;gap:5px">'
            + '<i class="fa-solid fa-rotate-left" style="color:#f97316;font-size:10px;width:12px"></i>'
            + '<span style="font-size:11px;color:#f97316;font-weight:600">Retirada</span>'
            + '<span style="font-family:Sora,sans-serif;font-size:13px;font-weight:700;color:var(--yellow);margin-left:auto">' + horaR + '</span>'
            + '</div>';
        }
        if (!horaE && !horaR) {
          horariosHtml += '<span style="font-size:11px;color:var(--text3)">Sem horário</span>';
        }
        horariosHtml += '</div>';

        return '<div class="cal-evento-item" onclick="window.location.href=APP_URL+\'/demandas\'">'
          + '<div class="cal-ev-info" style="flex:1">'
          + '<div class="cal-ev-titulo">' + escHtml(ev.titulo) + '</div>'
          + '<div class="cal-ev-sub">' + escHtml(ev.cliente_nome || '—') + '</div>'
          + '<div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--border)">' + horariosHtml + '</div>'
          + '</div>'
          + '<div class="cal-ev-badge" style="background:' + cor + '20;color:' + cor + ';border:1px solid ' + cor + '44;align-self:flex-start">' + label + '</div>'
          + '</div>';
      }).join('');
    }

    document.getElementById('cal-aviso').style.display = 'none';
    document.getElementById('cal-panel').classList.add('open');
    document.getElementById('cal-panel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  },

  prev:  function() { this.mes--; if (this.mes < 0)  { this.mes = 11; this.ano--; } this.render(); },
  next:  function() { this.mes++; if (this.mes > 11) { this.mes = 0;  this.ano++; } this.render(); },
  hoje:  function() { this.ano = new Date().getFullYear(); this.mes = new Date().getMonth(); this.render(); }
};

function fecharPainel() {
  document.getElementById('cal-panel').classList.remove('open');
  document.getElementById('cal-aviso').style.display = 'block';
}

// Bind de botões e renderização
function calInit() {
  Cal.render();
  document.getElementById('cal-btn-prev').addEventListener('click', function(){ Cal.prev(); });
  document.getElementById('cal-btn-next').addEventListener('click', function(){ Cal.next(); });
  document.getElementById('cal-btn-hoje').addEventListener('click', function(){ Cal.hoje(); });
  document.getElementById('cal-panel-close').addEventListener('click', fecharPainel);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', calInit);
} else {
  calInit();
}
</script>
