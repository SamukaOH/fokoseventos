<?php
class DemandasController {

    public function index(): void {
        requireAdmin();
        $currentPage = 'demandas';
        $pageContent = APP_PATH . '/views/demandas/index.php';
        include APP_PATH . '/views/layout.php';
    }

    // GET /api/demandas — lista (admin vê todas, motorista vê as suas)
    public function lista(): void {
        requireLogin();
        $user = getUser();
        if ($user['tipo'] === 'motorista') {
            $historico = isset($_GET['historico']);
            $statusFilter = $historico
                ? "AND d.status IN ('finalizado','devolvido')"
                : "AND d.status NOT IN ('finalizado','devolvido','cancelado')";
            $demandas = Database::fetchAll(
                "SELECT d.*, c.nome as cliente_nome,
                        GROUP_CONCAT(dl.caractere ORDER BY dl.id SEPARATOR '') as letreiros_texto
                 FROM demandas d
                 LEFT JOIN clientes c ON c.id = d.cliente_id
                 LEFT JOIN demanda_letreiros dl ON dl.demanda_id = d.id
                 WHERE (d.motorista_id = ? OR d.motorista_retirada_id = ?)
                   $statusFilter
                 GROUP BY d.id
                 ORDER BY d.data_evento DESC",
                [$user['id'], $user['id']]
            );
        } else {
            $status = $_GET['status'] ?? '';
            $where  = $status ? "AND d.status=?" : "";
            $params = $status ? [$status] : [];
            $demandas = Database::fetchAll(
                "SELECT d.*, c.nome as cliente_nome, u.nome as motorista_nome,
                        GROUP_CONCAT(dl.caractere ORDER BY dl.id SEPARATOR '') as letreiros_texto
                 FROM demandas d
                 LEFT JOIN clientes c ON c.id = d.cliente_id
                 LEFT JOIN usuarios u ON u.id = d.motorista_id
                 LEFT JOIN demanda_letreiros dl ON dl.demanda_id = d.id
                 WHERE 1=1 $where
                 GROUP BY d.id
                 ORDER BY d.data_evento DESC",
                $params
            );
        }
        jsonResponse(['demandas' => $demandas]);
    }

    // GET /api/demandas/:id
    public function getOne(string $id): void {
        requireLogin();
        $demanda = Database::fetchOne(
            "SELECT d.*, c.nome as cliente_nome, u.nome as motorista_nome
             FROM demandas d
             LEFT JOIN clientes c ON c.id = d.cliente_id
             LEFT JOIN usuarios u ON u.id = d.motorista_id
             WHERE d.id=?", [(int)$id]
        );
        if (!$demanda) jsonResponse(['erro'=>'Não encontrado.'],404);

        $letreiros = Database::fetchAll(
            "SELECT dl.*, t.nome as tipo_nome, s.nome as tamanho_nome
             FROM demanda_letreiros dl
             JOIN letreiros_tipos t ON t.id = dl.tipo_id
             JOIN letreiros_tamanhos s ON s.id = dl.tamanho_id
             WHERE dl.demanda_id=?", [(int)$id]
        );
        jsonResponse(['demanda' => $demanda, 'letreiros' => $letreiros]);
    }

