<?php
// ============================================================
// FOKOS EVENTOS — Financeiro Controller
// ============================================================

// ============================================================
// FOKOS EVENTOS — Motoristas Controller
// ============================================================

class MotoristasController {

    public function index(): void {
        requireAdmin();
        $currentPage = 'motoristas';
        $pageContent = APP_PATH . '/views/motoristas/index.php';
        include APP_PATH . '/views/layout.php';
    }

    public function lista(): void {
        requireAdmin();
        $motoristas = Database::fetchAll(
            "SELECT u.id, u.nome, u.email, u.telefone, u.foto, u.status, u.ultimo_login, u.criado_em,
                    m.cpf, m.cnh, m.veiculo, m.placa, m.disponivel,
                    (SELECT COUNT(*) FROM demandas WHERE motorista_id=u.id AND status='finalizado') as total_entregas,
                    (SELECT COUNT(*) FROM demandas WHERE motorista_id=u.id AND status NOT IN ('finalizado','cancelado')) as demandas_ativas
             FROM usuarios u
             LEFT JOIN motoristas m ON m.usuario_id = u.id
             WHERE u.tipo = 'motorista'
             ORDER BY u.nome"
        );
        jsonResponse(['motoristas' => $motoristas]);
    }

    public function criar(): void {
        requireAdmin();
        $csrf = $_POST['_csrf'] ?? '';
        if (!verifyCsrf($csrf)) jsonResponse(['erro' => 'Token inválido.'], 403);

        $nome     = sanitize($_POST['nome'] ?? '');
        $email    = sanitize($_POST['email'] ?? '');
        $telefone = sanitize($_POST['telefone'] ?? '');
        $senha    = $_POST['senha'] ?? '';
        $cpf      = sanitize($_POST['cpf'] ?? '');
        $cnh      = sanitize($_POST['cnh'] ?? '');
        $veiculo  = sanitize($_POST['veiculo'] ?? '');
        $placa    = sanitize($_POST['placa'] ?? '');

        if (empty($nome) || empty($email) || empty($senha)) jsonResponse(['erro' => 'Nome, e-mail e senha são obrigatórios.'], 422);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(['erro' => 'E-mail inválido.'], 422);

        $existe = Database::fetchOne("SELECT id FROM usuarios WHERE email=?", [$email]);
        if ($existe) jsonResponse(['erro' => 'E-mail já cadastrado.'], 409);

        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
        $userId = Database::insert(
            "INSERT INTO usuarios (nome,email,senha,tipo,telefone,status) VALUES (?,?,?,'motorista',?,'ativo')",
            [$nome,$email,$hash,$telefone]
        );

        Database::insert(
            "INSERT INTO motoristas (usuario_id,cpf,cnh,veiculo,placa) VALUES (?,?,?,?,?)",
            [$userId,$cpf,$cnh,$veiculo,$placa]
        );

        logActivity("Motorista criado: {$nome}", 'motoristas', $userId);
        jsonResponse(['sucesso' => true, 'id' => $userId, 'mensagem' => 'Motorista cadastrado!']);
    }

    public function setDisponivel(string $id): void {
        requireAdmin();
        $val = (int)($_POST['disponivel'] ?? 1);
        Database::query("UPDATE motoristas SET disponivel=? WHERE usuario_id=?", [$val, (int)$id]);
        jsonResponse(['sucesso' => true]);
    }

    public function demandas(string $id): void {
        requireAdmin();
        $demandas = Database::fetchAll(
            "SELECT d.*, c.nome as cliente_nome, 'entrega' as tipo_motorista
             FROM demandas d
             LEFT JOIN clientes c ON c.id = d.cliente_id
             WHERE d.motorista_id = ? AND d.status NOT IN ('finalizado','cancelado')
             UNION
             SELECT d.*, c.nome as cliente_nome, 'retirada' as tipo_motorista
             FROM demandas d
             LEFT JOIN clientes c ON c.id = d.cliente_id
             WHERE d.motorista_retirada_id = ? AND d.status NOT IN ('finalizado','cancelado')
             ORDER BY data_evento ASC",
            [(int)$id, (int)$id]
        );
        jsonResponse(['demandas' => $demandas]);
    }

    public function deletar(string $id): void {
        requireAdmin();
        Database::query("UPDATE usuarios SET status='inativo' WHERE id=? AND tipo='motorista'", [(int)$id]);
        jsonResponse(['sucesso' => true, 'mensagem' => 'Motorista desativado.']);
    }
}

// ============================================================
// FOKOS EVENTOS — Notificações Controller
// ============================================================

class NotificacoesController {

