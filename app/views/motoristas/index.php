<?php
$motoristas = Database::fetchAll(
    "SELECT u.*, m.cpf, m.cnh, m.veiculo, m.placa, m.disponivel,
            (SELECT COUNT(*) FROM demandas WHERE motorista_id=u.id AND status='finalizado') as total_entregas,
            (SELECT COUNT(*) FROM demandas WHERE motorista_id=u.id AND status NOT IN ('finalizado','cancelado')) as demandas_ativas
     FROM usuarios u
     LEFT JOIN motoristas m ON m.usuario_id = u.id
     WHERE u.tipo = 'motorista'
     ORDER BY u.nome"
);
$ativos     = count(array_filter($motoristas, fn($m) => $m['status']==='ativo'));
$disponiveis= count(array_filter($motoristas, fn($m) => $m['status']==='ativo' && $m['disponivel']==1));
$ocupados   = $ativos - $disponiveis;
?>

<div style="display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:24px">
  <button class="btn btn-primary" onclick="Modal.open('modal-novo-motorista')">
    <i class="fa-solid fa-plus"></i> Novo Motorista
  </button>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:24px">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(255,214,0,.12);color:var(--yellow)"><i class="fa-solid fa-users"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= count($motoristas) ?></div><div class="stat-card-label">Total</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(76,217,100,.12);color:#4cd964"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= $ativos ?></div><div class="stat-card-label">Ativos</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(100,210,255,.12);color:#64d2ff"><i class="fa-solid fa-circle-dot"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= $disponiveis ?></div><div class="stat-card-label">Disponíveis</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(245,158,11,.12);color:#f59e0b"><i class="fa-solid fa-truck-fast"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= $ocupados ?></div><div class="stat-card-label">Ocupados</div></div>
  </div>
</div>

<!-- Busca e filtros -->
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:20px">
  <div style="position:relative;flex:1;min-width:200px;max-width:320px">
    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text3);font-size:13px"></i>
    <input type="text" placeholder="Buscar motorista..." id="busca-mot"
      class="form-control search-input" style="padding-left:36px"
      oninput="filtrarMotoristas()">
  </div>
  <div style="display:flex;gap:6px">
    <button class="btn btn-primary btn-sm" id="fil-todos"    onclick="setFiltro('todos',this)">Todos</button>
    <button class="btn btn-ghost  btn-sm" id="fil-ativo"     onclick="setFiltro('ativo',this)">Ativos</button>
    <button class="btn btn-ghost  btn-sm" id="fil-disponivel" onclick="setFiltro('disponivel',this)">Disponíveis</button>
    <button class="btn btn-ghost  btn-sm" id="fil-inativo"   onclick="setFiltro('inativo',this)">Inativos</button>
  </div>
</div>

<!-- Grid de cards -->
<?php if(empty($motoristas)): ?>
<div style="text-align:center;padding:60px 20px;color:var(--text3)">
  <i class="fa-solid fa-truck-fast" style="font-size:40px;opacity:.2;display:block;margin-bottom:12px"></i>
  <h3 style="margin-bottom:8px">Nenhum motorista cadastrado</h3>
  <button class="btn btn-primary" onclick="Modal.open('modal-novo-motorista')"><i class="fa-solid fa-plus"></i> Cadastrar primeiro motorista</button>
