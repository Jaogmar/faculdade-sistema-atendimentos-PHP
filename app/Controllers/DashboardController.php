<?php

class DashboardController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    private function json(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }

    public function resumo(): void
    {
        try {
            $totalPessoas = (int) $this->pdo->query('SELECT COUNT(*) FROM pessoas')->fetchColumn();
            $totalTipos = (int) $this->pdo->query('SELECT COUNT(*) FROM tipos_atendimentos')->fetchColumn();
            $totalAtendimentos = (int) $this->pdo->query('SELECT COUNT(*) FROM atendimentos')->fetchColumn();

            $porStatus = ['aberto' => 0, 'em_andamento' => 0, 'concluido' => 0];
            $stmt = $this->pdo->query('SELECT status, COUNT(*) AS total FROM atendimentos GROUP BY status');
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
                $porStatus[$linha['status']] = (int) $linha['total'];
            }

            $sqlRecentes = 'SELECT a.id,
                                   a.data_atendimento,
                                   a.horario_atendimento,
                                   a.status,
                                   p.nome AS pessoa_nome,
                                   t.nome AS tipo_nome,
                                   u.nome AS responsavel_nome
                            FROM atendimentos a
                            INNER JOIN pessoas p            ON p.id = a.pessoa_id
                            INNER JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
                            INNER JOIN usuarios u           ON u.id = a.usuario_id
                            ORDER BY a.id DESC
                            LIMIT 5';

            $recentes = $this->pdo->query($sqlRecentes)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($recentes as &$item) {
                $item['protocolo'] = 'ATD-' . str_pad((string) $item['id'], 4, '0', STR_PAD_LEFT);
            }
            unset($item);

            $this->json([
                'indicadores' => [
                    'total_pessoas' => $totalPessoas,
                    'total_tipos' => $totalTipos,
                    'total_atendimentos' => $totalAtendimentos,
                    'abertos' => $porStatus['aberto'],
                    'em_andamento' => $porStatus['em_andamento'],
                    'concluidos' => $porStatus['concluido'],
                ],
                'atendimentos_recentes' => $recentes,
            ]);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Erro ao carregar o resumo do dashboard.'], 500);
        }
    }
}
