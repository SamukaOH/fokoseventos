<?php
// Buscar tipos e tamanhos
$tipos    = Database::fetchAll("SELECT * FROM letreiros_tipos WHERE ativo=1 ORDER BY id");
$tamanhos = Database::fetchAll("SELECT * FROM letreiros_tamanhos WHERE ativo=1 ORDER BY altura_cm");

// Filtros
$tipoFil    = (int)($_GET['tipo'] ?? 0);
$tamanhoFil = (int)($_GET['tamanho'] ?? 0);

// Buscar estoque com filtros
$where = "WHERE 1=1";
$params = [];
if ($tipoFil)    { $where .= " AND e.tipo_id=?";    $params[] = $tipoFil; }
if ($tamanhoFil) { $where .= " AND e.tamanho_id=?"; $params[] = $tamanhoFil; }

$estoque = Database::fetchAll(
    "SELECT e.*, t.nome as tipo_nome, t.cor as tipo_cor, s.nome as tamanho_nome
     FROM letreiros_estoque e
     JOIN letreiros_tipos t ON t.id = e.tipo_id
     JOIN letreiros_tamanhos s ON s.id = e.tamanho_id
     $where
     ORDER BY e.tamanho_id, e.tipo_id, e.caractere",
    $params
);

// Agrupar por tamanho > tipo > caractere
$grid = [];
foreach ($estoque as $item) {
    $grid[$item['tamanho_nome']][$item['tipo_nome']][$item['caractere']] = $item;
}

// Stats gerais
$totalItens    = Database::count("SELECT SUM(quantidade_total) FROM letreiros_estoque");
$totalDisp     = Database::count("SELECT SUM(quantidade_disponivel) FROM letreiros_estoque");
$totalRua      = Database::count("SELECT SUM(quantidade_rua) FROM letreiros_estoque");
$totalReserv   = Database::count("SELECT SUM(quantidade_reservada) FROM letreiros_estoque");

// Chars padrão
$letras   = array_merge(range('A','Z'), ['?','!','%','&','♥','~','.',',','-','(',')','/','@','#','0','1','2','3','4','5','6','7','8','9']);
?>

<div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-bottom:20px"><div style="display:flex;gap:6px;flex-shrink:0">
    <button class="btn btn-ghost btn-sm" onclick="Modal.open('modal-ajuste-massa')" title="Ajuste em massa">
      <i class="fa-solid fa-sliders"></i><span class="hide-mobile"> Ajuste</span>
    </button>
    <button class="btn btn-primary btn-sm" onclick="Modal.open('modal-add-item')">
      <i class="fa-solid fa-plus"></i><span class="hide-mobile"> Adicionar</span>
    </button>
  </div>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:20px">
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(255,214,0,.12);color:var(--yellow)"><i class="fa-solid fa-font"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= number_format($totalItens) ?></div><div class="stat-card-label">Total de peças</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(76,217,100,.12);color:#4cd964"><i class="fa-solid fa-circle-check"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= number_format($totalDisp) ?></div><div class="stat-card-label">Disponíveis</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(100,210,255,.12);color:#64d2ff"><i class="fa-solid fa-truck-fast"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= number_format($totalRua) ?></div><div class="stat-card-label">Na rua</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon" style="background:rgba(245,158,11,.12);color:#f59e0b"><i class="fa-solid fa-clock"></i></div>
    <div class="stat-card-info"><div class="stat-card-value"><?= number_format($totalReserv) ?></div><div class="stat-card-label">Reservadas</div></div>
  </div>
</div>

