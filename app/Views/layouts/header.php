<?php
require_once __DIR__ . '/config-view.php';

$usuarioLogado = function_exists('usuarioAtual') ? usuarioAtual() : null;

$tituloPagina = $tituloPagina ?? 'AtendeLab';
$paginaAtiva = $paginaAtiva ?? '';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?> - AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>assets/css/style.css" rel="stylesheet">

    <script src="<?= $baseUrl ?>assets/js/api.js" defer></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= $baseUrl ?>?controller=auth&action=dashboard">AtendeLab</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtiva === 'dashboard' ? 'active' : '' ?>"
                       href="<?= $baseUrl ?>?controller=auth&action=dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtiva === 'pessoas' ? 'active' : '' ?>"
                       href="<?= $baseUrl ?>?controller=frontend&action=pessoas">Pessoas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtiva === 'tipos' ? 'active' : '' ?>"
                       href="<?= $baseUrl ?>?controller=frontend&action=tipos">Tipos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtiva === 'atendimentos' ? 'active' : '' ?>"
                       href="<?= $baseUrl ?>?controller=frontend&action=atendimentos">Atendimentos</a>
                </li>
            </ul>

            <div class="d-flex align-items-center text-light">
                <?php if ($usuarioLogado): ?>
                    <span class="me-3 small">
                        <?= htmlspecialchars($usuarioLogado['nome'], ENT_QUOTES, 'UTF-8') ?>
                        (<?= htmlspecialchars($usuarioLogado['perfil'], ENT_QUOTES, 'UTF-8') ?>)
                    </span>
                <?php endif; ?>
                <a class="btn btn-outline-light btn-sm" href="<?= $baseUrl ?>?controller=auth&action=logout">Sair</a>
            </div>
        </div>
    </div>
</nav>

<main class="container my-4">
