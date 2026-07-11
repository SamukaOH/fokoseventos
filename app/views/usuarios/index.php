<?php
$usuarios = Database::fetchAll(
    "SELECT u.*, m.veiculo, m.placa, m.disponivel
     FROM usuarios u
     LEFT JOIN motoristas m ON m.usuario_id = u.id
     ORDER BY u.tipo, u.nome"
);
$usuariosJson = json_encode($usuarios, JSON_UNESCAPED_UNICODE|JSON_HEX_APOS);
$meuId = $_SESSION['user_id'] ?? 0;
?>

<div style="display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:24px">
  <button class="btn btn-primary" onclick="Modal.open('modal-novo-usuario')">
    <i class="fa-solid fa-plus"></i> Novo Usuário
  </button>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px">
  <?php
    $admins = count(array_filter($usuarios, fn($u) => $u['tipo']==='admin'));
    $mots   = count(array_filter($usuarios, fn($u) => $u['tipo']==='motorista'));
    $ativos = count(array_filter($usuarios, fn($u) => $u['status']==='ativo'));
  ?>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(255,214,0,.12);color:var(--yellow)"><i class="fa-solid fa-users"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= count($usuarios) ?></div><div class="stat-card-label">Total</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(168,85,247,.12);color:#a855f7"><i class="fa-solid fa-shield"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= $admins ?></div><div class="stat-card-label">Admins</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(59,130,246,.12);color:#3b82f6"><i class="fa-solid fa-truck-fast"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= $mots ?></div><div class="stat-card-label">Motoristas</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(34,197,94,.12);color:#22c55e"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= $ativos ?></div><div class="stat-card-label">Ativos</div></div>
  </div>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr><th>Usuário</th><th class="hide-mobile">Tipo</th><th>Status</th><th style="width:28px"></th></tr>
    </thead>
    <tbody>
      <?php foreach($usuarios as $u): ?>
      <tr class="row-click" onclick="verUsuario(<?= $u['id'] ?>)">
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:34px;height:34px;border-radius:12px;background:linear-gradient(135deg,var(--yellow),var(--yellow-deep));display:flex;align-items:center;justify-content:center;font-weight:700;color:#000;font-size:13px;flex-shrink:0">
              <?= strtoupper(substr($u['nome'],0,2)) ?>
            </div>
            <div style="min-width:0">
              <div style="font-weight:600"><?= htmlspecialchars($u['nome']) ?></div>
              <div class="hide-mobile" style="font-size:12px;color:var(--text3)"><?= htmlspecialchars($u['email']) ?></div>
            </div>
          </div>
        </td>
        <td class="hide-mobile">
          <?php if($u['tipo']==='admin'): ?>
            <span class="badge" style="color:#C6A3FF"><i class="fa-solid fa-shield"></i> Admin</span>
          <?php else: ?>
            <span class="badge badge-em_rota"><i class="fa-solid fa-truck-fast"></i> Motorista</span>
          <?php endif; ?>
        </td>
        <td>
          <span class="badge <?= $u['status']==='ativo' ? 'badge-entregue' : 'badge-cancelado' ?>">
            <?= $u['status'] === 'ativo' ? 'Ativo' : 'Inativo' ?>
          </span>
        </td>
        <td><i class="fa-solid fa-chevron-right"></i></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal Ver Usuário -->
<div class="modal-overlay" id="modal-ver-usuario">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fa-solid fa-user" style="color:var(--yellow)"></i> <span id="vu-nome">Usuário</span></h3>
      <button class="modal-close" data-close-modal="modal-ver-usuario"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="vu-body"></div>
    <div class="modal-footer" style="justify-content:space-between;flex-wrap:wrap;gap:8px">
      <div style="display:flex;gap:8px">
        <button class="btn btn-danger btn-sm" id="vu-btn-desativar" style="display:none"><i class="fa-solid fa-ban"></i> Desativar</button>
        <button class="btn btn-success btn-sm" id="vu-btn-ativar" style="display:none"><i class="fa-solid fa-circle-check"></i> Ativar</button>
      </div>
      <div style="display:flex;gap:8px">
        <button class="btn btn-ghost" data-close-modal="modal-ver-usuario">Fechar</button>
        <button class="btn btn-primary" id="vu-btn-senha"><i class="fa-solid fa-key"></i> Alterar senha</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Novo Usuário -->
