<?php
$pageTitles = [
  'dashboard'  => ['Dashboard',  'Visão geral do sistema'],
  'demandas'   => ['Demandas',   'Gestão de eventos e entregas'],
  'estoque'    => ['Estoque',    'Controle de materiais'],
  'financeiro' => ['Financeiro', 'Receitas e despesas'],
  'motoristas' => ['Motoristas', 'Equipe de entrega'],
];
$cp    = $currentPage ?? 'dashboard';
$title = $pageTitles[$cp] ?? [$cp, ''];
?>
<header class="topbar" id="topbar">
  <button class="icon-btn hamburger" id="hamburger">
    <i class="fa-solid fa-bars"></i>
  </button>

  <div class="topbar-title">
    <?= htmlspecialchars($title[0]) ?>
    <?php if($title[1]): ?><small><?= htmlspecialchars($title[1]) ?></small><?php endif; ?>
  </div>

  <div class="topbar-actions">
    <!-- Calendário -->
    <a href="<?= APP_URL ?>/calendario" class="icon-btn" title="Calendário">
      <i class="fa-solid fa-calendar-days"></i>
    </a>

    <!-- Notificações -->
    <div style="position:relative">
      <button class="icon-btn" onclick="Notifs.toggle()" id="notif-btn" style="position:relative">
        <i class="fa-solid fa-bell"></i>
        <div class="notif-badge" id="notif-badge" style="display:none"></div>
      </button>
    </div>

    <!-- Logout -->
    <a href="<?= APP_URL ?>/logout" class="icon-btn" title="Sair">
      <i class="fa-solid fa-right-from-bracket"></i>
    </a>
  </div>
</header>

<script>
// Criar dropdown de notificações no body para evitar herança de CSS
(function() {
  var dd = document.createElement('div');
  dd.id = 'notif-dropdown';
  dd.className = 'notif-dropdown';
  dd.innerHTML =
    '<div class="notif-dropdown-header">'
    + '<h4><span class="notif-header-dot"></span> Notificações</h4>'
    + '<button class="btn btn-ghost btn-sm" onclick="marcarTodasNotifs()" style="font-size:11px">Marcar lidas</button>'
    + '</div>'
    + '<div class="notif-list" id="notif-list">'
    + '<div style="padding:20px;text-align:center;color:#666;font-size:13px">Carregando...</div>'
    + '</div>';
  document.body.appendChild(dd);
})();

function posicionarDropdown() {
  var btn = document.getElementById('notif-btn');
  var dd  = document.getElementById('notif-dropdown');
  if (!btn || !dd) return;
  if (window.innerWidth <= 768) {
    // Mobile: painel inteiro deslizando de cima (igual ao app do motorista)
    dd.style.top = ''; dd.style.right = ''; dd.style.position = '';
    return;
  }
  var rect = btn.getBoundingClientRect();
  dd.style.position   = 'fixed';
  dd.style.top        = (rect.bottom + 10) + 'px';
  dd.style.right      = (window.innerWidth - rect.right) + 'px';
}

document.addEventListener('click', function(e) {
  var dd  = document.getElementById('notif-dropdown');
  var btn = document.getElementById('notif-btn');
  if (dd && dd.classList.contains('open') && !dd.contains(e.target) && btn && !btn.contains(e.target)) {
    dd.classList.remove('open');
  }
});

var hamburger = document.getElementById('hamburger');
var sidebar   = document.getElementById('sidebar');

function abrirSidebar() {
  sidebar.classList.add('open');
  var ov = document.getElementById('sidebar-overlay');
  if (ov) ov.classList.add('open');
}
function fecharSidebar() {
  sidebar.classList.remove('open');
  var ov = document.getElementById('sidebar-overlay');
  if (ov) ov.classList.remove('open');
}

if (hamburger && sidebar) {
  hamburger.addEventListener('click', function(e) {
    e.stopPropagation();
    sidebar.classList.contains('open') ? fecharSidebar() : abrirSidebar();
  });
  document.addEventListener('click', function(e) {
    if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== hamburger) {
      fecharSidebar();
    }
  });
}

async function marcarTodasNotifs() {
  var fd = new FormData();
  fd.append('_csrf', CSRF);
  await Api.post('/api/notificacoes/todas-lidas', fd);
  Notifs.load();
}
</script>
