<?php
// ============================================================
// FOKOS EVENTOS — Estoque Controller
// ============================================================

class EstoqueController {

    public function index(): void {
        requireAdmin();
        $currentPage = 'estoque';
        $pageContent = APP_PATH . '/views/estoque/index.php';
        include APP_PATH . '/views/layout.php';
    }

    public function lista(): void {
        requireAdmin();
        $where  = "1=1";
        $params = [];

        if (!empty($_GET['busca'])) {
            $where .= " AND (e.nome LIKE ? OR e.sku LIKE ? OR e.fornecedor LIKE ?)";
            $b = '%' . $_GET['busca'] . '%';
            $params = array_merge($params, [$b,$b,$b]);
        }
        if (!empty($_GET['categoria'])) {
            $where .= " AND e.categoria_id = ?";
            $params[] = (int)$_GET['categoria'];
        }
        if (!empty($_GET['alerta'])) {
            $where .= " AND e.quantidade <= e.quantidade_minima";
        }

        $produtos = Database::fetchAll(
            "SELECT e.*, c.nome as categoria_nome, c.cor as categoria_cor FROM estoque e
             LEFT JOIN categorias_estoque c ON e.categoria_id = c.id
             WHERE $where AND e.status='ativo' ORDER BY e.nome",
            $params
        );
        $categorias = Database::fetchAll("SELECT * FROM categorias_estoque ORDER BY nome");
        jsonResponse(['produtos' => $produtos, 'categorias' => $categorias]);
    }

    public function criar(): void {
        requireAdmin();
        $csrf = $_POST['_csrf'] ?? '';
        if (!verifyCsrf($csrf)) jsonResponse(['erro' => 'Token inválido.'], 403);

        $campos = ['nome','sku','fornecedor','localizacao','observacoes'];
        $data = [];
        foreach ($campos as $c) $data[$c] = sanitize($_POST[$c] ?? '');
        $data['categoria_id']       = (int)($_POST['categoria_id'] ?? 0) ?: null;
        $data['quantidade']         = max(0, (int)($_POST['quantidade'] ?? 0));
        $data['quantidade_minima']  = max(0, (int)($_POST['quantidade_minima'] ?? 1));
        $data['custo']              = (float)str_replace(',', '.', $_POST['custo'] ?? '0');

        if (empty($data['nome'])) jsonResponse(['erro' => 'Nome obrigatório.'], 422);

        $id = Database::insert(
            "INSERT INTO estoque (nome,sku,categoria_id,quantidade,quantidade_minima,custo,fornecedor,localizacao,observacoes)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [$data['nome'],$data['sku'],$data['categoria_id'],$data['quantidade'],$data['quantidade_minima'],$data['custo'],$data['fornecedor'],$data['localizacao'],$data['observacoes']]
        );

        if ($data['quantidade'] > 0) {
            Database::query(
                "INSERT INTO movimentacoes_estoque (produto_id,tipo,quantidade,motivo,usuario_id) VALUES (?,?,?,?,?)",
                [$id,'entrada',$data['quantidade'],'Cadastro inicial',getUser()['id']]
            );
        }

        logActivity("Produto criado: {$data['nome']}", 'estoque', $id);
        jsonResponse(['sucesso' => true, 'id' => $id, 'mensagem' => 'Produto cadastrado!']);
    }

    public function movimentar(): void {
        requireAdmin();
        $csrf = $_POST['_csrf'] ?? '';
        if (!verifyCsrf($csrf)) jsonResponse(['erro' => 'Token inválido.'], 403);

        $prodId = (int)($_POST['produto_id'] ?? 0);
        $tipo   = sanitize($_POST['tipo'] ?? '');
        $qtd    = max(1, (int)($_POST['quantidade'] ?? 0));
        $motivo = sanitize($_POST['motivo'] ?? '');

        if (!in_array($tipo, ['entrada','saida'])) jsonResponse(['erro' => 'Tipo inválido.'], 422);

        $prod = Database::fetchOne("SELECT * FROM estoque WHERE id=?", [$prodId]);
        if (!$prod) jsonResponse(['erro' => 'Produto não encontrado.'], 404);

        if ($tipo === 'saida' && $prod['quantidade'] < $qtd) {
            jsonResponse(['erro' => "Estoque insuficiente. Disponível: {$prod['quantidade']}"], 422);
        }

        $novaQtd = $tipo === 'entrada' ? $prod['quantidade'] + $qtd : $prod['quantidade'] - $qtd;
        Database::query("UPDATE estoque SET quantidade=?, atualizado_em=NOW() WHERE id=?", [$novaQtd, $prodId]);
        Database::query(
            "INSERT INTO movimentacoes_estoque (produto_id,tipo,quantidade,motivo,usuario_id) VALUES (?,?,?,?,?)",
            [$prodId, $tipo, $qtd, $motivo, getUser()['id']]
        );

        logActivity("Movimentação estoque #{$prodId} {$tipo} {$qtd}", 'estoque', $prodId);
        jsonResponse(['sucesso' => true, 'nova_quantidade' => $novaQtd, 'mensagem' => 'Movimentação registrada!']);
    }

    public function historico(string $id): void {
        requireAdmin();
        $historico = Database::fetchAll(
            "SELECT m.*, u.nome as usuario_nome FROM movimentacoes_estoque m
             LEFT JOIN usuarios u ON m.usuario_id = u.id
             WHERE m.produto_id = ? ORDER BY m.criado_em DESC LIMIT 30",
            [(int)$id]
        );
        jsonResponse(['historico' => $historico]);
    }

    public function deletar(string $id): void {
        requireAdmin();
        Database::query("UPDATE estoque SET status='inativo' WHERE id=?", [(int)$id]);
        jsonResponse(['sucesso' => true, 'mensagem' => 'Produto removido.']);
    }
}
