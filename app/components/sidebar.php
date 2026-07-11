<?php
$user = getUser();
$currentPage = $currentPage ?? 'dashboard';
$baixo = Database::count("SELECT COUNT(*) FROM estoque WHERE quantidade <= quantidade_minima AND status='ativo'");
$pendentes = Database::count("SELECT COUNT(*) FROM demandas WHERE status='pendente'");
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <img src="<?= APP_URL ?>/public/assets/img/logo-full.png" alt="Fokos Eventos" class="brand-full">
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Principal</div>

    <a class="nav-item <?= $currentPage==='dashboard'  ? 'active' : '' ?>" href="<?= APP_URL ?>/dashboard">
      <i class="fa-solid fa-gauge-high"></i> Dashboard
    </a>
    <a class="nav-item <?= $currentPage==='demandas'   ? 'active' : '' ?>" href="<?= APP_URL ?>/demandas">
      <i class="fa-solid fa-clipboard-list"></i> Demandas
      <?php if($pendentes > 0): ?><span class="nav-badge yellow"><?= $pendentes ?></span><?php endif; ?>
    </a>
    <a class="nav-item <?= $currentPage==='calendario' ? 'active' : '' ?>" href="<?= APP_URL ?>/calendario">
      <i class="fa-solid fa-calendar-days"></i> Calendário
    </a>

    <div class="nav-section-label">Operação</div>

    <a class="nav-item <?= $currentPage==='estoque'    ? 'active' : '' ?>" href="<?= APP_URL ?>/estoque">
      <i class="fa-solid fa-boxes-stacked"></i> Estoque
      <?php if($baixo > 0): ?><span class="nav-badge"><?= $baixo ?></span><?php endif; ?>
    </a>
    <a class="nav-item <?= $currentPage==='motoristas' ? 'active' : '' ?>" href="<?= APP_URL ?>/motoristas">
      <i class="fa-solid fa-truck-fast"></i> Motoristas
    </a>
    <a class="nav-item <?= $currentPage==='clientes'   ? 'active' : '' ?>" href="<?= APP_URL ?>/clientes">
      <i class="fa-solid fa-building-user"></i> Clientes
    </a>

    <div class="nav-section-label">Financeiro</div>

    <a class="nav-item <?= $currentPage==='financeiro' ? 'active' : '' ?>" href="<?= APP_URL ?>/financeiro">
      <i class="fa-solid fa-chart-line"></i> Financeiro
    </a>
    <a class="nav-item <?= $currentPage==='relatorios' ? 'active' : '' ?>" href="<?= APP_URL ?>/relatorios">
      <i class="fa-solid fa-chart-pie"></i> Relatórios
    </a>

    <div class="nav-section-label">Sistema</div>

    <a class="nav-item <?= $currentPage==='usuarios'   ? 'active' : '' ?>" href="<?= APP_URL ?>/usuarios">
      <i class="fa-solid fa-users"></i> Usuários
    </a>
    <a class="nav-item <?= $currentPage==='logs'       ? 'active' : '' ?>" href="<?= APP_URL ?>/logs">
      <i class="fa-solid fa-scroll"></i> Logs
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-chip">
      <div class="user-avatar">
        <?= strtoupper(substr($user['nome'], 0, 2)) ?>
      </div>
      <div class="user-info">
        <strong><?= htmlspecialchars($user['nome']) ?></strong>
        <small><?= $user['tipo'] === 'admin' ? 'Administrador' : 'Motorista' ?></small>
      </div>
    </div>
    <a href="<?= APP_URL ?>/logout" class="btn-logout" title="Sair">
      <i class="fa-solid fa-right-from-bracket"></i>
    </a>
  </div>
</aside>