<!-- Filtros responsivos -->
<div style="display:flex;gap:8px;margin-bottom:16px">
  <select class="estoque-select" onchange="location.href=this.value">
    <option value="?<?= $tipoFil ? '&tipo='.$tipoFil : '' ?>">Todos os tamanhos</option>
    <?php foreach($tamanhos as $t): ?>
    <option value="?tamanho=<?= $t['id'] ?><?= $tipoFil ? '&tipo='.$tipoFil : '' ?>" <?= $tamanhoFil==$t['id']?'selected':'' ?>><?= $t['nome'] ?></option>
    <?php endforeach; ?>
  </select>
  <select class="estoque-select" onchange="location.href=this.value">
    <option value="?<?= $tamanhoFil ? '&tamanho='.$tamanhoFil : '' ?>">Todos os tipos</option>
    <?php foreach($tipos as $t): ?>
    <option value="?tipo=<?= $t['id'] ?><?= $tamanhoFil ? '&tamanho='.$tamanhoFil : '' ?>" <?= $tipoFil==$t['id']?'selected':'' ?>><?= $t['nome'] ?></option>
    <?php endforeach; ?>
  </select>
</div>

<?php if(empty($estoque)): ?>
<div style="text-align:center;padding:60px 20px;color:var(--text3)">
  <i class="fa-solid fa-font" style="font-size:40px;opacity:.2;display:block;margin-bottom:16px"></i>
  <h3 style="margin-bottom:8px">Nenhum item cadastrado</h3>
  <p style="font-size:13px;margin-bottom:20px">Use "Ajuste em massa" para inicializar o estoque de todas as letras de uma vez.</p>
  <button class="btn btn-primary" onclick="Modal.open('modal-ajuste-massa')">
    <i class="fa-solid fa-sliders"></i> Inicializar estoque
  </button>
</div>
<?php else: ?>

<?php foreach($grid as $tamanhoNome => $tipos_grid): ?>
<div style="margin-bottom:32px">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border)">
    <span style="background:var(--yellow);color:#000;padding:4px 14px;border-radius:8px;font-family:'Bebas Neue',sans-serif;font-size:18px;letter-spacing:.04em"><?= $tamanhoNome ?></span>
    <span style="font-family:'Sora',sans-serif;font-size:15px;font-weight:700;color:var(--text)">Letreiros</span>
  </div>

  <?php foreach($tipos_grid as $tipoNome => $chars): ?>
  <div style="background:var(--bg2);border:1px solid var(--border);border-radius:16px;margin-bottom:16px;overflow:hidden">
    <div style="padding:12px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.02)">
      <?php
        $tipoObj = array_values(array_filter($tipos, fn($t) => $t['nome']===$tipoNome))[0] ?? null;
        $cor = $tipoObj['cor'] ?? '#888';
      ?>
      <span style="width:10px;height:10px;border-radius:50%;background:<?= $cor ?>;display:inline-block;flex-shrink:0"></span>
      <strong style="font-size:14px"><?= htmlspecialchars($tipoNome) ?></strong>
      <span style="font-size:12px;color:var(--text3)"><?= count($chars) ?> caracteres cadastrados</span>
    </div>
    <div class="letras-grid">
      <?php foreach($chars as $char => $item):
        $disp  = $item['quantidade_disponivel'];
        $rua   = $item['quantidade_rua'];
        $res   = $item['quantidade_reservada'];
        $cor_borda = $disp === 0 ? '#ff3b30' : ($disp <= 2 ? '#f59e0b' : '#4cd964');
      ?>
      <div class="letra-card" onclick="abrirItemEstoque(<?= $item['id'] ?>)"
           style="border-color:<?= $cor_borda ?>33;background:<?= $cor_borda ?>08">
        <div class="letra-char"><?= htmlspecialchars($char) ?></div>
        <div class="letra-disp" style="color:<?= $cor_borda ?>"><?= $disp ?></div>
        <div class="letra-label">disp.</div>
        <?php if($rua > 0 || $res > 0): ?>
        <div class="letra-extra">
          <?php if($rua > 0): ?><span style="color:#64d2ff"><?= $rua ?>🚚</span><?php endif; ?>
          <?php if($res > 0): ?><span style="color:#f59e0b"><?= $res ?>⏳</span><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Modal Adicionar Item -->