</div>
<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px" id="grid-motoristas">
  <?php foreach($motoristas as $m):
    $initials = implode('', array_map(fn($n)=>$n[0], array_slice(explode(' ', $m['nome']), 0, 2)));
    $disponivel = $m['status']==='ativo' && $m['disponivel']==1;
    $dotColor   = $m['status']!=='ativo' ? '#555' : ($disponivel ? '#4cd964' : '#f59e0b');
    $dotLabel   = $m['status']!=='ativo' ? 'Inativo' : ($disponivel ? 'Disponível' : 'Ocupado');
  ?>
  <div class="mot-card" data-status="<?= $m['status'] ?>" data-disp="<?= $m['disponivel'] ?>" data-nome="<?= strtolower($m['nome']) ?>">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px">
      <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--yellow),#ff9900);display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-size:17px;font-weight:800;color:#000;flex-shrink:0">
        <?= strtoupper($initials) ?>
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-weight:600;font-size:15px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($m['nome']) ?></div>
        <div style="font-size:12px;color:var(--text3)"><?= htmlspecialchars($m['email']) ?></div>
      </div>
      <div style="display:flex;flex-direction:column;align-items:center;gap:3px">
        <div style="width:9px;height:9px;border-radius:50%;background:<?= $dotColor ?>;box-shadow:0 0 8px <?= $dotColor ?>"></div>
        <span style="font-size:10px;color:var(--text3)"><?= $dotLabel ?></span>
      </div>
    </div>

    <div style="display:flex;gap:8px;margin-bottom:14px">
      <div style="flex:1;background:var(--bg3);border-radius:8px;padding:10px;text-align:center">
        <div style="font-size:20px;font-weight:700;color:var(--yellow);font-family:'Sora',sans-serif"><?= $m['demandas_ativas'] ?></div>
        <div style="font-size:10px;color:var(--text3)">ativas</div>
      </div>
      <div style="flex:1;background:var(--bg3);border-radius:8px;padding:10px;text-align:center">
        <div style="font-size:20px;font-weight:700;color:#4cd964;font-family:'Sora',sans-serif"><?= $m['total_entregas'] ?></div>
        <div style="font-size:10px;color:var(--text3)">entregas</div>
      </div>
      <?php if($m['veiculo']): ?>
      <div style="flex:2;background:var(--bg3);border-radius:8px;padding:10px">
        <div style="font-size:11px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($m['veiculo']) ?></div>
        <?php if($m['placa']): ?><div style="font-size:10px;color:var(--text3)"><?= htmlspecialchars($m['placa']) ?></div><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <div style="display:flex;gap:8px">
      <?php if($m['telefone']): ?>
      <a href="https://wa.me/55<?= preg_replace('/\D/','',$m['telefone']) ?>" target="_blank"
         style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:9px;border-radius:8px;background:rgba(37,211,102,.12);color:#25d366;text-decoration:none;font-size:12px;font-weight:500">
        <i class="fa-brands fa-whatsapp"></i> WhatsApp
      </a>
      <?php endif; ?>
      <button onclick="verDemandas(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['nome'])) ?>')"
         style="flex:1;padding:9px;border-radius:8px;background:var(--bg3);border:1px solid var(--border);color:var(--text);cursor:pointer;font-size:12px;font-weight:500">
        <i class="fa-solid fa-clipboard-list"></i> Demandas
      </button>
      <?php if($m['status']==='ativo'): ?>
      <button onclick="toggleDisp(<?= $m['id'] ?>, <?= $m['disponivel']?0:1 ?>)"
         style="width:36px;height:36px;border-radius:8px;background:var(--bg3);border:1px solid var(--border);color:var(--text3);cursor:pointer;font-size:13px">
        <i class="fa-solid <?= $m['disponivel'] ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
      </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal Novo Motorista -->
<div class="modal-overlay" id="modal-novo-motorista">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3>Novo Motorista</h3>
      <button class="modal-close" data-close-modal="modal-novo-motorista"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Nome completo *</label>
          <input type="text" class="form-control" id="nm-nome" placeholder="Nome do motorista">
        </div>
        <div class="form-group">
          <label class="form-label">E-mail *</label>
          <input type="email" class="form-control" id="nm-email" placeholder="email@fokos.com">
        </div>
        <div class="form-group">
          <label class="form-label">Telefone / WhatsApp</label>
          <input type="text" class="form-control" id="nm-tel" placeholder="(11) 99999-0000">
        </div>
        <div class="form-group">
          <label class="form-label">CPF</label>
          <input type="text" class="form-control" id="nm-cpf" placeholder="000.000.000-00">
        </div>
        <div class="form-group">
          <label class="form-label">CNH</label>
          <input type="text" class="form-control" id="nm-cnh" placeholder="00000000000">
        </div>
        <div class="form-group">
          <label class="form-label">Veículo</label>
          <input type="text" class="form-control" id="nm-veiculo" placeholder="Ex: Fiat Ducato">
        </div>
        <div class="form-group">
          <label class="form-label">Placa</label>
          <input type="text" class="form-control" id="nm-placa" placeholder="ABC-1234">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Senha de acesso *</label>
          <input type="password" class="form-control" id="nm-senha" placeholder="Mínimo 6 caracteres">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-novo-motorista">Cancelar</button>
      <button class="btn btn-primary" id="btn-nm" onclick="criarMotorista()">
        <i class="fa-solid fa-check"></i> Cadastrar
      </button>
    </div>
  </div>
</div>

<!-- Modal Demandas do Motorista -->
<div class="modal-overlay" id="modal-dem-motorista">
  <div class="modal" style="max-width:600px">
    <div class="modal-header">
      <h3 id="dem-mot-title">Demandas</h3>
      <button class="modal-close" data-close-modal="modal-dem-motorista"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="dem-mot-body" style="max-height:60vh;overflow-y:auto"></div>
  </div>
</div>


<script>
var filtroAtual = 'todos';

function setFiltro(f, btn) {
  filtroAtual = f;
  document.querySelectorAll('[id^="fil-"]').forEach(function(b){ b.className = 'btn btn-ghost btn-sm'; });
  btn.className = 'btn btn-primary btn-sm';
  filtrarMotoristas();
}