<div class="modal-overlay" id="modal-novo-usuario">
  <div class="modal">
    <div class="modal-header">
      <h3>Novo Usuário</h3>
      <button class="modal-close" data-close-modal="modal-novo-usuario"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Nome *</label>
          <input type="text" class="form-control" id="usr-nome" placeholder="Nome completo">
        </div>
        <div class="form-group">
          <label class="form-label">E-mail *</label>
          <input type="email" class="form-control" id="usr-email" placeholder="email@fokos.com">
        </div>
        <div class="form-group">
          <label class="form-label">Telefone</label>
          <input type="text" class="form-control" id="usr-tel" placeholder="(11) 99999-0000">
        </div>
        <div class="form-group">
          <label class="form-label">Tipo *</label>
          <select class="form-control" id="usr-tipo">
            <option value="admin">Administrador</option>
            <option value="motorista">Motorista</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Senha *</label>
          <input type="password" class="form-control" id="usr-senha" placeholder="Mínimo 6 caracteres">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-novo-usuario">Cancelar</button>
      <button class="btn btn-primary" id="btn-salvar-usr" onclick="salvarUsuario()">
        <i class="fa-solid fa-check"></i> Criar Usuário
      </button>
    </div>
  </div>
</div>

<!-- Modal Alterar Senha -->
<div class="modal-overlay" id="modal-senha">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <h3>Alterar Senha — <span id="senha-usr-nome"></span></h3>
      <button class="modal-close" data-close-modal="modal-senha"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="senha-usr-id">
      <div class="form-group">
        <label class="form-label">Nova senha *</label>
        <input type="password" class="form-control" id="nova-senha" placeholder="Mínimo 6 caracteres">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-senha">Cancelar</button>
      <button class="btn btn-primary" onclick="confirmarSenha()"><i class="fa-solid fa-key"></i> Salvar senha</button>
    </div>
  </div>
</div>

<script>
var USUARIOS = <?= $usuariosJson ?>;
var MEU_ID   = <?= (int)$meuId ?>;

function verUsuario(id) {
  var u = USUARIOS.find(function(x){ return x.id == id; });
  if (!u) return;
  var tipoBadge = u.tipo==='admin'
    ? '<span class="badge" style="color:#C6A3FF"><i class="fa-solid fa-shield"></i> Admin</span>'
    : '<span class="badge badge-em_rota"><i class="fa-solid fa-truck-fast"></i> Motorista</span>';
  document.getElementById('vu-nome').textContent = u.nome;
  document.getElementById('vu-body').innerHTML =
    '<div class="detail-grid">'
    +'<div class="detail-item"><div class="detail-item-lbl">Tipo</div><div class="detail-item-val">'+tipoBadge+'</div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">Status</div><div class="detail-item-val"><span class="badge '+(u.status==='ativo'?'badge-entregue':'badge-cancelado')+'">'+(u.status==='ativo'?'Ativo':'Inativo')+'</span></div></div>'
    +'<div class="detail-item full"><div class="detail-item-lbl">E-mail</div><div class="detail-item-val">'+escHtml(u.email||'—')+'</div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">Telefone</div><div class="detail-item-val">'+escHtml(u.telefone||'—')+'</div></div>'
    +'<div class="detail-item"><div class="detail-item-lbl">Último login</div><div class="detail-item-val">'+(u.ultimo_login?u.ultimo_login.substring(0,16).replace('T',' ').split('-').length>1?formatarDataHora(u.ultimo_login):u.ultimo_login:'Nunca')+'</div></div>'
    +(u.veiculo?'<div class="detail-item"><div class="detail-item-lbl">Veículo</div><div class="detail-item-val">'+escHtml(u.veiculo)+'</div></div>':'')
    +(u.placa?'<div class="detail-item"><div class="detail-item-lbl">Placa</div><div class="detail-item-val">'+escHtml(u.placa)+'</div></div>':'')
    +'</div>';
  var podeAlterar = (u.id != MEU_ID);
  document.getElementById('vu-btn-desativar').style.display = (podeAlterar && u.status==='ativo')   ? '' : 'none';
  document.getElementById('vu-btn-ativar').style.display    = (podeAlterar && u.status!=='ativo')   ? '' : 'none';
  document.getElementById('vu-btn-desativar').onclick = function(){ Modal.close('modal-ver-usuario'); desativarUsuario(u.id, u.nome); };
  document.getElementById('vu-btn-ativar').onclick    = function(){ Modal.close('modal-ver-usuario'); ativarUsuario(u.id, u.nome); };
  document.getElementById('vu-btn-senha').onclick     = function(){ Modal.close('modal-ver-usuario'); alterarSenha(u.id, u.nome); };
  Modal.open('modal-ver-usuario');
}

