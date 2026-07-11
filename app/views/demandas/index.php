<?php
$demandas = Database::fetchAll(
    "SELECT d.*, c.nome as cliente_nome, u.nome as motorista_nome,
            GROUP_CONCAT(dl.caractere ORDER BY dl.id SEPARATOR '') as letreiros_texto
     FROM demandas d
     LEFT JOIN clientes c ON c.id = d.cliente_id
     LEFT JOIN usuarios u ON u.id = d.motorista_id
     LEFT JOIN demanda_letreiros dl ON dl.demanda_id = d.id
     WHERE d.status != 'cancelado'
     GROUP BY d.id
     ORDER BY d.data_evento ASC"
);
$clientes   = Database::fetchAll("SELECT id, nome FROM clientes ORDER BY nome");
$motoristas = Database::fetchAll("SELECT id, nome FROM usuarios WHERE tipo='motorista' AND status='ativo' ORDER BY nome");
$tipos      = Database::fetchAll("SELECT * FROM letreiros_tipos WHERE ativo=1 ORDER BY id");
$tamanhos   = Database::fetchAll("SELECT * FROM letreiros_tamanhos WHERE ativo=1 ORDER BY altura_cm");

// Grupos de status
$grupos = [
    'enviar'    => ['label'=>'Para Enviar',  'cor'=>'#3b82f6', 'icone'=>'fa-truck-fast',   'status'=>['pendente','preparacao','em_rota','entregue']],
    'retirar'   => ['label'=>'Para Retirar', 'cor'=>'#f97316', 'icone'=>'fa-rotate-left',  'status'=>['em_retirada','devolvido']],
    'finalizado'=> ['label'=>'Finalizados',  'cor'=>'#6b7280', 'icone'=>'fa-circle-check', 'status'=>['finalizado']],
];

$statusConfig = [
    'pendente'    => ['label'=>'Pendente',      'cor'=>'#f59e0b', 'icone'=>'fa-clock'],
    'preparacao'  => ['label'=>'Em Preparação', 'cor'=>'#3b82f6', 'icone'=>'fa-screwdriver-wrench'],
    'em_rota'     => ['label'=>'Em Rota',       'cor'=>'#a855f7', 'icone'=>'fa-truck-fast'],
    'entregue'    => ['label'=>'Entregue',      'cor'=>'#22c55e', 'icone'=>'fa-box-open'],
    'em_retirada' => ['label'=>'Em Retirada',   'cor'=>'#f97316', 'icone'=>'fa-rotate-left'],
    'devolvido'   => ['label'=>'Devolvido',     'cor'=>'#64d2ff', 'icone'=>'fa-warehouse'],
    'finalizado'  => ['label'=>'Finalizado',    'cor'=>'#6b7280', 'icone'=>'fa-circle-check'],
];
?>

<!-- Header -->
<div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;margin-bottom:16px"><button class="btn btn-primary btn-sm" onclick="Modal.open('modal-nova-demanda')" style="flex-shrink:0">
    <i class="fa-solid fa-plus"></i> <span class="hide-mobile">Nova Demanda</span><span class="show-mobile">Nova</span>
  </button>
</div>