function filtrarMotoristas() {
  var busca = document.getElementById('busca-mot').value.toLowerCase();
  document.querySelectorAll('.mot-card').forEach(function(card) {
    var nome   = card.dataset.nome || '';
    var status = card.dataset.status;
    var disp   = card.dataset.disp;
    var okBusca = !busca || nome.includes(busca);
    var okFiltro = filtroAtual === 'todos'
      || (filtroAtual === 'ativo'      && status === 'ativo')
      || (filtroAtual === 'disponivel' && status === 'ativo' && disp == 1)
      || (filtroAtual === 'inativo'    && status === 'inativo');
    card.style.display = (okBusca && okFiltro) ? '' : 'none';
  });
}

async function criarMotorista() {
  var nome  = document.getElementById('nm-nome').value.trim();
  var email = document.getElementById('nm-email').value.trim();
  var senha = document.getElementById('nm-senha').value;
  if (!nome || !email || !senha) return Toast.warning('Atenção', 'Nome, e-mail e senha são obrigatórios.');
  var btn = document.getElementById('btn-nm');
  setLoadingBtn(btn, true, 'Cadastrando...');
  var fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('nome',    nome);
  fd.append('email',   email);
  fd.append('senha',   senha);
  fd.append('telefone', document.getElementById('nm-tel').value);
  fd.append('cpf',     document.getElementById('nm-cpf').value);
  fd.append('cnh',     document.getElementById('nm-cnh').value);
  fd.append('veiculo', document.getElementById('nm-veiculo').value);
  fd.append('placa',   document.getElementById('nm-placa').value);
  try {
    await Api.post('/api/motoristas', fd);
    Toast.success('Cadastrado!', nome + ' adicionado à equipe.');
    Modal.close('modal-novo-motorista');
    setTimeout(function(){ location.reload(); }, 800);
  } catch(e) { Toast.error('Erro', e.message); }
  finally { setLoadingBtn(btn, false, '<i class="fa-solid fa-check"></i> Cadastrar'); }
}

async function toggleDisp(id, val) {
  var fd = new FormData();
  fd.append('_csrf', CSRF); fd.append('disponivel', val);
  try {
    await Api.post('/api/motoristas/'+id+'/disponivel', fd);
    Toast.success('Atualizado', val ? 'Marcado como disponível.' : 'Marcado como ocupado.');
    setTimeout(function(){ location.reload(); }, 600);
  } catch(e) { Toast.error('Erro', e.message); }
}

async function verDemandas(id, nome) {
  document.getElementById('dem-mot-title').textContent = 'Demandas — ' + nome;
  document.getElementById('dem-mot-body').innerHTML = '<div style="padding:20px;text-align:center"><i class="fa-solid fa-spinner fa-spin"></i></div>';
  Modal.open('modal-dem-motorista');
  var res = await Api.get('/api/motoristas/'+id+'/demandas');
  var demandas = res ? res.demandas : [];
  if (!demandas.length) {
    document.getElementById('dem-mot-body').innerHTML = '<div style="padding:30px;text-align:center;color:var(--text3)">Nenhuma demanda ativa</div>';
    return;
  }
  var STATUS_LABEL = {pendente:'Pendente',preparacao:'Em Preparação',em_rota:'Em Rota',em_retirada:'Em Retirada',entregue:'Entregue',finalizado:'Finalizado'};
  var STATUS_CLS   = {pendente:'badge-pendente',preparacao:'badge-preparacao',em_rota:'badge-em_rota',em_retirada:'badge-em_rota',entregue:'badge-entregue',finalizado:'badge-ghost'};
  document.getElementById('dem-mot-body').innerHTML = demandas.map(function(d) {
    return '<div style="padding:14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">'
      + '<div style="flex:1"><div style="font-weight:600;margin-bottom:4px">'+escHtml(d.titulo)+'</div>'
      + '<div style="font-size:12px;color:var(--text3)">'+(d.data_evento?dataBR(d.data_evento):'—')+' · '+escHtml(d.cliente_nome||'—')+'</div>'
      + '<div style="font-size:11px;color:var(--text3);margin-top:2px">'+(d.tipo_motorista==='retirada'?'<i class="fa-solid fa-rotate-left"></i> Motorista de retirada':'<i class="fa-solid fa-truck-fast"></i> Motorista de entrega')+'</div>'
      + '</div>'
      + '<span class="badge '+(STATUS_CLS[d.status]||'badge-ghost')+'">'+(STATUS_LABEL[d.status]||d.status)+'</span>'
      + '</div>';
  }).join('');
}
</script>
