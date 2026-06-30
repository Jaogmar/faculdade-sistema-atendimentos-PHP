<?php
// Controller da entidade de atendimentos.
// Usa JOIN para trazer os nomes relacionados (pessoa, tipo e responsável).
class AtendimentosController
{
    // Conexão PDO reutilizada em todos os métodos.
    private PDO $pdo;

    // Status válidos para um atendimento (RN05).
    private const STATUS_VALIDOS = ['aberto', 'em_andamento', 'concluido'];

    // Status permitidos na CRIAÇÃO de um atendimento (não nasce concluído).
    private const STATUS_INICIAIS = ['aberto', 'em_andamento'];

    public function __construct()
    {
        // Importa o arquivo que inicializa o objeto $pdo.
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    // Helper de resposta JSON: centraliza status HTTP, header e encoding.
    private function json(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }

    // Protocolo visual a partir do ID, sem coluna nova (ex.: 248 -> ATD-0248).
    private function protocolo(int $id): string
    {
        return 'ATD-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    // SELECT base com os JOINs e os aliases esperados pela spec.
    private function sqlBase(): string
    {
        return 'SELECT a.id,
                       a.data_atendimento,
                       a.horario_atendimento,
                       a.descricao,
                       a.observacao_final,
                       a.status,
                       a.criado_em,
                       a.atualizado_em,
                       a.pessoa_id,
                       a.tipo_atendimento_id,
                       a.usuario_id,
                       p.nome AS pessoa_nome,
                       t.nome AS tipo_nome,
                       u.nome AS responsavel_nome
                FROM atendimentos a
                INNER JOIN pessoas p             ON p.id = a.pessoa_id
                INNER JOIN tipos_atendimentos t  ON t.id = a.tipo_atendimento_id
                INNER JOIN usuarios u            ON u.id = a.usuario_id';
    }

    public function listar(): void
    {
        $sql = $this->sqlBase() . ' ORDER BY a.id DESC';

        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Adiciona o protocolo formatado a cada registro.
        foreach ($atendimentos as &$atendimento) {
            $atendimento['protocolo'] = $this->protocolo((int) $atendimento['id']);
        }
        unset($atendimento);

        $this->json($atendimentos);
    }

    public function buscarPorId(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            $this->json(['erro' => 'ID inválido.'], 422);
            return;
        }

        $sql = $this->sqlBase() . ' WHERE a.id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            $this->json(['erro' => 'Atendimento não encontrado.'], 404);
            return;
        }

        $atendimento['protocolo'] = $this->protocolo((int) $atendimento['id']);

        $this->json($atendimento);
    }

