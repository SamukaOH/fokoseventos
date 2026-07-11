<?php
class FinanceiroController {

    private static ?array $_cols = null;

    private function cols(): array {
        if (self::$_cols === null) {
            try {
                $rows = Database::fetchAll("SHOW COLUMNS FROM financeiro");
                self::$_cols = array_column($rows, 'Field');
            } catch (\Throwable $e) {
                self::$_cols = ['id','tipo','descricao','categoria','valor','status','criado_em'];
            }
        }
        return self::$_cols;
    }

    private function temCol(string $c): bool {
        return in_array($c, $this->cols());
    }

    private function dc(string $alias = ''): string {
        $p = $alias ? "$alias." : "";
        return $this->temCol('data_lancamento')
            ? "COALESCE({$p}data_lancamento, DATE({$p}criado_em))"
            : "DATE({$p}criado_em)";
    }

    // GET /financeiro
    public function index(): void {
        requireAdmin();
        $currentPage = 'financeiro';
        $pageContent = APP_PATH . '/views/financeiro/index.php';
        include APP_PATH . '/views/layout.php';
    }

    // GET /api/financeiro
    public function lista(): void {
        requireAdmin();
        $modo = $_GET['modo'] ?? 'mensal';
        $mes  = $_GET['mes']  ?? date('Y-m');
        $ano  = $_GET['ano']  ?? date('Y');
        $dc   = $this->dc('f');

        $lancamentos = [];
        $rec = 0;
        $desp = 0;

        try {
            if ($modo === 'anual') {
                $where  = "WHERE YEAR($dc) = ?";
                $params = [(int)$ano];
            } else {
                $where  = "WHERE DATE_FORMAT($dc,'%Y-%m') = ?";
                $params = [$mes];
            }

            $extraCols = '';
            if ($this->temCol('data_lancamento'))  $extraCols .= ', f.data_lancamento';
            if ($this->temCol('subtotal'))         $extraCols .= ', f.subtotal';
            if ($this->temCol('desconto_tipo'))    $extraCols .= ', f.desconto_tipo';
            if ($this->temCol('desconto_valor'))   $extraCols .= ', f.desconto_valor';
            if ($this->temCol('frete'))            $extraCols .= ', f.frete';
            if ($this->temCol('valor_motorista'))  $extraCols .= ', f.valor_motorista';

            $joinUser = $this->temCol('criado_por') ? "LEFT JOIN usuarios u ON u.id = f.criado_por" : "";
            $colUser  = $this->temCol('criado_por') ? ", u.nome as criado_por_nome" : "";

            $lancamentos = Database::fetchAll(
                "SELECT f.id, f.tipo, f.descricao, f.categoria, f.valor, f.status,
                        f.demanda_id, f.criado_em $extraCols $colUser,
                        d.titulo as demanda_titulo
                 FROM financeiro f
                 LEFT JOIN demandas d ON d.id = f.demanda_id
                 $joinUser
                 $where
                 ORDER BY $dc DESC, f.id DESC",
                $params
            );

            foreach ($lancamentos as $l) {
                if ($l['tipo'] === 'receita') $rec  += (float)$l['valor'];
                else                          $desp += (float)$l['valor'];
            }
        } catch (\Throwable $e) {
            $lancamentos = [];
        }

        // Evolução 12 meses
        $evolucao = [];
        try {
            $dc2 = $this->dc();
            $evolucao = Database::fetchAll(
                "SELECT DATE_FORMAT($dc2,'%Y-%m') as mes,
                        COALESCE(SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END),0) as receitas,
                        COALESCE(SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END),0) as despesas
                 FROM financeiro
                 WHERE $dc2 >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY mes ORDER BY mes ASC"
            );
        } catch (\Throwable $e) {}

        // Demandas para o select
        $demandas = [];
        try {
            $demandas = Database::fetchAll(
                "SELECT d.id, d.titulo, d.data_evento, d.status, c.nome as cliente_nome
                 FROM demandas d
                 LEFT JOIN clientes c ON c.id = d.cliente_id
                 WHERE d.status != 'cancelado'
                 ORDER BY d.data_evento DESC
                 LIMIT 300"
            );
        } catch (\Throwable $e) {
            $demandas = Database::fetchAll(
                "SELECT id, titulo, data_evento, status FROM demandas WHERE status != 'cancelado' ORDER BY data_evento DESC LIMIT 300"
            );
        }

        $precos = $this->listarPrecos();

        jsonResponse([
            'lancamentos'    => $lancamentos,
            'total_receitas' => $rec,
            'total_despesas' => $desp,
            'lucro'          => $rec - $desp,
            'evolucao'       => $evolucao,
            'demandas'       => $demandas,
            'precos'         => $precos,
        ]);
    }

    // POST /api/financeiro
    public function criar(): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? '')) jsonResponse(['erro' => 'CSRF inválido.'], 403);

        $tipo      = in_array($_POST['tipo'] ?? '', ['receita','despesa']) ? $_POST['tipo'] : 'receita';
        $descricao = sanitize($_POST['descricao'] ?? '');
        $categoria = sanitize($_POST['categoria'] ?? 'Geral');
        $demandaId = (int)($_POST['demanda_id'] ?? 0) ?: null;
        $subtotal  = (float)str_replace(',', '.', $_POST['subtotal']        ?? 0);
        $descTipo  = in_array($_POST['desconto_tipo'] ?? '', ['valor','percentual']) ? $_POST['desconto_tipo'] : null;
        $descVal   = (float)str_replace(',', '.', $_POST['desconto_valor']  ?? 0);
        $frete     = (float)str_replace(',', '.', $_POST['frete']           ?? 0);
        $valMot    = (float)str_replace(',', '.', $_POST['valor_motorista'] ?? 0);
        $dataLanc  = $_POST['data_lancamento'] ?? date('Y-m-d');
        $status    = in_array($_POST['status'] ?? '', ['pago','pendente']) ? $_POST['status'] : 'pendente';

        if (!$descricao) jsonResponse(['erro' => 'Descrição obrigatória.'], 422);

        $desconto = 0;
        if ($descTipo === 'percentual') $desconto = round($subtotal * $descVal / 100, 2);
        elseif ($descTipo === 'valor')  $desconto = $descVal;
        $valor = max(0, $subtotal - $desconto + $frete);
        if ($valor == 0) $valor = max(0, (float)str_replace(',', '.', $_POST['valor'] ?? 0));

        $fields = ['tipo','descricao','categoria','valor','status'];
        $values = [$tipo, $descricao, $categoria, $valor, $status];
        $marks  = ['?','?','?','?','?'];

        $optionals = [
            'demanda_id'      => $demandaId,
            'subtotal'        => $subtotal,
            'desconto_tipo'   => $descTipo,
            'desconto_valor'  => $descVal,
            'frete'           => $frete,
            'valor_motorista' => $valMot,
            'data_lancamento' => $dataLanc,
            'criado_por'      => $_SESSION['user_id'] ?? null,
        ];
        foreach ($optionals as $col => $val) {
            if ($this->temCol($col)) {
                $fields[] = $col;
                $values[] = $val;
                $marks[]  = '?';
            }
        }
        $fields[] = 'criado_em';
        $marks[]  = 'NOW()';

        $id = Database::insert(
            "INSERT INTO financeiro (" . implode(',', $fields) . ") VALUES (" . implode(',', $marks) . ")",
            $values
        );

        if ($valMot > 0 && $this->temCol('valor_motorista')) {
            $f2 = ['tipo','descricao','categoria','valor','status'];
            $v2 = ['despesa', "Motorista — $descricao", 'Motorista', $valMot, $status];
            $m2 = ['?','?','?','?','?'];
            foreach (['demanda_id','data_lancamento','criado_por'] as $col) {
                if ($this->temCol($col)) {
                    $f2[] = $col; $m2[] = '?';
                    $v2[] = $col === 'demanda_id' ? $demandaId : ($col === 'data_lancamento' ? $dataLanc : ($_SESSION['user_id'] ?? null));
                }
            }
            $f2[] = 'criado_em'; $m2[] = 'NOW()';
            Database::insert("INSERT INTO financeiro (" . implode(',', $f2) . ") VALUES (" . implode(',', $m2) . ")", $v2);
        }

        logActivity("Lançamento: $descricao R$ $valor", 'financeiro', $id);
        jsonResponse(['sucesso' => true, 'id' => $id, 'valor_final' => $valor]);
    }

    // GET /api/financeiro/orcamento/:id
    public function orcamento(string $demandaId): void {
        requireAdmin();
        $dem = Database::fetchOne(
            "SELECT d.*, c.nome as cliente_nome FROM demandas d LEFT JOIN clientes c ON c.id=d.cliente_id WHERE d.id=?",
            [(int)$demandaId]
        );
        if (!$dem) jsonResponse(['erro' => 'Demanda não encontrada.'], 404);

        $tbl = Database::fetchAll("SHOW TABLES LIKE 'letreiros_precos'");
        if (empty($tbl)) jsonResponse(['demanda' => $dem, 'linhas' => [], 'subtotal' => 0]);

        try {
            $grupos = Database::fetchAll(
                "SELECT dl.tipo_id, dl.tamanho_id,
                        t.nome as tipo_nome, s.nome as tamanho_nome,
                        COUNT(DISTINCT IFNULL(dl.posicao_trecho, dl.id)) as qtd,
                        p.preco_unitario, p.descricao as preco_desc
                 FROM demanda_letreiros dl
                 JOIN letreiros_tipos t ON t.id = dl.tipo_id
                 JOIN letreiros_tamanhos s ON s.id = dl.tamanho_id
                 LEFT JOIN letreiros_precos p ON p.tipo_id = dl.tipo_id AND p.tamanho_id = dl.tamanho_id
                 WHERE dl.demanda_id = ?
                 GROUP BY dl.tipo_id, dl.tamanho_id",
                [(int)$demandaId]
            );
        } catch (\Throwable $e) {
            $grupos = [];
        }

        $linhas = []; $subtotal = 0;
        foreach ($grupos as $g) {
            $qtd   = max(1, (int)$g['qtd']);
            $preco = (float)($g['preco_unitario'] ?? 0);
            $total = $preco * $qtd;
            $linhas[] = ['descricao' => $g['preco_desc'] ?? ($g['tipo_nome'] . ' ' . $g['tamanho_nome']), 'qtd' => $qtd, 'preco_unit' => $preco, 'total' => $total, 'sem_preco' => ($preco == 0)];
            $subtotal += $total;
        }
        jsonResponse(['demanda' => $dem, 'linhas' => $linhas, 'subtotal' => $subtotal]);
    }

    // GET /api/financeiro/precos
    public function precos(): void {
        requireAdmin();
        jsonResponse(['precos' => $this->listarPrecos()]);
    }

    // POST /api/financeiro/precos/:id
    public function atualizarPreco(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? '')) jsonResponse(['erro' => 'CSRF inválido.'], 403);
        $preco = (float)str_replace(',', '.', $_POST['preco_unitario'] ?? 0);
        $desc  = sanitize($_POST['descricao'] ?? '');
        Database::query("UPDATE letreiros_precos SET preco_unitario=?, descricao=? WHERE id=?", [$preco, $desc, (int)$id]);
        jsonResponse(['sucesso' => true]);
    }

    // POST /api/financeiro/:id/delete
    public function deletar(string $id): void {
        requireAdmin();
        if (!verifyCsrf($_POST['_csrf'] ?? '')) jsonResponse(['erro' => 'CSRF inválido.'], 403);
        Database::query("DELETE FROM financeiro WHERE id=?", [(int)$id]);
        jsonResponse(['sucesso' => true]);
    }

    // GET /api/financeiro/dashboard
    public function dashboard(): void {
        requireAdmin();
        $ano = (int)($_GET['ano'] ?? date('Y'));
        $mes = $_GET['mes'] ?? date('Y-m');
        $dc  = $this->dc();

        $mesStat     = Database::fetchOne("SELECT COALESCE(SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END),0) as receitas, COALESCE(SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END),0) as despesas, COALESCE(SUM(CASE WHEN tipo='receita' AND status='pendente' THEN valor ELSE 0 END),0) as a_receber FROM financeiro WHERE DATE_FORMAT($dc,'%Y-%m')=?", [$mes]);
        $anoStat     = Database::fetchOne("SELECT COALESCE(SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END),0) as receitas, COALESCE(SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END),0) as despesas, COUNT(DISTINCT demanda_id) as demandas_faturadas FROM financeiro WHERE YEAR($dc)=?", [$ano]);
        $evolucao    = Database::fetchAll("SELECT DATE_FORMAT($dc,'%Y-%m') as mes, COALESCE(SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END),0) as receitas, COALESCE(SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END),0) as despesas FROM financeiro WHERE $dc >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY mes ORDER BY mes ASC");
        $dcf = $this->dc('f');
        $topClientes = Database::fetchAll("SELECT c.nome, COALESCE(SUM(f.valor),0) as total, COUNT(DISTINCT d.id) as eventos FROM financeiro f JOIN demandas d ON d.id=f.demanda_id JOIN clientes c ON c.id=d.cliente_id WHERE f.tipo='receita' AND YEAR($dcf)=? GROUP BY c.id ORDER BY total DESC LIMIT 5", [$ano]);
        $pendentes   = Database::fetchAll("SELECT d.titulo, d.data_evento, c.nome as cliente_nome, COALESCE(SUM(f.valor),0) as valor FROM financeiro f JOIN demandas d ON d.id=f.demanda_id LEFT JOIN clientes c ON c.id=d.cliente_id WHERE f.status='pendente' AND f.tipo='receita' GROUP BY d.id ORDER BY d.data_evento ASC LIMIT 10");
        $porCategoria= Database::fetchAll("SELECT categoria, COALESCE(SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END),0) as receitas, COALESCE(SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END),0) as despesas FROM financeiro WHERE YEAR($dc)=? GROUP BY categoria ORDER BY receitas DESC", [$ano]);

        jsonResponse(compact('mesStat','anoStat','evolucao','topClientes','pendentes','porCategoria'));
    }

    private function listarPrecos(): array {
        try {
            $tbls = Database::fetchAll("SHOW TABLES LIKE 'letreiros_precos'");
            if (empty($tbls)) return [];
            return Database::fetchAll(
                "SELECT p.id, p.descricao, p.preco_unitario, p.ativo,
                        t.nome as tipo_nome, s.nome as tamanho_nome
                 FROM letreiros_precos p
                 JOIN letreiros_tipos t ON t.id = p.tipo_id
                 JOIN letreiros_tamanhos s ON s.id = p.tamanho_id
                 WHERE p.ativo = 1
                 ORDER BY p.id"
            );
        } catch (\Throwable $e) {
            return [];
        }
    }
}