function formatarDataHora(iso) {
  var d = new Date(iso.replace(' ','T'));
  if (isNaN(d)) return iso;
  return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});
}

async function ativarUsuario(id, nome) {
  Modal.confirm('Ativar usuário', 'Reativar o acesso de "' + nome + '"?', async function() {
    var fd = new FormData(); fd.append('_csrf', CSRF);
    try {
      await Api.post('/api/usuarios/' + id + '/ativar', fd);
      Toast.success('Ativado', nome + ' foi reativado.');
      setTimeout(function() { location.reload(); }, 800);
    } catch(e) { Toast.error('Erro', e.message); }
  });
}

async function salvarUsuario() {
  var nome  = document.getElementById('usr-nome').value.trim();
  var email = document.getElementById('usr-email').value.trim();
  var senha = document.getElementById('usr-senha').value;
  if (!nome || !email || !senha) return Toast.warning('Atenção', 'Preencha todos os campos obrigatórios.');
  var btn = document.getElementById('btn-salvar-usr');
  setLoadingBtn(btn, true, 'Criando...');
  var fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('nome', nome); fd.append('email', email);
  fd.append('telefone', document.getElementById('usr-tel').value);
  fd.append('tipo', document.getElementById('usr-tipo').value);
  fd.append('senha', senha);
  try {
    await Api.post('/api/usuarios', fd);
    Toast.success('Criado!', 'Usuário criado com sucesso.');
    Modal.close('modal-novo-usuario');
    setTimeout(function() { location.reload(); }, 800);
  } catch(e) { Toast.error('Erro', e.message); }
  finally { setLoadingBtn(btn, false, '<i class="fa-solid fa-check"></i> Criar Usuário'); }
}

function alterarSenha(id, nome) {
  document.getElementById('senha-usr-id').value   = id;
  document.getElementById('senha-usr-nome').textContent = nome;
  document.getElementById('nova-senha').value = '';
  Modal.open('modal-senha');
}

async function confirmarSenha() {
  var id    = document.getElementById('senha-usr-id').value;
  var senha = document.getElementById('nova-senha').value;
  if (senha.length < 6) return Toast.warning('Atenção', 'Senha deve ter ao menos 6 caracteres.');
  var fd = new FormData();
  fd.append('_csrf', CSRF); fd.append('senha', senha);
  try {
    await Api.post('/api/usuarios/' + id + '/senha', fd);
    Toast.success('Atualizado!', 'Senha alterada com sucesso.');
    Modal.close('modal-senha');
  } catch(e) { Toast.error('Erro', e.message); }
}

async function desativarUsuario(id, nome) {
  Modal.confirm('Desativar usuário', 'Desativar "' + nome + '"?', async function() {
    var fd = new FormData(); fd.append('_csrf', CSRF);
    try {
      await Api.post('/api/usuarios/' + id + '/desativar', fd);
      Toast.success('Desativado', nome + ' foi desativado.');
      setTimeout(function() { location.reload(); }, 800);
    } catch(e) { Toast.error('Erro', e.message); }
  });
}
</script>
