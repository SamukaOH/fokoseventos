<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="app-url" content="<?= APP_URL ?>">
<meta name="csrf"    content="<?= csrfToken() ?>">
<meta name="theme-color" content="#0D0E12">
<title><?= APP_NAME ?> — Sistema ERP</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/public/assets/css/app.css?v=<?= APP_VERSION ?>">
</head>
<body>

<div class="app-shell">
  <div class="sidebar-overlay" id="sidebar-overlay" onclick="fecharSidebar()"></div>
  <?php include APP_PATH . '/components/sidebar.php'; ?>
  <div class="app-main">
    <?php include APP_PATH . '/components/topbar.php'; ?>
    <main class="main-content" id="main-content">
      <?php include $pageContent; ?>
    </main>
  </div>
</div>

<nav class="bottom-nav" id="bottom-nav">
  <a href="<?= APP_URL ?>/dashboard" class="bnav-item <?= ($currentPage??'')==='dashboard' ? 'active' : '' ?>">
    <i class="fa-solid fa-gauge-high"></i><span>Início</span>
  </a>
  <a href="<?= APP_URL ?>/demandas" class="bnav-item <?= ($currentPage??'')==='demandas' ? 'active' : '' ?>">
    <i class="fa-solid fa-clipboard-list"></i><span>Demandas</span>
    <?php $pend=Database::count("SELECT COUNT(*) FROM demandas WHERE status='pendente'"); if($pend>0): ?><span class="bnav-badge"><?= $pend ?></span><?php endif; ?>
  </a>
  <a href="<?= APP_URL ?>/calendario" class="bnav-item <?= ($currentPage??'')==='calendario' ? 'active' : '' ?>">
    <i class="fa-solid fa-calendar-days"></i><span>Agenda</span>
  </a>
  <a href="<?= APP_URL ?>/estoque" class="bnav-item <?= ($currentPage??'')==='estoque' ? 'active' : '' ?>">
    <i class="fa-solid fa-boxes-stacked"></i><span>Estoque</span>
  </a>
  <a href="<?= APP_URL ?>/motoristas" class="bnav-item <?= ($currentPage??'')=='motoristas' ? 'active' : '' ?>">
    <i class="fa-solid fa-truck-fast"></i><span>Equipe</span>
  </a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
<script src="<?= APP_URL ?>/public/assets/js/app.js?v=<?= APP_VERSION ?>"></script>
<script>if(typeof flatpickr!=='undefined') flatpickr.localize(flatpickr.l10ns.pt);</script>

</body>
</html>