<div class="modal-overlay" id="modal-add-item">
  <div class="modal" style="max-width:440px">
    <div class="modal-header">
      <h3>Adicionar Item ao Estoque</h3>
      <button class="modal-close" data-close-modal="modal-add-item"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Caractere *</label>
          <input type="text" class="form-control" id="add-char" maxlength="2" placeholder="A" style="font-size:24px;text-align:center;font-family:'Bebas Neue',sans-serif">
        </div>
        <div class="form-group">
          <label class="form-label">Quantidade</label>
          <input type="number" class="form-control" id="add-qtd" value="1" min="0">
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Tipo *</label>
          <select class="form-control" id="add-tipo">
            <?php foreach($tipos as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Tamanho *</label>
          <select class="form-control" id="add-tamanho">
            <?php foreach($tamanhos as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-add-item">Cancelar</button>
      <button class="btn btn-primary" onclick="adicionarItem()"><i class="fa-solid fa-plus"></i> Adicionar</button>
    </div>
  </div>
</div>

<!-- Modal Ajuste em massa -->
<div class="modal-overlay" id="modal-ajuste-massa">
  <div class="modal" style="max-width:500px">
    <div class="modal-header">
      <h3>Inicializar / Ajustar Estoque em Massa</h3>
      <button class="modal-close" data-close-modal="modal-ajuste-massa"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--text3);margin-bottom:16px">
        Cria todos os caracteres (A-Z e símbolos) para o tipo e tamanho selecionados com a quantidade informada.
        Itens já existentes serão ignorados.
      </p>
      <div class="form-row">
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Tipo *</label>
          <select class="form-control" id="massa-tipo">
            <?php foreach($tipos as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Tamanho *</label>
          <select class="form-control" id="massa-tamanho">
            <?php foreach($tamanhos as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1">
          <label class="form-label">Quantidade inicial por caractere</label>
          <input type="number" class="form-control" id="massa-qtd" value="0" min="0">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-ajuste-massa">Cancelar</button>
      <button class="btn btn-primary" id="btn-massa" onclick="ajusteMassa()">
        <i class="fa-solid fa-sliders"></i> Inicializar
      </button>
    </div>
  </div>
</div>

<!-- Modal Item Individual -->
<div class="modal-overlay" id="modal-item">
  <div class="modal" style="max-width:420px">
    <div class="modal-header">
      <h3 id="modal-item-title">Editar Item</h3>
      <button class="modal-close" data-close-modal="modal-item"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="item-id">
      <div style="text-align:center;margin-bottom:20px">
        <div id="item-char-big" style="font-family:'Bebas Neue',sans-serif;font-size:64px;color:var(--yellow);line-height:1"></div>
        <div id="item-info" style="font-size:12px;color:var(--text3);margin-top:4px"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:20px">
        <div style="background:var(--bg3);border-radius:10px;padding:12px;text-align:center">
          <div id="item-disp" style="font-size:22px;font-weight:700;color:#4cd964">0</div>
          <div style="font-size:10px;color:var(--text3)">Disponível</div>
        </div>
        <div style="background:var(--bg3);border-radius:10px;padding:12px;text-align:center">
          <div id="item-rua" style="font-size:22px;font-weight:700;color:#64d2ff">0</div>
          <div style="font-size:10px;color:var(--text3)">Na rua</div>
        </div>
        <div style="background:var(--bg3);border-radius:10px;padding:12px;text-align:center">
          <div id="item-res" style="font-size:22px;font-weight:700;color:#f59e0b">0</div>
          <div style="font-size:10px;color:var(--text3)">Reservado</div>
        </div>
      </div>

      <!-- Tabs de ação -->
      <div style="display:flex;gap:6px;margin-bottom:16px">
        <button class="btn btn-primary btn-sm" onclick="abaItem('ajuste',this)" id="tab-ajuste" style="flex:1">Ajustar qtd</button>
        <button class="btn btn-ghost btn-sm" onclick="abaItem('venda',this)" id="tab-venda" style="flex:1"><i class="fa-solid fa-tag"></i> Vender</button>
        <button class="btn btn-ghost btn-sm" onclick="abaItem('excluir',this)" id="tab-excluir" style="flex:1;color:var(--danger)"><i class="fa-solid fa-trash"></i></button>
      </div>

      <!-- Aba: Ajuste -->
      <div id="aba-ajuste">
        <div class="form-group">
          <label class="form-label">Nova quantidade total</label>
          <input type="number" class="form-control" id="item-nova-qtd" min="0" placeholder="Ex: 5">
        </div>
      </div>

      <!-- Aba: Venda -->
      <div id="aba-venda" style="display:none">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Quantidade a vender *</label>
            <input type="number" class="form-control" id="venda-qtd" min="1" value="1">
          </div>
          <div class="form-group">
            <label class="form-label">Preço unitário (R$)</label>
            <input type="text" class="form-control" id="venda-preco" placeholder="0,00">
          </div>
          <div class="form-group" style="grid-column:1/-1">
            <label class="form-label">Observações</label>
            <input type="text" class="form-control" id="venda-obs" placeholder="Ex: Venda para João Silva">
          </div>
        </div>
        <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:10px;padding:12px;font-size:12px;color:var(--text3)">
          <i class="fa-solid fa-circle-info" style="color:#22c55e"></i>
          A venda desconta permanentemente do estoque e registra uma receita no financeiro.
        </div>
      </div>

      <!-- Aba: Excluir -->
      <div id="aba-excluir" style="display:none">
        <div style="background:rgba(255,59,48,.08);border:1px solid rgba(255,59,48,.2);border-radius:10px;padding:16px;text-align:center">
          <i class="fa-solid fa-triangle-exclamation" style="color:var(--danger);font-size:24px;display:block;margin-bottom:10px"></i>
          <div style="font-size:13px;font-weight:600;margin-bottom:6px">Excluir este item?</div>
          <div style="font-size:12px;color:var(--text3)">Esta ação é irreversível. Só é possível excluir itens sem peças na rua ou reservadas.</div>
        </div>
      </div>

    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" data-close-modal="modal-item">Cancelar</button>
      <button class="btn btn-primary" id="btn-item-action" onclick="executarAcaoItem()">
        <i class="fa-solid fa-check"></i> Salvar
      </button>
    </div>
  </div>
</div>


<script>
var CHARS_PADRAO = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ?!%&♥~.,-()/@ #0123456789'.split('');

async function adicionarItem() {
  var char    = document.getElementById('add-char').value.trim().toUpperCase();
  var qtd     = parseInt(document.getElementById('add-qtd').value) || 0;
  var tipo    = document.getElementById('add-tipo').value;
  var tamanho = document.getElementById('add-tamanho').value;
  if (!char) return Toast.warning('Atenção', 'Informe o caractere.');
  var fd = new FormData();
  fd.append('_csrf', CSRF); fd.append('caractere', char);
  fd.append('quantidade', qtd); fd.append('tipo_id', tipo); fd.append('tamanho_id', tamanho);
  try {
    await Api.post('/api/estoque/letreiros/add', fd);
    Toast.success('Adicionado!', char + ' adicionado ao estoque.');
    Modal.close('modal-add-item');
    setTimeout(function(){ location.reload(); }, 800);
  } catch(e) { Toast.error('Erro', e.message); }
}

async function ajusteMassa() {
  var tipo    = document.getElementById('massa-tipo').value;
  var tamanho = document.getElementById('massa-tamanho').value;
  var qtd     = parseInt(document.getElementById('massa-qtd').value) || 0;
  var btn = document.getElementById('btn-massa');
  setLoadingBtn(btn, true, 'Criando...');
  var fd = new FormData();
  fd.append('_csrf', CSRF); fd.append('tipo_id', tipo);
  fd.append('tamanho_id', tamanho); fd.append('quantidade', qtd);
  try {
    var res = await Api.post('/api/estoque/letreiros/massa', fd);
    Toast.success('Pronto!', res.criados + ' itens criados.');
    Modal.close('modal-ajuste-massa');
    setTimeout(function(){ location.reload(); }, 800);
  } catch(e) { Toast.error('Erro', e.message); }
  finally { setLoadingBtn(btn, false, '<i class="fa-solid fa-sliders"></i> Inicializar'); }
}

function abrirItemEstoque(id) {
  Api.get('/api/estoque/letreiros/' + id).then(function(data) {
    if (!data) return;
    var item = data.item;
    document.getElementById('item-id').value       = item.id;
    document.getElementById('item-char-big').textContent = item.caractere;
    document.getElementById('modal-item-title').textContent = '"' + item.caractere + '" — ' + item.tipo_nome + ' ' + item.tamanho_nome;
    document.getElementById('item-info').textContent = item.tipo_nome + ' · ' + item.tamanho_nome;
    document.getElementById('item-disp').textContent = item.quantidade_disponivel;
    document.getElementById('item-rua').textContent  = item.quantidade_rua;
    document.getElementById('item-res').textContent  = item.quantidade_reservada;
    document.getElementById('item-nova-qtd').value   = item.quantidade_total;
    Modal.open('modal-item');
  });
}

var _abaAtual = 'ajuste';
function abaItem(aba, btn) {
  _abaAtual = aba;
  ['ajuste','venda','excluir'].forEach(function(a) {
    document.getElementById('aba-'+a).style.display = a===aba?'':'none';
    document.getElementById('tab-'+a).className = 'btn '+(a===aba?'btn-primary':'btn-ghost')+' btn-sm'+(a==='excluir'?' '+'' :'');
  });
  var btnAcao = document.getElementById('btn-item-action');
  if(aba==='venda')   { btnAcao.innerHTML='<i class="fa-solid fa-tag"></i> Confirmar Venda'; btnAcao.className='btn btn-primary'; }
  else if(aba==='excluir'){ btnAcao.innerHTML='<i class="fa-solid fa-trash"></i> Excluir'; btnAcao.className='btn btn-danger'; }
  else                { btnAcao.innerHTML='<i class="fa-solid fa-check"></i> Salvar'; btnAcao.className='btn btn-primary'; }
}

async function executarAcaoItem() {
  var id = document.getElementById('item-id').value;
  if(_abaAtual==='ajuste') await salvarItemQtd();
  else if(_abaAtual==='venda') await venderItem(id);
  else if(_abaAtual==='excluir') await deletarItem(id);
}

async function salvarItemQtd() {
  var id  = document.getElementById('item-id').value;
  var qtd = parseInt(document.getElementById('item-nova-qtd').value);
  if(isNaN(qtd)||qtd<0) return Toast.warning('Atenção','Quantidade inválida.');
  var fd=new FormData(); fd.append('_csrf',CSRF); fd.append('quantidade_total',qtd);
  try { await Api.post('/api/estoque/letreiros/'+id+'/ajustar',fd); Toast.success('Atualizado!','Quantidade ajustada.'); Modal.close('modal-item'); setTimeout(function(){ location.reload(); },700); }
  catch(e){ Toast.error('Erro',e.message); }
}

async function venderItem(id) {
  var qtd   = parseInt(document.getElementById('venda-qtd').value)||1;
  var preco = document.getElementById('venda-preco').value.replace(',','.');
  var obs   = document.getElementById('venda-obs').value;
  var fd=new FormData(); fd.append('_csrf',CSRF); fd.append('quantidade',qtd); fd.append('preco',preco); fd.append('observacoes',obs);
  try { var res=await Api.post('/api/estoque/letreiros/'+id+'/vender',fd); Toast.success('Vendido!',res.mensagem); Modal.close('modal-item'); setTimeout(function(){ location.reload(); },700); }
  catch(e){ Toast.error('Erro',e.message); }
}

async function deletarItem(id) {
  var fd=new FormData(); fd.append('_csrf',CSRF);
  try { await Api.post('/api/estoque/letreiros/'+id+'/deletar',fd); Toast.success('Excluído','Item removido do estoque.'); Modal.close('modal-item'); setTimeout(function(){ location.reload(); },700); }
  catch(e){ Toast.error('Erro',e.message); }
}
</script>
