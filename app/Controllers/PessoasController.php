<?php
// Controller da entidade de pessoas atendidas.
// Segue o mesmo padrão do UsuariosController: recebe a requisição, valida e acessa o banco.
class PessoasController
{
    // Conexão PDO reutilizada em todos os métodos.
    private PDO $pdo;

    public function __construct()
    {
        // Importa o arquivo que inicializa o objeto $pdo.
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = 'SELECT id, nome, documento, email, telefone, curso, periodo, status, criado_em
                FROM pessoas
                ORDER BY id DESC';

        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Lê e valida o ID recebido por GET.
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $sql = 'SELECT id, nome, documento, email, telefone, curso, periodo, status, criado_em
                FROM pessoas
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada.']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Coleta dados do formulário (POST).
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        // Regras mínimas de validação de entrada.
        if ($nome === '' || $documento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome e documento são obrigatórios.']);
            return;
        }

        // E-mail é opcional, mas se informado precisa ser válido.
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'INSERT INTO pessoas (nome, documento, email, telefone, curso, periodo, status)
                    VALUES (:nome, :documento, :email, :telefone, :curso, :periodo, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':email', $email !== '' ? $email : null);
            $stmt->bindValue(':telefone', $telefone !== '' ? $telefone : null);
            $stmt->bindValue(':curso', $curso !== '' ? $curso : null);
            $stmt->bindValue(':periodo', $periodo !== '' ? $periodo : null);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar pessoa.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // ID vem no POST para operação de update.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if (!$id || $nome === '' || $documento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID, nome e documento são obrigatórios.']);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE pessoas
                    SET nome = :nome,
                        documento = :documento,
                        email = :email,
                        telefone = :telefone,
                        curso = :curso,
                        periodo = :periodo,
                        status = :status
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':email', $email !== '' ? $email : null);
            $stmt->bindValue(':telefone', $telefone !== '' ? $telefone : null);
            $stmt->bindValue(':curso', $curso !== '' ? $curso : null);
            $stmt->bindValue(':periodo', $periodo !== '' ? $periodo : null);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
        }
    }

    public function inativar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Exclusão lógica: a pessoa é referenciada por FK em atendimentos,
        // então alteramos o status em vez de remover fisicamente.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $sql = "UPDATE pessoas SET status = 'inativo' WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar pessoa.']);
        }
    }
}
