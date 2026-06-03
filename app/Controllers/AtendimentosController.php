<?php
// Controller da entidade de atendimentos.
// Demonstra o uso de JOIN para trazer os nomes relacionados (usuário, pessoa e tipo).
class AtendimentosController
{
    // Conexão PDO reutilizada em todos os métodos.
    private PDO $pdo;

    // Status válidos para um atendimento.
    private const STATUS_VALIDOS = ['aberto', 'em_andamento', 'concluido', 'cancelado'];

    public function __construct()
    {
        // Importa o arquivo que inicializa o objeto $pdo.
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // JOIN com usuarios, pessoas e tipos_atendimentos para exibir os nomes.
        $sql = 'SELECT a.id,
                       a.data,
                       a.hora,
                       a.descricao,
                       a.observacao_final,
                       a.status,
                       a.criado_em,
                       u.id   AS usuario_id,
                       u.nome AS usuario_nome,
                       p.id   AS pessoa_id,
                       p.nome AS pessoa_nome,
                       t.id   AS tipo_atendimento_id,
                       t.nome AS tipo_atendimento_nome
                FROM atendimentos a
                INNER JOIN usuarios u            ON u.id = a.usuario_id
                INNER JOIN pessoas p             ON p.id = a.pessoa_id
                INNER JOIN tipos_atendimentos t  ON t.id = a.tipo_atendimento_id
                ORDER BY a.id DESC';

        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $sql = 'SELECT a.id,
                       a.data,
                       a.hora,
                       a.descricao,
                       a.observacao_final,
                       a.status,
                       a.criado_em,
                       u.id   AS usuario_id,
                       u.nome AS usuario_nome,
                       p.id   AS pessoa_id,
                       p.nome AS pessoa_nome,
                       t.id   AS tipo_atendimento_id,
                       t.nome AS tipo_atendimento_nome
                FROM atendimentos a
                INNER JOIN usuarios u            ON u.id = a.usuario_id
                INNER JOIN pessoas p             ON p.id = a.pessoa_id
                INNER JOIN tipos_atendimentos t  ON t.id = a.tipo_atendimento_id
                WHERE a.id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento não encontrado.']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $data = trim($_POST['data'] ?? '');
        $hora = trim($_POST['hora'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoId = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? 'aberto';

        if ($data === '' || $hora === '' || !$usuarioId || !$pessoaId || !$tipoId) {
            http_response_code(400);
            echo json_encode(['erro' => 'Data, hora, usuario_id, pessoa_id e tipo_atendimento_id são obrigatórios.']);
            return;
        }

        if (!in_array($status, self::STATUS_VALIDOS, true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'INSERT INTO atendimentos (data, hora, descricao, status, usuario_id, pessoa_id, tipo_atendimento_id)
                    VALUES (:data, :hora, :descricao, :status, :usuario_id, :pessoa_id, :tipo_atendimento_id)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':data', $data);
            $stmt->bindValue(':hora', $hora);
            $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':pessoa_id', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipoId, PDO::PARAM_INT);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento cadastrado com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            // Geralmente cai aqui se algum id referenciado (FK) não existir.
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar atendimento.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $data = trim($_POST['data'] ?? '');
        $hora = trim($_POST['hora'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');
        $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoId = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? 'aberto';

        if (!$id || $data === '' || $hora === '' || !$usuarioId || !$pessoaId || !$tipoId) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, data, hora, usuario_id, pessoa_id e tipo_atendimento_id são obrigatórios.']);
            return;
        }

        if (!in_array($status, self::STATUS_VALIDOS, true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE atendimentos
                    SET data = :data,
                        hora = :hora,
                        descricao = :descricao,
                        observacao_final = :observacao_final,
                        status = :status,
                        usuario_id = :usuario_id,
                        pessoa_id = :pessoa_id,
                        tipo_atendimento_id = :tipo_atendimento_id
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':data', $data);
            $stmt->bindValue(':hora', $hora);
            $stmt->bindValue(':descricao', $descricao !== '' ? $descricao : null);
            $stmt->bindValue(':observacao_final', $observacaoFinal !== '' ? $observacaoFinal : null);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':pessoa_id', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipoId, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar atendimento.']);
        }
    }

    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Atualização apenas do status do atendimento (fluxo mais comum no dia a dia).
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        if (!in_array($status, self::STATUS_VALIDOS, true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE atendimentos
                    SET status = :status,
                        observacao_final = :observacao_final
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':observacao_final', $observacaoFinal !== '' ? $observacaoFinal : null);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Status do atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar status do atendimento.']);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Atendimento não é referenciado por outras tabelas, então a exclusão física é segura.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $sql = 'DELETE FROM atendimentos WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Atendimento excluído com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir atendimento.']);
        }
    }
}
