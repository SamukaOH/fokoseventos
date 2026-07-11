<?php
class DashboardController {

    public function index(): void {
        requireAdmin();
        $currentPage = 'dashboard';
        $pageContent = APP_PATH . '/views/dashboard/index.php';
        include APP_PATH . '/views/layout.php';
    }

    public function dadosAjax(): void {
        requireAdmin();
        jsonResponse($this->getDados());
    }

    private function q(string $sql, array $p = []): mixed {
        try { return Database::fetchOne($sql, $p); } catch (\Throwable $e) { return null; }
    }
    private function qa(string $sql, array $p = []): array {
        try { return Database::fetchAll($sql, $p); } catch (\Throwable $e) { return []; }
    }
    private function qc(string $sql, array $p = []): int {
        try { return Database::count($sql, $p); } catch (\Throwable $e) { return 0; }
    }

    private function getDados(): array {
        $demandasAtivas      = $this->qc("SELECT COUNT(*) FROM demandas WHERE status NOT IN ('finalizado','cancelado')");
        $demandasHoje        = $this->qc("SELECT COUNT(*) FROM demandas WHERE data_evento = CURDATE()");
        $demandasFinalizadas = $this->qc("SELECT COUNT(*) FROM demandas WHERE status = 'finalizado'");
        $motoristasAtivos    = $this->qc("SELECT COUNT(*) FROM usuarios WHERE tipo='motorista'");

        $proximosEventos = $this->qa(
            "SELECT d.titulo, d.status, d.data_evento, d.horario, c.nome as cliente_nome
             FROM demandas d LEFT JOIN clientes c ON c.id=d.cliente_id
             WHERE d.data_evento >= CURDATE() AND d.status NOT IN ('finalizado','cancelado')
             ORDER BY d.data_evento ASC LIMIT 5"
        );

        $proximasRetiradas = $this->qa(
            "SELECT d.titulo, d.status, d.data_retirada, d.horario_retirada, c.nome as cliente_nome, u.nome as motorista_nome
             FROM demandas d
             LEFT JOIN clientes c ON c.id=d.cliente_id
             LEFT JOIN usuarios u ON u.id=d.motorista_retirada_id
             WHERE d.data_retirada IS NOT NULL
               AND d.status IN ('entregue','montado','em_retirada')
             ORDER BY d.data_retirada ASC, d.horario_retirada ASC LIMIT 5"
        );

        $ultimasDemandas = $this->qa(
            "SELECT d.titulo, d.status, d.data_evento, c.nome as cliente_nome
             FROM demandas d LEFT JOIN clientes c ON c.id=d.cliente_id
             ORDER BY d.criado_em DESC LIMIT 8"
        );

        // Financeiro
        $finMes  = ['receitas' => 0, 'despesas' => 0, 'a_receber' => 0];
        $finAno  = ['receitas' => 0, 'despesas' => 0];
        $evolucaoMensal = [];
        $topClientes    = [];
        $semLancamento  = 0;

        $cols   = $this->qa("SHOW COLUMNS FROM financeiro LIKE 'data_lancamento'");
        $datCol = count($cols) > 0
            ? "COALESCE(data_lancamento, DATE(criado_em))"
            : "DATE(criado_em)";

        $mes = date('Y-m');
        $ano = date('Y');

        $fm = $this->q(
            "SELECT COALESCE(SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END),0) as receitas,
                    COALESCE(SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END),0) as despesas,
                    COALESCE(SUM(CASE WHEN tipo='receita' AND status='pendente' THEN valor ELSE 0 END),0) as a_receber
             FROM financeiro WHERE DATE_FORMAT($datCol,'%Y-%m')=?", [$mes]
        );
        if ($fm) $finMes = $fm;

        $fa = $this->q(
            "SELECT COALESCE(SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END),0) as receitas,
                    COALESCE(SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END),0) as despesas
             FROM financeiro WHERE YEAR($datCol)=?", [$ano]
        );
        if ($fa) $finAno = $fa;

        $evolucaoMensal = $this->qa(
            "SELECT DATE_FORMAT($datCol,'%Y-%m') as mes,
                    COALESCE(SUM(CASE WHEN tipo='receita' THEN valor ELSE 0 END),0) as receitas,
                    COALESCE(SUM(CASE WHEN tipo='despesa' THEN valor ELSE 0 END),0) as despesas
             FROM financeiro
             WHERE $datCol >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY mes ORDER BY mes ASC"
        );

        $topClientes = $this->qa(
            "SELECT c.nome, COALESCE(SUM(f.valor),0) as total
             FROM financeiro f
             JOIN demandas d ON d.id=f.demanda_id
             JOIN clientes c ON c.id=d.cliente_id
             WHERE f.tipo='receita' AND YEAR($datCol)=?
             GROUP BY c.id ORDER BY total DESC LIMIT 3", [$ano]
        );

        $semLancamento = $this->qc(
            "SELECT COUNT(*) FROM demandas d
             WHERE d.status='finalizado'
             AND NOT EXISTS (SELECT 1 FROM financeiro f WHERE f.demanda_id=d.id AND f.tipo='receita')
             AND d.data_evento >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
        );

        $lucroMes = (float)($finMes['receitas'] ?? 0) - (float)($finMes['despesas'] ?? 0);

        return [
            'demandasAtivas'      => $demandasAtivas,
            'demandasHoje'        => $demandasHoje,
            'demandasFinalizadas' => $demandasFinalizadas,
            'motoristasAtivos'    => $motoristasAtivos,
            'finMes'              => $finMes,
            'finAno'              => $finAno,
            'lucroMes'            => $lucroMes,
            'evolucaoMensal'      => $evolucaoMensal,
            'proximosEventos'     => $proximosEventos,
            'ultimasDemandas'     => $ultimasDemandas,
            'proximasRetiradas'   => $proximasRetiradas,
            'topClientes'         => $topClientes,
            'semLancamento'       => $semLancamento,
        ];
    }
}