    // POST /api/demandas — criar com trechos de letreiros
    public function criar(): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);

        $titulo     = sanitize($_POST['titulo'] ?? '');
        $clienteId  = (int)($_POST['cliente_id'] ?? 0) ?: null;
        $motoristId = (int)($_POST['motorista_id'] ?? 0) ?: null;
        $motRetId   = (int)($_POST['motorista_retirada_id'] ?? 0) ?: null;
        $dataEvento = $_POST['data_evento'] ?? null;
        $horario    = $_POST['horario'] ?? null;
        $horarioRet = $_POST['horario_retirada'] ?? null;
        $dataRet    = $_POST['data_retirada'] ?? null;
        $endereco   = sanitize($_POST['endereco'] ?? '');
        $telefone   = sanitize($_POST['telefone'] ?? '');
        $prio       = in_array($_POST['prioridade']??'',['baixa','media','alta','urgente']) ? $_POST['prioridade'] : 'media';
        $obs        = sanitize($_POST['observacoes'] ?? '');
        $trechos    = json_decode($_POST['trechos'] ?? '[]', true);

        if (!$titulo) jsonResponse(['erro'=>'Título obrigatório.'],422);

        // Verificar estoque para cada trecho
        foreach ($trechos as $tr) {
            $texto   = strtoupper(preg_replace('/\s/', '', $tr['texto']));
            $tipoId  = (int)$tr['tipo_id'];
            $tamId   = (int)$tr['tamanho_id'];
            $contagem = array_count_values(str_split($texto));
            foreach ($contagem as $char => $qtd) {
                $est = Database::fetchOne(
                    "SELECT quantidade_disponivel FROM letreiros_estoque WHERE caractere=? AND tipo_id=? AND tamanho_id=?",
                    [$char, $tipoId, $tamId]
                );
                $disp = $est ? (int)$est['quantidade_disponivel'] : 0;
                if ($disp < $qtd) {
                    $tipo = Database::fetchOne("SELECT nome FROM letreiros_tipos WHERE id=?", [$tipoId]);
                    $tam  = Database::fetchOne("SELECT nome FROM letreiros_tamanhos WHERE id=?", [$tamId]);
                    jsonResponse(['erro'=>"Estoque insuficiente: letra \"$char\" ({$tipo['nome']} {$tam['nome']}) — precisa $qtd, disponível $disp."],422);
                }
            }
        }

        // Criar demanda
        try {
            $demandaId = Database::insert(
                "INSERT INTO demandas (titulo,cliente_id,motorista_id,motorista_retirada_id,endereco,data_evento,horario,horario_retirada,data_retirada,telefone,prioridade,observacoes,status,criado_por) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'pendente',?)",
                [$titulo,$clienteId,$motoristId,$motRetId,$endereco,$dataEvento?:null,$horario?:null,$horarioRet?:null,$dataRet?:null,$telefone,$prio,$obs,$_SESSION['user_id']??null]
            );
        } catch (\Throwable $e) {
            $demandaId = Database::insert(
                "INSERT INTO demandas (titulo,cliente_id,motorista_id,endereco,data_evento,horario,telefone,prioridade,observacoes,status,criado_por) VALUES (?,?,?,?,?,?,?,?,?,'pendente',?)",
                [$titulo,$clienteId,$motoristId,$endereco,$dataEvento?:null,$horario?:null,$telefone,$prio,$obs,$_SESSION['user_id']??null]
            );
        }

        // Notificar motoristas
        if ($motoristId) {
            criarNotificacao($motoristId, "Nova demanda atribuída", "Você foi atribuído como motorista de entrega em: $titulo", 'info');
        }
        if ($motRetId && $motRetId !== $motoristId) {
            criarNotificacao($motRetId, "Nova demanda atribuída", "Você foi atribuído como motorista de retirada em: $titulo", 'info');
        }
        // Notificar admins
        $admins = Database::fetchAll("SELECT id FROM usuarios WHERE tipo='admin' AND status='ativo'");
        foreach ($admins as $admin) {
            criarNotificacao($admin['id'], "Demanda criada: $titulo", "Nova demanda adicionada ao sistema.", 'sucesso');
        }

        // Processar trechos: descontar estoque e criar demanda_letreiros
        foreach ($trechos as $tr) {
            $texto  = strtoupper(preg_replace('/\s/', '', $tr['texto']));
            $tipoId = (int)$tr['tipo_id'];
            $tamId  = (int)$tr['tamanho_id'];
            $chars  = str_split($texto);

            foreach ($chars as $char) {
                // Buscar item de estoque
                $estItem = Database::fetchOne(
                    "SELECT id FROM letreiros_estoque WHERE caractere=? AND tipo_id=? AND tamanho_id=?",
                    [$char, $tipoId, $tamId]
                );
                if (!$estItem) continue;

                // Descontar disponível, reservar
                Database::query(
                    "UPDATE letreiros_estoque SET quantidade_disponivel=quantidade_disponivel-1, quantidade_reservada=quantidade_reservada+1 WHERE id=?",
                    [$estItem['id']]
                );

                // Registrar na demanda
                Database::insert(
                    "INSERT INTO demanda_letreiros (demanda_id,letreiro_id,caractere,quantidade,tipo_id,tamanho_id,status) VALUES (?,?,?,1,?,?,'reservado')",
                    [$demandaId, $estItem['id'], $char, $tipoId, $tamId]
                );
            }
        }

        logActivity("Demanda criada: $titulo", 'demandas', $demandaId);
        jsonResponse(['sucesso'=>true, 'id'=>$demandaId]);
    }

    // POST /api/demandas/:id/status — motorista atualiza status
    public function atualizarStatus(string $id): void {
        requireLogin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);
        $status = $_POST['status'] ?? '';
        $statusValidos = ['pendente','preparacao','em_rota','em_retirada','entregue','devolvido','finalizado'];
        if (!in_array($status, $statusValidos)) jsonResponse(['erro'=>'Status inválido.'],422);

        $demanda = Database::fetchOne("SELECT * FROM demandas WHERE id=?", [(int)$id]);
        if (!$demanda) jsonResponse(['erro'=>'Demanda não encontrada.'],404);

        // Ao marcar em rota: letreiros passam para na_rua
        if ($status === 'em_rota') {
            // Buscar IDs dos letreiros desta demanda que estão reservados
            $lets = Database::fetchAll(
                "SELECT letreiro_id FROM demanda_letreiros WHERE demanda_id=? AND status='reservado'",
                [(int)$id]
            );
            Database::query("UPDATE demanda_letreiros SET status='na_rua' WHERE demanda_id=? AND status='reservado'", [(int)$id]);
            foreach ($lets as $l) {
                Database::query(
                    "UPDATE letreiros_estoque SET quantidade_rua=quantidade_rua+1, quantidade_reservada=quantidade_reservada-1 WHERE id=?",
                    [$l['letreiro_id']]
                );
            }
        }

        // Ao devolver ao depósito: letreiros voltam ao estoque
        if ($status === 'finalizado' || $status === 'devolvido') {
            $this->devolverLetreiros((int)$id);
        }

        Database::query("UPDATE demandas SET status=? WHERE id=?", [$status, (int)$id]);

        // Notificar admins com detalhes ricos
        $demanda = Database::fetchOne(
            "SELECT d.titulo, d.data_evento, d.horario,
                    u.nome as motorista_nome,
                    GROUP_CONCAT(dl.caractere ORDER BY dl.id SEPARATOR '') as letreiros_texto
             FROM demandas d
             LEFT JOIN usuarios u ON u.id = d.motorista_id
             LEFT JOIN demanda_letreiros dl ON dl.demanda_id = d.id
             WHERE d.id=? GROUP BY d.id",
            [(int)$id]
        );
        $statusLabels = [
            'preparacao'  => 'está preparando o letreiro',
            'em_rota'     => 'saiu para entrega',
            'em_retirada' => 'foi buscar o letreiro',
            'entregue'    => 'entregou no cliente',
            'finalizado'  => 'devolveu ao depósito',
        ];
        $statusLabel   = $statusLabels[$status] ?? $status;
        $motNome       = $demanda['motorista_nome'] ?? 'Motorista';
        $letText       = $demanda['letreiros_texto'] ? ' · "' . $demanda['letreiros_texto'] . '"' : '';
        $hora          = !empty($demanda['horario']) ? ' · ' . substr($demanda['horario'],0,5) : '';
        $data          = !empty($demanda['data_evento']) ? ' · ' . date('d/m', strtotime($demanda['data_evento'])) . $hora : '';
        $tituloNotif   = "$motNome $statusLabel";
        $msgNotif      = $demanda['titulo'] . $letText . $data;
        $admins = Database::fetchAll("SELECT id FROM usuarios WHERE tipo='admin' AND status='ativo'");
        foreach ($admins as $admin) {
            criarNotificacao($admin['id'], $tituloNotif, $msgNotif, 'info');
        }

        logActivity("Status demanda #$id: $status", 'demandas', (int)$id);
        jsonResponse(['sucesso'=>true]);
    }

    // POST /api/demandas/:id/status-admin — admin muda status manualmente
    public function atualizarStatusAdmin(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);
        $status = $_POST['status'] ?? '';
        $statusValidos = ['pendente','preparacao','em_rota','em_retirada','entregue','devolvido','finalizado','cancelado'];
        if (!in_array($status, $statusValidos)) jsonResponse(['erro'=>'Status inválido.'],422);

        if ($status === 'finalizado' || $status === 'devolvido') $this->devolverLetreiros((int)$id);

        Database::query("UPDATE demandas SET status=? WHERE id=?", [$status, (int)$id]);
        jsonResponse(['sucesso'=>true]);
    }

    // POST /api/demandas/:id/cancelar
    public function editar(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? '')) jsonResponse(['erro'=>'CSRF inválido.'],403);

        $titulo      = sanitize($_POST['titulo']      ?? '');
        $clienteId   = (int)($_POST['cliente_id']    ?? 0) ?: null;
        $endereco    = sanitize($_POST['endereco']    ?? '');
        $telefone    = sanitize($_POST['telefone']    ?? '');
        $prioridade  = in_array($_POST['prioridade']??'',['normal','alta','urgente']) ? $_POST['prioridade'] : 'normal';
        $dataEvento  = $_POST['data_evento']          ?? null;
        $horario     = $_POST['horario']              ?? null;
        $motorista   = (int)($_POST['motorista_id']  ?? 0) ?: null;
        $dataRet     = $_POST['data_retirada']        ?? null;
        $horarioRet  = $_POST['horario_retirada']     ?? null;
        $motRet      = (int)($_POST['motorista_retirada_id'] ?? 0) ?: null;
        $obs         = sanitize($_POST['observacoes'] ?? '');

        if (!$titulo) jsonResponse(['erro'=>'Título obrigatório.'],422);

        // Verificar colunas existentes
        $temDataRet = Database::fetchAll("SHOW COLUMNS FROM demandas LIKE 'data_retirada'");

        $sql = "UPDATE demandas SET titulo=?, cliente_id=?, endereco=?, telefone=?, prioridade=?,
                data_evento=?, horario=?, motorista_id=?, horario_retirada=?, motorista_retirada_id=?, observacoes=?";
        $params = [$titulo,$clienteId,$endereco,$telefone,$prioridade,
                   $dataEvento?:null,$horario?:null,$motorista,$horarioRet?:null,$motRet,$obs];

        if ($temDataRet) {
            $sql .= ", data_retirada=?";
            $params[] = $dataRet ?: null;
        }
        $sql .= " WHERE id=?";
        $params[] = (int)$id;

        Database::query($sql, $params);
        logActivity("Demanda editada: $titulo", 'demandas', (int)$id);
        jsonResponse(['sucesso'=>true]);
    }

    public function cancelar(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);
        // Devolver letreiros reservados/na_rua
        $this->devolverLetreiros((int)$id);
        Database::query("UPDATE demandas SET status='cancelado' WHERE id=?", [(int)$id]);
        jsonResponse(['sucesso'=>true]);
    }

    // POST /api/demandas/:id — motorista adiciona observação
    public function update(string $id): void {
        requireLogin();
        if (!verifyCsrf($_POST['_csrf']??'')) jsonResponse(['erro'=>'Token inválido.'],403);
        $obs = sanitize($_POST['observacoes'] ?? '');
        Database::query("UPDATE demandas SET observacoes=? WHERE id=?", [$obs, (int)$id]);
        jsonResponse(['sucesso'=>true]);
    }

    // POST /api/demandas/:id/foto
    public function uploadFoto(string $id): void {
        requireLogin();
        jsonResponse(['sucesso'=>true]);
    }

    private function devolverLetreiros(int $demandaId): void {
        // Buscar letreiros ainda não devolvidos
        $lets = Database::fetchAll(
            "SELECT * FROM demanda_letreiros WHERE demanda_id=? AND status != 'devolvido'",
            [$demandaId]
        );
        foreach ($lets as $l) {
            $col = $l['status'] === 'na_rua' ? 'quantidade_rua' : 'quantidade_reservada';
            Database::query(
                "UPDATE letreiros_estoque SET quantidade_disponivel=quantidade_disponivel+1, $col=$col-1 WHERE id=?",
                [$l['letreiro_id']]
            );
        }
        Database::query(
            "UPDATE demanda_letreiros SET status='devolvido' WHERE demanda_id=? AND status != 'devolvido'",
            [$demandaId]
        );
    }
}