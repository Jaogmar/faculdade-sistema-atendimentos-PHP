<?php
// Carrega os controllers responsáveis pelos endpoints da aplicação.
require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';

// Define controller e action por query string.
// Exemplo: ?controller=usuarios&action=listar
$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// Roteador simples e didático: encaminha por controller e, dentro dele, por action.
switch ($controller) {
    case 'usuarios':
        $usuariosController = new UsuariosController();

        switch ($action) {
            case 'listar':
                $usuariosController->listar();
                break;
            case 'buscar':
            case 'buscarPorId':
                $usuariosController->buscarPorId();
                break;
            case 'criar':
                $usuariosController->criar();
                break;
            case 'atualizar':
                $usuariosController->atualizar();
                break;
            case 'excluir':
                $usuariosController->excluir();
                break;
            default:
                echo 'Ação de usuários não encontrada.';
                break;
        }
        break;

    case 'pessoas':
        $pessoasController = new PessoasController();

        switch ($action) {
            case 'listar':
                $pessoasController->listar();
                break;
            case 'buscar':
            case 'buscarPorId':
                $pessoasController->buscarPorId();
                break;
            case 'criar':
                $pessoasController->criar();
                break;
            case 'atualizar':
                $pessoasController->atualizar();
                break;
            case 'inativar':
            case 'excluir':
                $pessoasController->inativar();
                break;
            default:
                echo 'Ação de pessoas não encontrada.';
                break;
        }
        break;

    case 'tipos_atendimentos':
        $tiposController = new TiposAtendimentosController();

        switch ($action) {
            case 'listar':
                $tiposController->listar();
                break;
            case 'buscar':
            case 'buscarPorId':
                $tiposController->buscarPorId();
                break;
            case 'criar':
                $tiposController->criar();
                break;
            case 'atualizar':
                $tiposController->atualizar();
                break;
            case 'inativar':
            case 'excluir':
                $tiposController->inativar();
                break;
            default:
                echo 'Ação de tipos de atendimento não encontrada.';
                break;
        }
        break;

    case 'atendimentos':
        $atendimentosController = new AtendimentosController();

        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;
            case 'buscar':
            case 'buscarPorId':
                $atendimentosController->buscarPorId();
                break;
            case 'criar':
                $atendimentosController->criar();
                break;
            case 'atualizar':
                $atendimentosController->atualizar();
                break;
            case 'atualizarStatus':
                $atendimentosController->atualizarStatus();
                break;
            case 'excluir':
                $atendimentosController->excluir();
                break;
            default:
                echo 'Ação de atendimentos não encontrada.';
                break;
        }
        break;

    default:
        // Resposta básica para indicar que a aplicação está no ar.
        echo '<h1>AtendeLab</h1>';
        echo '<p>Projeto em execução. Use ?controller=usuarios&action=listar para testar.</p>';
        break;
}
