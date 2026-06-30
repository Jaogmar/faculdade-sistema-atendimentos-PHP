<?php
class PessoasController
{
    private PDO $pdo;

    private const STATUS_VALIDOS = ['ativo', 'inativo'];

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

    public function listar(): void
    {
        $sql = 'SELECT id, nome, documento, telefone, email, curso, periodo,
                       observacoes, status, criado_em, atualizado_em
                FROM pessoas
                ORDER BY id DESC';

        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json($pessoas);
    }

    public function buscarPorId(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            $this->json(['erro' => 'ID inválido.'], 422);
            return;
        }

        $sql = 'SELECT id, nome, documento, telefone, email, curso, periodo,
                       observacoes, status, criado_em, atualizado_em
                FROM pessoas
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            $this->json(['erro' => 'Pessoa não encontrada.'], 404);
            return;
        }

        $this->json($pessoa);
    }

    public function criar(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if ($nome === '' || $documento === '' || $email === '') {
            $this->json(['erro' => 'Nome, documento e e-mail são obrigatórios.'], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['erro' => 'E-mail inválido.'], 422);
            return;
        }

        if (!in_array($status, self::STATUS_VALIDOS, true)) {
            $this->json(['erro' => 'Status inválido.'], 422);
            return;
        }

        try {
            $sql = 'INSERT INTO pessoas (nome, documento, telefone, email, curso, periodo, observacoes, status)
                    VALUES (:nome, :documento, :telefone, :email, :curso, :periodo, :observacoes, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':telefone', $telefone !== '' ? $telefone : null);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':curso', $curso !== '' ? $curso : null);
            $stmt->bindValue(':periodo', $periodo !== '' ? $periodo : null);
            $stmt->bindValue(':observacoes', $observacoes !== '' ? $observacoes : null);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            $this->json([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => (int) $this->pdo->lastInsertId(),
            ], 201);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->json(['erro' => 'Documento já cadastrado.'], 409);
                return;
            }

            $this->json(['erro' => 'Erro ao cadastrar pessoa.'], 500);
        }
    }

    public function atualizar(): void
    {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '' || $documento === '' || $email === '') {
            $this->json(['erro' => 'ID, nome, documento e e-mail são obrigatórios.'], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['erro' => 'E-mail inválido.'], 422);
            return;
        }

        if (!in_array($status, self::STATUS_VALIDOS, true)) {
            $this->json(['erro' => 'Status inválido.'], 422);
            return;
        }

        try {
            $sql = 'UPDATE pessoas
                    SET nome = :nome,
                        documento = :documento,
                        telefone = :telefone,
                        email = :email,
                        curso = :curso,
                        periodo = :periodo,
                        observacoes = :observacoes,
                        status = :status
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':telefone', $telefone !== '' ? $telefone : null);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':curso', $curso !== '' ? $curso : null);
            $stmt->bindValue(':periodo', $periodo !== '' ? $periodo : null);
            $stmt->bindValue(':observacoes', $observacoes !== '' ? $observacoes : null);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->json(['mensagem' => 'Pessoa atualizada com sucesso.']);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->json(['erro' => 'Documento já cadastrado.'], 409);
                return;
            }

            $this->json(['erro' => 'Erro ao atualizar pessoa.'], 500);
        }
    }

    public function inativar(): void
    {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            $this->json(['erro' => 'ID inválido.'], 422);
            return;
        }

        try {
            $sql = "UPDATE pessoas SET status = 'inativo' WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->json(['mensagem' => 'Pessoa inativada com sucesso.']);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Erro ao inativar pessoa.'], 500);
        }
    }
}