<!-- Filtros visuais -->
<div class="filtros-grid" id="filtros-grupo">
  <?php
    $todosQtd = count($demandas);
    $filtrosConfig = [
      'todos'     => ['label'=>'Todas',         'qtd'=>$todosQtd, 'icone'=>'fa-list',         'cor'=>'#FFD600', 'bg'=>'rgba(255,214,0,.08)',   'borda'=>'rgba(255,214,0,.3)'],
      'enviar'    => ['label'=>'Para Enviar',    'qtd'=>count(array_filter($demandas, fn($d)=>in_array($d['status'],['pendente','preparacao','em_rota','entregue']))), 'icone'=>'fa-truck-fast',   'cor'=>'#3b82f6','bg'=>'rgba(59,130,246,.08)','borda'=>'rgba(59,130,246,.3)'],
      'retirar'   => ['label'=>'Para Retirar',   'qtd'=>count(array_filter($demandas, fn($d)=>in_array($d['status'],['em_retirada','devolvido']))), 'icone'=>'fa-rotate-left',  'cor'=>'#f97316','bg'=>'rgba(249,115,22,.08)','borda'=>'rgba(249,115,22,.3)'],
      'finalizado'=> ['label'=>'Finalizados',    'qtd'=>count(array_filter($demandas, fn($d)=>$d['status']==='finalizado')), 'icone'=>'fa-circle-check',  'cor'=>'#22c55e','bg'=>'rgba(34,197,94,.08)', 'borda'=>'rgba(34,197,94,.3)'],
    ];
    foreach($filtrosConfig as $fk => $f):
  ?>
  <button id="filtro-<?= $fk ?>" onclick="filtrarGrupo('<?= $fk ?>',this)"
    class="filtro-card <?= $fk==='todos'?'filtro-ativo':'' ?>" style="--fc:<?= $f['cor'] ?>">
    <span class="filtro-card-icon"><i class="fa-solid <?= $f['icone'] ?>"></i></span>
    <span class="filtro-card-val"><?= $f['qtd'] ?></span>
    <span class="filtro-card-lbl"><?= $f['label'] ?></span>
  </button>
  <?php endforeach; ?>
</div>

<!-- Filtro de mês -->
<?php
  $meses = [];
  foreach($demandas as $d) {
    if($d['data_evento']) {
      $m = date('Y-m', strtotime($d['data_evento']));
      $meses[$m] = date('M/Y', strtotime($d['data_evento']));
    }
  }
  ksort($meses);
?>
<div style="margin-bottom:16px">
  <select id="select-mes" onchange="filtrarMes(this.value)" class="form-control" style="width:auto;min-width:180px">
    <option value="">Todos os meses</option>
    <?php foreach($meses as $val => $label): ?>
    <option value="<?= $val ?>"><?= $label ?></option>
    <?php endforeach; ?>
  </select>
</div>

<!-- Tabela -->
<div class="table-wrap">
  <table class="table" id="tabela-demandas">
    <thead>
      <tr>
        <th>Demanda</th>
        <th>Status</th>
        <th>Data</th>
        <th class="hide-mobile">Letreiro</th>
        <th class="hide-mobile">Motorista</th>
        <th class="hide-mobile"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($demandas as $d):
        $sc    = $statusConfig[$d['status']] ?? ['label'=>$d['status'],'cor'=>'#888'];
        $grupo = '';
        foreach($grupos as $gk => $g) { if(in_array($d['status'], $g['status'])) { $grupo = $gk; break; } }
      ?>
      <tr class="demanda-row" data-status="<?= $d['status'] ?>" data-grupo="<?= $grupo ?>" data-mes="<?= $d['data_evento'] ? date('Y-m',strtotime($d['data_evento'])) : '' ?>"
          onclick="verDetalhes(<?= $d['id'] ?>)" style="cursor:pointer">
        <td>
          <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($d['titulo']) ?></div>
          <div style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($d['cliente_nome'] ?? '–') ?></div>
        </td>
        <td>
          <span class="badge badge-<?= $d['status'] ?>"><?= $sc['label'] ?></span>
        </td>
        <td style="font-size:12px;color:var(--text3);white-space:nowrap">
          <?= $d['data_evento'] ? date('d/m', strtotime($d['data_evento'])) : '–' ?>
          <?php if($d['horario']): ?>
          <div style="font-size:10px"><?= substr($d['horario'],0,5) ?></div>
          <?php endif; ?>
        </td>
        <td class="hide-mobile" style="font-family:'Bebas Neue',sans-serif;font-size:18px;color:var(--yellow)">
          <?= htmlspecialchars($d['letreiros_texto'] ?? '–') ?>
        </td>
        <td class="hide-mobile" style="font-size:12px"><?= htmlspecialchars($d['motorista_nome'] ?? '–') ?></td>
        <td class="hide-mobile">
          <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation();verDetalhes(<?= $d['id'] ?>)">
            <i class="fa-solid fa-eye"></i>
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($demandas)): ?>
      <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text3)">
        Nenhuma demanda. <button class="btn btn-ghost btn-sm" onclick="Modal.open('modal-nova-demanda')">Criar primeira</button>
      </td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal Nova Demanda -->