    public function lista(): void {
        requireLogin();
        $user = getUser();
        // Apagar notificações com mais de 24h automaticamente
        Database::query(
            "DELETE FROM notificacoes WHERE usuario_id=? AND criado_em < DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            [$user['id']]
        );
        $notifs = Database::fetchAll(
            "SELECT * FROM notificacoes WHERE usuario_id=? ORDER BY criado_em DESC LIMIT 30",
            [$user['id']]
        );
        $naoLidas = Database::count(
            "SELECT COUNT(*) FROM notificacoes WHERE usuario_id=? AND lida=0",
            [$user['id']]
        );
        jsonResponse(['notificacoes' => $notifs, 'nao_lidas' => $naoLidas]);
    }

    public function marcarLida(): void {
        requireLogin();
        $id = (int)($_POST['id'] ?? 0);
        Database::query("UPDATE notificacoes SET lida=1 WHERE id=? AND usuario_id=?", [$id, getUser()['id']]);
        jsonResponse(['sucesso' => true]);
    }

    public function marcarTodasLidas(): void {
        requireLogin();
        Database::query("UPDATE notificacoes SET lida=1 WHERE usuario_id=?", [getUser()['id']]);
        jsonResponse(['sucesso' => true]);
    }
}

// ============================================================
// FOKOS EVENTOS — Clientes Controller
// ============================================================
class ClientesController {
    public function index(): void {
        requireAdmin();
        $currentPage = 'clientes';
        $pageContent = APP_PATH . '/views/clientes/index.php';
        include APP_PATH . '/views/layout.php';
    }
    public function get(string $id): void {
        requireAdmin();
        $c = Database::fetchOne("SELECT * FROM clientes WHERE id=?", [(int)$id]);
        if (!$c) jsonResponse(['erro'=>'Não encontrado.'], 404);
        jsonResponse(['cliente' => $c]);
    }
    public function salvar(): void {
        requireAdmin();
        $csrf = $_POST['_csrf'] ?? '';
        if (!verifyCsrf($csrf)) jsonResponse(['erro'=>'Token inválido.'], 403);
        $id   = (int)($_POST['id'] ?? 0);
        $nome = sanitize($_POST['nome'] ?? '');
        if (!$nome) jsonResponse(['erro'=>'Nome obrigatório.'], 422);
        $fields = ['nome'=>$nome,'email'=>sanitize($_POST['email']??''),
                   'telefone'=>sanitize($_POST['telefone']??''),'whatsapp'=>sanitize($_POST['whatsapp']??''),
                   'cpf_cnpj'=>sanitize($_POST['cpf_cnpj']??''),'endereco'=>sanitize($_POST['endereco']??''),
                   'observacoes'=>sanitize($_POST['observacoes']??'')];
        if ($id) {
            $sets = implode(',', array_map(fn($k) => "$k=?", array_keys($fields)));
            Database::query("UPDATE clientes SET $sets WHERE id=?", [...array_values($fields), $id]);
            jsonResponse(['sucesso'=>true,'mensagem'=>'Cliente atualizado.']);
        } else {
            $cols = implode(',', array_keys($fields));
            $vals = implode(',', array_fill(0, count($fields), '?'));
            $newId = Database::insert("INSERT INTO clientes ($cols) VALUES ($vals)", array_values($fields));
            jsonResponse(['sucesso'=>true,'id'=>$newId,'mensagem'=>'Cliente criado.']);
        }
    }
    public function deletar(string $id): void {
        requireAdmin();
        Database::query("DELETE FROM clientes WHERE id=?", [(int)$id]);
        jsonResponse(['sucesso'=>true,'mensagem'=>'Cliente excluído.']);
    }
}

// ============================================================
// FOKOS EVENTOS — Usuários Controller
// ============================================================
class UsuariosController {
    public function index(): void {
        requireAdmin();
        $currentPage = 'usuarios';
        $pageContent = APP_PATH . '/views/usuarios/index.php';
        include APP_PATH . '/views/layout.php';
    }
    public function criar(): void {
        requireAdmin();
        $csrf = $_POST['_csrf'] ?? '';
        if (!verifyCsrf($csrf)) jsonResponse(['erro'=>'Token inválido.'], 403);
        $nome  = sanitize($_POST['nome'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $tipo  = in_array($_POST['tipo']??'', ['admin','motorista']) ? $_POST['tipo'] : 'motorista';
        $tel   = sanitize($_POST['telefone'] ?? '');
        if (!$nome||!$email||strlen($senha)<6) jsonResponse(['erro'=>'Dados inválidos.'], 422);
        if (Database::fetchOne("SELECT id FROM usuarios WHERE email=?", [$email])) jsonResponse(['erro'=>'E-mail já cadastrado.'], 409);
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost'=>10]);
        $id = Database::insert("INSERT INTO usuarios (nome,email,senha,tipo,telefone,status) VALUES (?,?,?,?,?,'ativo')", [$nome,$email,$hash,$tipo,$tel]);
        if ($tipo === 'motorista') Database::insert("INSERT INTO motoristas (usuario_id) VALUES (?)", [$id]);
        logActivity("Usuário criado: $nome", 'usuarios', $id);
        jsonResponse(['sucesso'=>true,'id'=>$id,'mensagem'=>'Usuário criado.']);
    }
    public function alterarSenha(string $id): void {
        requireAdmin();
        $csrf  = $_POST['_csrf'] ?? '';
        if (!verifyCsrf($csrf)) jsonResponse(['erro'=>'Token inválido.'], 403);
        $senha = $_POST['senha'] ?? '';
        if (strlen($senha) < 6) jsonResponse(['erro'=>'Senha muito curta.'], 422);
        $hash = password_hash($senha, PASSWORD_BCRYPT, ['cost'=>10]);
        Database::query("UPDATE usuarios SET senha=? WHERE id=?", [$hash, (int)$id]);
        jsonResponse(['sucesso'=>true,'mensagem'=>'Senha alterada.']);
    }
    public function desativar(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? '')) jsonResponse(['erro'=>'Token inválido.'], 403);
        Database::query("UPDATE usuarios SET status='inativo' WHERE id=?", [(int)$id]);
        jsonResponse(['sucesso'=>true,'mensagem'=>'Usuário desativado.']);
    }