    public function criar(): void
    {
        $dataAtendimento = trim($_POST['data_atendimento'] ?? '');
        $horarioAtendimento = trim($_POST['horario_atendimento'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoId = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        // O responsável vem da sessão (usuário logado), com fallback para o POST.
        $usuarioId = $this->usuarioResponsavel();
        $status = $_POST['status'] ?? 'aberto';

        // RN12: campos obrigatórios antes de qualquer acesso ao banco.
        if (
            $dataAtendimento === '' || $horarioAtendimento === '' || $descricao === ''
            || !$pessoaId || !$tipoId || !$usuarioId
        ) {
            $this->json([
                'erro' => 'data_atendimento, horario_atendimento, descricao, pessoa_id '
                    . 'e tipo_atendimento_id são obrigatórios.',
            ], 422);
            return;
        }

        // RN05: na criação o atendimento nasce aberto ou em andamento.
        if (!in_array($status, self::STATUS_INICIAIS, true)) {
            $this->json(['erro' => "Status inicial inválido (use 'aberto' ou 'em_andamento')."], 422);
            return;
        }

        // RN02/RN03/RN04: confere existência real das FKs antes do INSERT,
        // devolvendo 422 claro em vez de depender da PDOException.
        if (!$this->registroExiste('pessoas', $pessoaId)) {
            $this->json(['erro' => 'Pessoa informada não existe.'], 422);
            return;
        }

        if (!$this->registroExiste('tipos_atendimentos', $tipoId)) {
            $this->json(['erro' => 'Tipo de atendimento informado não existe.'], 422);
            return;
        }

        // RN04: o usuário responsável precisa existir E estar ativo.
        if (!$this->usuarioAtivoExiste($usuarioId)) {
            $this->json(['erro' => 'Usuário responsável inexistente ou inativo.'], 422);
            return;
        }

        try {
            $sql = 'INSERT INTO atendimentos
                        (pessoa_id, tipo_atendimento_id, usuario_id, descricao, status,
                         data_atendimento, horario_atendimento)
                    VALUES
                        (:pessoa_id, :tipo_atendimento_id, :usuario_id, :descricao, :status,
                         :data_atendimento, :horario_atendimento)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipoId, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':data_atendimento', $dataAtendimento);
            $stmt->bindValue(':horario_atendimento', $horarioAtendimento);
            $stmt->execute();

            $id = (int) $this->pdo->lastInsertId();

            $this->json([
                'mensagem' => 'Atendimento cadastrado com sucesso.',
                'id' => $id,
                'protocolo' => $this->protocolo($id),
            ], 201);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Erro ao cadastrar atendimento.'], 500);
        }
    }

    public function alterarStatus(): void
    {
        // Fluxo mais comum no dia a dia: avançar o status do atendimento.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');

        if (!$id) {
            $this->json(['erro' => 'ID inválido.'], 422);
            return;
        }

        // RN05: status precisa estar no conjunto válido.
        if (!in_array($status, self::STATUS_VALIDOS, true)) {
            $this->json(['erro' => 'Status inválido.'], 422);
            return;
        }

        // RN06: concluir exige observacao_final.
        if ($status === 'concluido' && $observacaoFinal === '') {
            $this->json(['erro' => 'observacao_final é obrigatória para concluir o atendimento.'], 422);
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

            $this->json(['mensagem' => 'Status do atendimento atualizado com sucesso.']);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Erro ao atualizar status do atendimento.'], 500);
        }
    }

    public function atualizar(): void
    {
        // Edição completa de um atendimento (utilitário; fora das rotas mínimas).
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $dataAtendimento = trim($_POST['data_atendimento'] ?? '');
        $horarioAtendimento = trim($_POST['horario_atendimento'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');
        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoId = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        // O responsável vem da sessão (usuário logado), com fallback para o POST.
        $usuarioId = $this->usuarioResponsavel();
        $status = $_POST['status'] ?? 'aberto';

        if (
            !$id || $dataAtendimento === '' || $horarioAtendimento === '' || $descricao === ''
            || !$pessoaId || !$tipoId || !$usuarioId
        ) {
            $this->json([
                'erro' => 'id, data_atendimento, horario_atendimento, descricao, pessoa_id '
                    . 'e tipo_atendimento_id são obrigatórios.',
            ], 422);
            return;
        }

        if (!in_array($status, self::STATUS_VALIDOS, true)) {
            $this->json(['erro' => 'Status inválido.'], 422);
            return;
        }

        if ($status === 'concluido' && $observacaoFinal === '') {
            $this->json(['erro' => 'observacao_final é obrigatória para concluir o atendimento.'], 422);
            return;
        }

        if (!$this->registroExiste('pessoas', $pessoaId)) {
            $this->json(['erro' => 'Pessoa informada não existe.'], 422);
            return;
        }

        if (!$this->registroExiste('tipos_atendimentos', $tipoId)) {
            $this->json(['erro' => 'Tipo de atendimento informado não existe.'], 422);
            return;
        }

        if (!$this->usuarioAtivoExiste($usuarioId)) {
            $this->json(['erro' => 'Usuário responsável inexistente ou inativo.'], 422);
            return;
        }

        try {
            $sql = 'UPDATE atendimentos
                    SET pessoa_id = :pessoa_id,
                        tipo_atendimento_id = :tipo_atendimento_id,
                        usuario_id = :usuario_id,
                        descricao = :descricao,
                        observacao_final = :observacao_final,
                        status = :status,
                        data_atendimento = :data_atendimento,
                        horario_atendimento = :horario_atendimento
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipoId, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':observacao_final', $observacaoFinal !== '' ? $observacaoFinal : null);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':data_atendimento', $dataAtendimento);
            $stmt->bindValue(':horario_atendimento', $horarioAtendimento);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->json(['mensagem' => 'Atendimento atualizado com sucesso.']);
        } catch (PDOException $e) {
            $this->json(['erro' => 'Erro ao atualizar atendimento.'], 500);
        }
    }

    // Define o usuário responsável pelo atendimento. No navegador o usuário já
    // está autenticado, então usamos $_SESSION['usuario']['id']. O fallback para
    // o POST mantém compatibilidade com testes via cliente HTTP (sem sessão).
    private function usuarioResponsavel(): int
    {
        if (isset($_SESSION['usuario']['id'])) {
            return (int) $_SESSION['usuario']['id'];
        }

        return (int) filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    }

    // Verifica se existe um registro com o ID informado em uma tabela.
    private function registroExiste(string $tabela, int $id): bool
    {
        // $tabela vem apenas de chamadas internas (literais), nunca do usuário.
        $sql = "SELECT 1 FROM {$tabela} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    // RN04: usuário precisa existir e estar com status 'ativo'.
    private function usuarioAtivoExiste(int $id): bool
    {
        $sql = "SELECT 1 FROM usuarios WHERE id = :id AND status = 'ativo' LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }
}