<div class="modal-overlay" id="modal-nova-demanda">
  <div class="modal" style="max-width:700px">
    <div class="modal-header">
      <h3>Nova Demanda</h3>
      <button class="modal-close" data-close-modal="modal-nova-demanda"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="max-height:75vh;overflow-y:auto">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:12px">Informações do evento</div>
      <div class="form-row" style="margin-bottom:20px">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Título *</label>
          <input type="text" class="form-control" id="nd-titulo" placeholder="Ex: Casamento João e Maria">
        </div>
        <div class="form-group">
          <label class="form-label">Cliente</label>
          <select class="form-control" id="nd-cliente">
            <option value="">Selecionar...</option>
            <?php foreach($clientes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- ENTREGA -->
        <div class="form-group" style="grid-column:1/-1">
          <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--yellow);margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--border)">
            <i class="fa-solid fa-truck-fast"></i> Entrega
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Data de entrega</label>
          <input type="date" class="form-control" id="nd-data">
        </div>
        <div class="form-group">
          <label class="form-label">Horário de entrega</label>
          <input type="time" class="form-control" id="nd-hora">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Motorista de entrega</label>
          <select class="form-control" id="nd-motorista">
            <option value="">Selecionar...</option>
            <?php foreach($motoristas as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- RETIRADA -->
        <div class="form-group" style="grid-column:1/-1">
          <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--blue);margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--border)">
            <i class="fa-solid fa-rotate-left"></i> Retirada
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Data de retirada</label>
          <input type="date" class="form-control" id="nd-data-ret">
        </div>
        <div class="form-group">
          <label class="form-label">Horário de retirada</label>
          <input type="time" class="form-control" id="nd-hora-ret">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Motorista de retirada</label>
          <select class="form-control" id="nd-motorista-ret">
            <option value="">Mesmo da entrega</option>
            <?php foreach($motoristas as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Endereço</label>
          <input type="text" class="form-control" id="nd-endereco" placeholder="Rua, número, bairro, cidade">
        </div>
        <div class="form-group">
          <label class="form-label">Contato</label>
          <input type="text" class="form-control" id="nd-telefone" placeholder="(11) 99999-0000">
        </div>
        <div class="form-group">
          <label class="form-label">Observações</label>
          <textarea class="form-control" id="nd-obs" rows="2" placeholder="Informações adicionais..."></textarea>
        </div>
      </div>

      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:12px">
        <i class="fa-solid fa-font" style="color:var(--yellow)"></i> Compositor de Letreiros
      </div>
      <div id="trechos-container"></div>
      <button type="button" class="btn btn-ghost btn-sm" onclick="addTrecho()" style="width:100%;margin-bottom:14px;border-style:dashed">
        <i class="fa-solid fa-plus"></i> Adicionar trecho com tipo diferente
      </button>
      <div style="background:var(--bg3);border-radius:12px;padding:14px" id="preview-letras">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:10px">Preview</div>
        <div id="preview-chars" style="display:flex;flex-wrap:wrap;gap:6px">
          <span style="font-size:12px;color:var(--text3)">Digite o texto acima...</span>
        </div>
        <div id="preview-alerta" style="margin-top:10px;font-size:12px;color:var(--danger);display:none">
          <i class="fa-solid fa-triangle-exclamation"></i> <span id="preview-alerta-txt"></span>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-nova-demanda">Cancelar</button>
      <button class="btn btn-primary" id="btn-criar-demanda" onclick="criarDemanda()">
        <i class="fa-solid fa-check"></i> Criar Demanda
      </button>
    </div>
  </div>
</div>

<!-- Modal Detalhes -->
<div class="modal-overlay" id="modal-detalhe">
  <div class="modal" style="max-width:560px">
    <div class="modal-header">
      <div>
        <h3 id="det-titulo" style="font-size:16px"></h3>
        <div id="det-badge" style="margin-top:4px"></div>
      </div>
      <button class="modal-close" data-close-modal="modal-detalhe"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="det-body" style="max-height:70vh;overflow-y:auto"></div>
  </div>
</div>


<script>
var TIPOS_LETREIRO    = <?= json_encode($tipos, JSON_UNESCAPED_UNICODE) ?>;
var TAMANHOS_LETREIRO = <?= json_encode($tamanhos, JSON_UNESCAPED_UNICODE) ?>;
window._demandasData  = <?= json_encode(array_values($demandas), JSON_UNESCAPED_UNICODE) ?>;
var STATUS_CONFIG     = <?= json_encode($statusConfig, JSON_UNESCAPED_UNICODE) ?>;

// Grupos para o modal de atualização
var STATUS_GRUPOS = {
    enviar:     ['pendente','preparacao','em_rota','entregue'],
    retirar:    ['em_retirada','devolvido'],
    finalizado: ['finalizado']
};

var trechoCount = 0;
document.addEventListener('DOMContentLoaded', function() { addTrecho(); });

// ---- Filtros ----
var _filtroGrupo = 'todos';
var _filtroMes   = '';

function filtrarGrupo(grupo, btn) {
    _filtroGrupo = grupo;
    document.querySelectorAll('.filtro-card').forEach(function(b) {
        b.classList.remove('filtro-ativo');
    });
    btn.classList.add('filtro-ativo');
    aplicarFiltros();
}

function filtrarMes(mes) {
    _filtroMes = mes;
    aplicarFiltros();
}

function aplicarFiltros() {
    document.querySelectorAll('.demanda-row').forEach(function(row) {
        var okGrupo = (_filtroGrupo === 'todos' || row.dataset.grupo === _filtroGrupo);
        var okMes   = !_filtroMes || (row.dataset.mes === _filtroMes);
        row.style.display = (okGrupo && okMes) ? '' : 'none';
    });
}

// ---- Compositor ----
function addTrecho() {
    trechoCount++;
    var id = 'trecho-'+trechoCount;
    var tiposOpts    = TIPOS_LETREIRO.map(function(t){ return '<option value="'+t.id+'">'+escHtml(t.nome)+'</option>'; }).join('');
    var tamanhosOpts = TAMANHOS_LETREIRO.map(function(t){ return '<option value="'+t.id+'">'+escHtml(t.nome)+'</option>'; }).join('');
    var html = '<div class="trecho-box" id="'+id+'">'
        +(trechoCount>1?'<button type="button" class="remove-trecho" onclick="removeTrecho(\''+id+'\')"><i class="fa-solid fa-xmark"></i></button>':'')
        +'<div class="form-row">'
        +'<div class="form-group" style="grid-column:1/-1"><label class="form-label">Texto *</label>'
        +'<input type="text" class="form-control" data-trecho-texto style="font-family:\'Bebas Neue\',sans-serif;font-size:20px;letter-spacing:.06em" placeholder="CASA COMIGO?" oninput="atualizarPreview()"></div>'
        +'<div class="form-group"><label class="form-label">Tipo *</label><select class="form-control" data-trecho-tipo onchange="atualizarPreview()">'+tiposOpts+'</select></div>'
        +'<div class="form-group"><label class="form-label">Tamanho *</label><select class="form-control" data-trecho-tamanho onchange="atualizarPreview()">'+tamanhosOpts+'</select></div>'
        +'</div></div>';
    document.getElementById('trechos-container').insertAdjacentHTML('beforeend', html);
}
function removeTrecho(id) { document.getElementById(id).remove(); atualizarPreview(); }

function coletarTrechos() {
    var result = [];
    document.querySelectorAll('.trecho-box').forEach(function(box) {
        var texto = box.querySelector('[data-trecho-texto]').value.trim();
        if (!texto) return;
        result.push({ texto:texto.toUpperCase(), tipo_id:box.querySelector('[data-trecho-tipo]').value, tamanho_id:box.querySelector('[data-trecho-tamanho]').value });
    });
    return result;
}

async function atualizarPreview() {
    var trechos = coletarTrechos();
    if (!trechos.length) return;
    var contagem = {};
    trechos.forEach(function(tr) {
        tr.texto.replace(/\s/g,'').split('').forEach(function(ch) {
            var k = ch+'|'+tr.tipo_id+'_'+tr.tamanho_id;
            if (!contagem[k]) contagem[k] = {char:ch, tipo_id:tr.tipo_id, tamanho_id:tr.tamanho_id, qtd:0};
            contagem[k].qtd++;
        });
    });
    var fd = new FormData(); fd.append('_csrf',CSRF); fd.append('itens', JSON.stringify(Object.values(contagem)));
    try {
        var res = await Api.post('/api/estoque/letreiros/verificar', fd);
        var html = ''; var alertas = [];
        res.itens.forEach(function(item) {
            var ok = item.disponivel >= item.qtd;
            if (!ok) alertas.push('"'+item.char+'" (precisa '+item.qtd+', tem '+item.disponivel+')');
            html += '<div class="char-pill '+(ok?'ok':'sem')+'">'
                +'<span style="font-family:\'Bebas Neue\',sans-serif;font-size:20px;color:'+(ok?'var(--yellow)':'var(--danger)')+'">'+escHtml(item.char)+'</span>'
                +'<span style="font-size:9px;color:'+(ok?'var(--text3)':'var(--danger)')+'">'+item.qtd+'×</span></div>';
        });
        document.getElementById('preview-chars').innerHTML = html;
        var al = document.getElementById('preview-alerta');
        if (alertas.length) { document.getElementById('preview-alerta-txt').textContent='Sem estoque: '+alertas.join(', '); al.style.display='block'; }
        else al.style.display = 'none';
    } catch(e) {}
}

async function criarDemanda() {
    var titulo = document.getElementById('nd-titulo').value.trim();
    if (!titulo) return Toast.warning('Atenção','Informe o título.');
    var btn = document.getElementById('btn-criar-demanda');
    setLoadingBtn(btn, true, 'Criando...');
    var fd = new FormData();
    fd.append('_csrf', CSRF);
    fd.append('titulo',               document.getElementById('nd-titulo').value);
    fd.append('cliente_id',           document.getElementById('nd-cliente').value);
    fd.append('motorista_id',         document.getElementById('nd-motorista').value);
    fd.append('motorista_retirada_id',document.getElementById('nd-motorista-ret').value);
    fd.append('data_evento',          document.getElementById('nd-data').value);
    fd.append('horario',              document.getElementById('nd-hora').value);
    fd.append('horario_retirada',     document.getElementById('nd-hora-ret').value);
    fd.append('data_retirada',        document.getElementById('nd-data-ret').value);
    fd.append('endereco',             document.getElementById('nd-endereco').value);
    fd.append('telefone',             document.getElementById('nd-telefone').value);
    fd.append('observacoes',          document.getElementById('nd-obs').value);
    fd.append('trechos',              JSON.stringify(coletarTrechos()));
    try {
        await Api.post('/api/demandas', fd);
        Toast.success('Criada!','Demanda criada com sucesso.');
        Modal.close('modal-nova-demanda');
        setTimeout(function(){ location.reload(); }, 700);
    } catch(e) { Toast.error('Erro', e.message); }
    finally { setLoadingBtn(btn, false, '<i class="fa-solid fa-check"></i> Criar Demanda'); }
}

// ---- Detalhes ----
async function verDetalhes(id) {
    var res = await Api.get('/api/demandas/'+id);
    if (!res) return;
    var d = res.demanda; var lets = res.letreiros || [];
    var sc = STATUS_CONFIG[d.status] || {label:d.status, cor:'#888'};

    document.getElementById('det-titulo').textContent = d.titulo;
    document.getElementById('det-badge').innerHTML    = '<span class="badge badge-'+d.status+'">'+sc.label+'</span>';

    // Letreiros agrupados
    var gruposLet = {};
    lets.forEach(function(l){ var k=l.tipo_nome+' '+l.tamanho_nome; if(!gruposLet[k]) gruposLet[k]={nome:k,chars:[]}; gruposLet[k].chars.push(l); });
    var letHtml = Object.values(gruposLet).map(function(g) {
        var texto = g.chars.map(function(c){return c.caractere;}).join('');
        return '<div style="margin-bottom:10px">'
            +'<div style="font-size:10px;color:var(--text3);margin-bottom:4px">'+escHtml(g.nome)+'</div>'
            +'<div style="font-family:\'Bebas Neue\',sans-serif;font-size:28px;color:var(--yellow);letter-spacing:.06em;line-height:1">'+escHtml(texto)+'</div>'
            +'</div>';
    }).join('') || '<p style="color:var(--text3);font-size:13px">Sem letreiros vinculados</p>';

    // Botões de status agrupados
    var grupoAtual = '';
    Object.keys(STATUS_GRUPOS).forEach(function(gk) { if(STATUS_GRUPOS[gk].includes(d.status)) grupoAtual = gk; });

    var statusBtns = '<div style="display:flex;flex-direction:column;gap:10px">';

    // Grupo Enviar
    statusBtns += '<div><div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#3b82f6;margin-bottom:6px"><i class="fa-solid fa-truck-fast"></i> Enviar</div>'
        +'<div style="display:flex;gap:6px;flex-wrap:wrap">';
    ['pendente','preparacao','em_rota','entregue'].forEach(function(s) {
        var inf = STATUS_CONFIG[s]; var isCur = d.status===s;
        statusBtns += '<button onclick="mudarStatus('+d.id+',\''+s+'\')" class="btn '+(isCur?'btn-primary':'btn-ghost')+' btn-sm">'+inf.label+'</button>';
    });
    statusBtns += '</div></div>';

    // Grupo Retirar
    statusBtns += '<div><div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#f97316;margin-bottom:6px"><i class="fa-solid fa-rotate-left"></i> Retirar</div>'
        +'<div style="display:flex;gap:6px;flex-wrap:wrap">';
    ['em_retirada','devolvido'].forEach(function(s) {
        var inf = STATUS_CONFIG[s]; var isCur = d.status===s;
        statusBtns += '<button onclick="mudarStatus('+d.id+',\''+s+'\')" class="btn '+(isCur?'btn-primary':'btn-ghost')+' btn-sm">'+inf.label+'</button>';
    });
    statusBtns += '</div></div>';

    // Finalizar
    statusBtns += '<div style="display:flex;gap:8px;padding-top:8px;border-top:1px solid var(--border)">'
        +'<button onclick="mudarStatus('+d.id+',\'finalizado\')" class="btn '+(d.status==='finalizado'?'btn-primary':'btn-ghost')+' btn-sm"><i class="fa-solid fa-circle-check"></i> Finalizar</button>'
        +'<button onclick="cancelarDemanda('+d.id+')" class="btn btn-ghost btn-sm" style="color:var(--danger)"><i class="fa-solid fa-ban"></i> Cancelar</button>'
        +'</div>';

    statusBtns += '</div>';

    document.getElementById('det-body').innerHTML =
        '<div style="display:flex;justify-content:flex-end;margin-bottom:12px">'
        +'<button class="btn btn-ghost btn-sm" onclick="abrirEditar('+d.id+')"><i class="fa-solid fa-pen-to-square"></i> Editar demanda</button>'
        +'</div>'
        +'<div class="form-row" style="margin-bottom:16px">'
        +'<div><div style="font-size:10px;color:var(--text3)">Cliente</div><div style="font-weight:500;font-size:14px">'+escHtml(d.cliente_nome||'—')+'</div></div>'
        +'<div><div style="font-size:10px;color:var(--text3)">Data Entrega / Hora</div><div style="font-weight:500;font-size:14px">'+(d.data_evento?dataBR(d.data_evento):'—')+(d.horario?' '+d.horario.substring(0,5):'')+'</div></div>'
        +'<div><div style="font-size:10px;color:var(--text3)">Data Retirada / Hora</div><div style="font-size:13px">'+(d.data_retirada?dataBR(d.data_retirada):'—')+(d.horario_retirada?' '+d.horario_retirada.substring(0,5):'')+'</div></div>'
        +'<div><div style="font-size:10px;color:var(--text3)">Motorista entrega</div><div style="font-size:13px">'+escHtml(d.motorista_nome||'—')+'</div></div>'
        +'<div><div style="font-size:10px;color:var(--text3)">Motorista retirada</div><div style="font-size:13px">'+escHtml(d.motorista_ret_nome||'—')+'</div></div>'
        +'<div><div style="font-size:10px;color:var(--text3)">Endereço</div><div style="font-size:12px;color:var(--text3)">'+escHtml(d.endereco||'—')+'</div></div>'
        +'</div>'
        +(letHtml !== '<p style="color:var(--text3);font-size:13px">Sem letreiros vinculados</p>'
            ? '<div style="background:var(--bg3);border-radius:12px;padding:14px;margin-bottom:16px">'+letHtml+'</div>' : '')
        +'<div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:10px">Atualizar Status</div>'
        +statusBtns;

    Modal.open('modal-detalhe');
}

async function mudarStatus(id, status) {
    var fd = new FormData(); fd.append('_csrf',CSRF); fd.append('status',status);
    try {
        await Api.post('/api/demandas/'+id+'/status-admin', fd);
        Toast.success('Atualizado!','');
        Modal.close('modal-detalhe');
        setTimeout(function(){ location.reload(); }, 500);
    } catch(e) { Toast.error('Erro',e.message); }
}

async function cancelarDemanda(id) {
    Modal.confirm('Cancelar demanda','As letras reservadas voltarão ao estoque. Confirmar?', async function() {
        var fd = new FormData(); fd.append('_csrf',CSRF);
        try { await Api.post('/api/demandas/'+id+'/cancelar',fd); setTimeout(function(){ location.reload(); },700); }
        catch(e){ Toast.error('Erro',e.message); }
    });
}

// ── EDITAR DEMANDA ──
function abrirEditar(id) {
    var d = window._demandasData ? window._demandasData.find(function(x){ return x.id==id; }) : null;
    if (!d) { Toast.error('Erro','Dados não encontrados'); return; }
    document.getElementById('ed-id').value           = d.id;
    document.getElementById('ed-titulo').value        = d.titulo || '';
    document.getElementById('ed-cliente').value       = d.cliente_id || '';
    document.getElementById('ed-endereco').value      = d.endereco || '';
    document.getElementById('ed-telefone').value      = d.telefone || '';
    document.getElementById('ed-prioridade').value    = d.prioridade || 'normal';
    document.getElementById('ed-data').value          = d.data_evento || '';
    document.getElementById('ed-hora').value          = d.horario ? d.horario.substring(0,5) : '';
    document.getElementById('ed-motorista').value     = d.motorista_id || '';
    document.getElementById('ed-data-ret').value      = d.data_retirada || '';
    document.getElementById('ed-hora-ret').value      = d.horario_retirada ? d.horario_retirada.substring(0,5) : '';
    document.getElementById('ed-motorista-ret').value = d.motorista_retirada_id || '';
    document.getElementById('ed-obs').value           = d.observacoes || '';
    Modal.close('modal-detalhe');
    Modal.open('modal-editar');
}

async function salvarEdicao() {
    var btn = document.getElementById('btn-salvar-edicao');
    btn.disabled = true;
    var id = document.getElementById('ed-id').value;
    var fd = new FormData();
    fd.append('_csrf',              CSRF);
    fd.append('titulo',             document.getElementById('ed-titulo').value);
    fd.append('cliente_id',         document.getElementById('ed-cliente').value);
    fd.append('endereco',           document.getElementById('ed-endereco').value);
    fd.append('telefone',           document.getElementById('ed-telefone').value);
    fd.append('prioridade',         document.getElementById('ed-prioridade').value);
    fd.append('data_evento',        document.getElementById('ed-data').value);
    fd.append('horario',            document.getElementById('ed-hora').value);
    fd.append('motorista_id',       document.getElementById('ed-motorista').value);
    fd.append('data_retirada',      document.getElementById('ed-data-ret').value);
    fd.append('horario_retirada',   document.getElementById('ed-hora-ret').value);
    fd.append('motorista_retirada_id', document.getElementById('ed-motorista-ret').value);
    fd.append('observacoes',        document.getElementById('ed-obs').value);
    try {
        await Api.post('/api/demandas/'+id+'/editar', fd);
        Toast.success('Demanda atualizada!','');
        Modal.close('modal-editar');
        setTimeout(function(){ location.reload(); }, 600);
    } catch(e) {
        Toast.error('Erro', e.message);
        btn.disabled = false;
    }
}
</script>

<!-- MODAL EDITAR DEMANDA -->
<div class="modal-overlay" id="modal-editar">
  <div class="modal" style="max-width:640px">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fa-solid fa-pen-to-square" style="color:var(--yellow)"></i> Editar Demanda</h3>
      <button class="modal-close" data-close-modal="modal-editar"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" style="max-height:80vh;overflow-y:auto">
      <input type="hidden" id="ed-id">
      <div class="form-grid">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Título *</label>
          <input type="text" class="form-control" id="ed-titulo">
        </div>
        <div class="form-group">
          <label class="form-label">Cliente</label>
          <select class="form-control" id="ed-cliente">
            <option value="">Selecionar...</option>
            <?php foreach($clientes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Endereço</label>
          <input type="text" class="form-control" id="ed-endereco">
        </div>
        <div class="form-group">
          <label class="form-label">Contato</label>
          <input type="text" class="form-control" id="ed-telefone">
        </div>
        <div class="form-group">
          <label class="form-label">Prioridade</label>
          <select class="form-control" id="ed-prioridade">
            <option value="normal">Normal</option>
            <option value="alta">Alta</option>
            <option value="urgente">Urgente</option>
          </select>
        </div>
        <!-- ENTREGA -->
        <div class="form-group" style="grid-column:1/-1">
          <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--yellow);padding-bottom:6px;border-bottom:1px solid var(--border)"><i class="fa-solid fa-truck-fast"></i> Entrega</div>
        </div>
        <div class="form-group">
          <label class="form-label">Data de entrega</label>
          <input type="date" class="form-control" id="ed-data">
        </div>
        <div class="form-group">
          <label class="form-label">Horário de entrega</label>
          <input type="time" class="form-control" id="ed-hora">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Motorista de entrega</label>
          <select class="form-control" id="ed-motorista">
            <option value="">Selecionar...</option>
            <?php foreach($motoristas as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- RETIRADA -->
        <div class="form-group" style="grid-column:1/-1">
          <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#64d2ff;padding-bottom:6px;border-bottom:1px solid var(--border)"><i class="fa-solid fa-rotate-left"></i> Retirada</div>
        </div>
        <div class="form-group">
          <label class="form-label">Data de retirada</label>
          <input type="date" class="form-control" id="ed-data-ret">
        </div>
        <div class="form-group">
          <label class="form-label">Horário de retirada</label>
          <input type="time" class="form-control" id="ed-hora-ret">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Motorista de retirada</label>
          <select class="form-control" id="ed-motorista-ret">
            <option value="">Mesmo da entrega</option>
            <?php foreach($motoristas as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Observações</label>
          <textarea class="form-control" id="ed-obs" rows="2"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-editar">Cancelar</button>
      <button class="btn btn-primary" id="btn-salvar-edicao" onclick="salvarEdicao()">
        <i class="fa-solid fa-check"></i> Salvar Alterações
      </button>
    </div>
  </div>
</div>