    // POST /api/usuarios/:id/ativar
    public function ativar(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? '')) jsonResponse(['erro'=>'Token inválido.'], 403);
        Database::query("UPDATE usuarios SET status='ativo' WHERE id=?", [(int)$id]);
        logActivity('Usuário #' . $id . ' reativado', 'usuarios', (int)$id);
        jsonResponse(['sucesso' => true]);
    }
}

// ============================================================
// LETREIROS — Estoque Controller
// ============================================================
class LetreirosController {

    // GET /api/estoque/letreiros/:id
    public function get(string $id): void {
        requireAdmin();
        $item = Database::fetchOne(
            "SELECT e.*, t.nome as tipo_nome, s.nome as tamanho_nome
             FROM letreiros_estoque e
             JOIN letreiros_tipos t ON t.id = e.tipo_id
             JOIN letreiros_tamanhos s ON s.id = e.tamanho_id
             WHERE e.id = ?", [(int)$id]
        );
        if (!$item) jsonResponse(['erro'=>'Item não encontrado.'], 404);
        jsonResponse(['item' => $item]);
    }

    // POST /api/estoque/letreiros/add
    public function add(): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);
        $char    = strtoupper(trim($_POST['caractere'] ?? ''));
        $qtd     = max(0, (int)($_POST['quantidade'] ?? 0));
        $tipo    = (int)($_POST['tipo_id'] ?? 0);
        $tamanho = (int)($_POST['tamanho_id'] ?? 0);
        if (!$char || !$tipo || !$tamanho) jsonResponse(['erro'=>'Dados incompletos.'],422);

        $existe = Database::fetchOne(
            "SELECT id FROM letreiros_estoque WHERE caractere=? AND tipo_id=? AND tamanho_id=?",
            [$char, $tipo, $tamanho]
        );
        if ($existe) {
            Database::query(
                "UPDATE letreiros_estoque SET quantidade_total=quantidade_total+?, quantidade_disponivel=quantidade_disponivel+? WHERE id=?",
                [$qtd, $qtd, $existe['id']]
            );
        } else {
            Database::insert(
                "INSERT INTO letreiros_estoque (caractere,tipo_id,tamanho_id,quantidade_total,quantidade_disponivel) VALUES (?,?,?,?,?)",
                [$char, $tipo, $tamanho, $qtd, $qtd]
            );
        }
        jsonResponse(['sucesso'=>true]);
    }

    // POST /api/estoque/letreiros/massa — cria A-Z + símbolos em massa
    public function massa(): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);
        $tipo    = (int)($_POST['tipo_id'] ?? 0);
        $tamanho = (int)($_POST['tamanho_id'] ?? 0);
        $qtd     = max(0, (int)($_POST['quantidade'] ?? 0));
        if (!$tipo || !$tamanho) jsonResponse(['erro'=>'Dados incompletos.'],422);

        $chars = array_merge(
            range('A','Z'),
            ['?','!','%','&','♥','~','.',',','-','(',')','/','@','#',
             '0','1','2','3','4','5','6','7','8','9']
        );
        $criados = 0;
        foreach ($chars as $char) {
            $existe = Database::fetchOne(
                "SELECT id FROM letreiros_estoque WHERE caractere=? AND tipo_id=? AND tamanho_id=?",
                [$char, $tipo, $tamanho]
            );
            if (!$existe) {
                Database::insert(
                    "INSERT INTO letreiros_estoque (caractere,tipo_id,tamanho_id,quantidade_total,quantidade_disponivel) VALUES (?,?,?,?,?)",
                    [$char, $tipo, $tamanho, $qtd, $qtd]
                );
                $criados++;
            }
        }
        jsonResponse(['sucesso'=>true, 'criados'=>$criados]);
    }

    // POST /api/estoque/letreiros/:id/ajustar
    public function ajustar(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);
        $novaTotal = max(0, (int)($_POST['quantidade_total'] ?? 0));
        $item = Database::fetchOne("SELECT * FROM letreiros_estoque WHERE id=?", [(int)$id]);
        if (!$item) jsonResponse(['erro'=>'Item não encontrado.'],404);
        // Disponível = total - reservado - rua
        $novaDisp = max(0, $novaTotal - $item['quantidade_reservada'] - $item['quantidade_rua']);
        Database::query(
            "UPDATE letreiros_estoque SET quantidade_total=?, quantidade_disponivel=? WHERE id=?",
            [$novaTotal, $novaDisp, (int)$id]
        );
        jsonResponse(['sucesso'=>true]);
    }

    // POST /api/estoque/letreiros/:id/vender — registrar venda
    public function vender(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);
        $qtd   = max(1,(int)($_POST['quantidade']??1));
        $preco = (float)str_replace(',','.',($_POST['preco']??0));
        $obs   = sanitize($_POST['observacoes']??'');
        $item  = Database::fetchOne("SELECT * FROM letreiros_estoque WHERE id=?",[(int)$id]);
        if(!$item) jsonResponse(['erro'=>'Item não encontrado.'],404);
        if($item['quantidade_disponivel'] < $qtd) jsonResponse(['erro'=>'Estoque insuficiente. Disponível: '.$item['quantidade_disponivel'].'.'],422);
        // Descontar do estoque
        Database::query(
            "UPDATE letreiros_estoque SET quantidade_total=quantidade_total-?, quantidade_disponivel=quantidade_disponivel-? WHERE id=?",
            [$qtd,$qtd,(int)$id]
        );
        // Registrar receita no financeiro
        $tipo = Database::fetchOne("SELECT nome FROM letreiros_tipos WHERE id=?",[$item['tipo_id']]);
        $tam  = Database::fetchOne("SELECT nome FROM letreiros_tamanhos WHERE id=?",[$item['tamanho_id']]);
        $descricao = "Venda letreiro '{$item['caractere']}' {$tipo['nome']} {$tam['nome']} (x{$qtd})";
        if($preco > 0) {
            Database::insert(
                "INSERT INTO financeiro (tipo,descricao,valor,categoria,status,pago_em,criado_por) VALUES ('receita',?,?,'Venda','pago',CURDATE(),?)",
                [$descricao,$preco*$qtd,$_SESSION['user_id']??null]
            );
        }
        logActivity("Venda: $descricao", 'estoque', (int)$id);
        jsonResponse(['sucesso'=>true,'mensagem'=>"$qtd peça(s) vendida(s)."]);
    }

    // POST /api/estoque/letreiros/:id/deletar
    public function deletar(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);
        $item = Database::fetchOne("SELECT * FROM letreiros_estoque WHERE id=?",[(int)$id]);
        if(!$item) jsonResponse(['erro'=>'Item não encontrado.'],404);
        if($item['quantidade_rua']>0 || $item['quantidade_reservada']>0)
            jsonResponse(['erro'=>'Não é possível excluir: há peças na rua ou reservadas.'],422);
        Database::query("DELETE FROM letreiros_estoque WHERE id=?",[(int)$id]);
        jsonResponse(['sucesso'=>true,'mensagem'=>'Item excluído do estoque.']);
    }

    // POST /api/estoque/letreiros/verificar — verifica disponibilidade de uma lista
    public function verificar(): void {
        requireLogin();
        $itens = json_decode($_POST['itens'] ?? '[]', true);
        $result = [];
        foreach ($itens as $item) {
            $char    = strtoupper($item['char'] ?? $item['caractere'] ?? '');
            $tipo    = (int)($item['tipo_id'] ?? 0);
            $tamanho = (int)($item['tamanho_id'] ?? 0);
            $qtd     = (int)($item['qtd'] ?? 1);
            $estoque = Database::fetchOne(
                "SELECT quantidade_disponivel FROM letreiros_estoque WHERE caractere=? AND tipo_id=? AND tamanho_id=?",
                [$char, $tipo, $tamanho]
            );
            $result[] = [
                'char'       => $char,
                'tipo_id'    => $tipo,
                'tamanho_id' => $tamanho,
                'qtd'        => $qtd,
                'disponivel' => $estoque ? (int)$estoque['quantidade_disponivel'] : 0,
            ];
        }
        jsonResponse(['itens' => $result]);
    }
}

// Método adicional ao LetreirosController — Venda e Exclusão
// (adicionar dentro da classe LetreirosController via patch)