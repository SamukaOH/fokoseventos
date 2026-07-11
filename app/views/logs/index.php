<?php
$pagina = max(1, (int)($_GET['pag'] ?? 1));
$porPagina = 50;
$offset = ($pagina - 1) * $porPagina;
$total = Database::count("SELECT COUNT(*) FROM logs_atividade");
$totalPags = ceil($total / $porPagina);

$logs = Database::fetchAll(
    "SELECT l.*, u.nome as usuario_nome, u.tipo as usuario_tipo
     FROM logs_atividade l
     LEFT JOIN usuarios u ON u.id = l.usuario_id
     ORDER BY l.criado_em DESC
     LIMIT $porPagina OFFSET $offset"
);

$modulos = Database::fetchAll("SELECT DISTINCT modulo FROM logs_atividade WHERE modulo != '' ORDER BY modulo");
?>

<div style="display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:24px">
  <div style="display:flex;gap:8px;align-items:center">
    <span style="font-size:13px;color:var(--text3)"><?= number_format($total) ?> registros</span>
    <button class="btn btn-ghost btn-sm" onclick="location.reload()"><i class="fa-solid fa-rotate-right"></i> Atualizar</button>
  </div>
</div>

<!-- Filtros -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
  <div class="search-wrap" style="flex:1;min-width:200px;max-width:320px">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" class="search-input" placeholder="Buscar ação..." id="busca-log" oninput="filtrarLogs()">
  </div>
  <select class="form-control" style="width:auto" id="fil-modulo" onchange="filtrarLogs()">
    <option value="">Todos os módulos</option>
    <?php foreach($modulos as $m): ?>
    <option value="<?= htmlspecialchars($m['modulo']) ?>"><?= ucfirst(htmlspecialchars($m['modulo'])) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<!-- Tabela -->
<div class="table-wrap">
  <table class="table" id="tabela-logs">
    <thead>
      <tr><th>Data/Hora</th><th>Usuário</th><th>Ação</th><th>Módulo</th><th>IP</th></tr>
    </thead>
    <tbody>
      <?php foreach($logs as $log): ?>
      <tr class="log-row">
        <td style="white-space:nowrap;font-size:12px;color:var(--text3)"><?= date('d/m/Y H:i:s', strtotime($log['criado_em'])) ?></td>
        <td>
          <?php if($log['usuario_nome']): ?>
          <div style="font-size:13px;font-weight:500"><?= htmlspecialchars($log['usuario_nome']) ?></div>
          <div style="font-size:11px;color:var(--text3)"><?= $log['usuario_tipo'] === 'admin' ? 'Admin' : 'Motorista' ?></div>
          <?php else: ?>
          <span style="color:var(--text3)">Sistema</span>
          <?php endif; ?>
        </td>
        <td style="font-size:13px"><?= htmlspecialchars($log['acao']) ?></td>
        <td>
          <?php if($log['modulo']): ?>
          <span class="badge badge-ghost" style="font-size:11px"><?= htmlspecialchars($log['modulo']) ?></span>
          <?php else: ?>–<?php endif; ?>
        </td>
        <td style="font-size:12px;color:var(--text3);font-family:monospace"><?= htmlspecialchars($log['ip'] ?: '–') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($logs)): ?>
      <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text3)">Nenhum log registrado</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Paginação -->
<?php if($totalPags > 1): ?>
<div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:20px;flex-wrap:wrap">
  <?php if($pagina > 1): ?>
  <a href="?pag=<?= $pagina-1 ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-chevron-left"></i> Anterior</a>
  <?php endif; ?>
  <span style="font-size:13px;color:var(--text3)">Página <?= $pagina ?> de <?= $totalPags ?></span>
  <?php if($pagina < $totalPags): ?>
  <a href="?pag=<?= $pagina+1 ?>" class="btn btn-ghost btn-sm">Próxima <i class="fa-solid fa-chevron-right"></i></a>
  <?php endif; ?>
</div>
<?php endif; ?>

<script>
function filtrarLogs() {
  var busca   = document.getElementById('busca-log').value.toLowerCase();
  var modulo  = document.getElementById('fil-modulo').value.toLowerCase();
  document.querySelectorAll('.log-row').forEach(function(row) {
    var texto = row.textContent.toLowerCase();
    var ok = (!busca || texto.includes(busca)) && (!modulo || texto.includes(modulo));
    row.style.display = ok ? '' : 'none';
  });
}
</script>
