<?php
/**
 * Cabeçalho comum: <head>, navegação e mensagens flash.
 *
 * Variáveis esperadas (todas opcionais):
 *   $tituloPagina - título da aba
 *   $base         - prefixo de caminho ('' na raiz, '../' em tasks/)
 *   $layout       - 'app' (padrão, com barra superior) ou 'auth' (login/cadastro)
 */
$tituloPagina = $tituloPagina ?? 'Gerenciador de Tarefas';
$base   = $base ?? '';
$layout = $layout ?? 'app';
$flash  = obter_flash();

$versaoCss = @filemtime(__DIR__ . '/../assets/css/style.css') ?: 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111111">
    <title><?= e($tituloPagina) ?> | Task Manager</title>

    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23111111'/%3E%3Cpath d='M9 16.5l4.5 4.5L23 11.5' fill='none' stroke='%23ff6b35' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e($base) ?>assets/css/style.css?v=<?= (int) $versaoCss ?>" rel="stylesheet">
</head>

<?php if ($layout === 'auth'): ?>
<body class="auth-body">

<div class="auth-shell">
    <aside class="auth-brand">
        <a class="brand brand--light" href="<?= e($base) ?>index.php">
            <span class="brand-mark"><i class="bi bi-check2"></i></span>
            <span class="brand-name">Task<span>Manager</span></span>
        </a>

        <div class="auth-brand__copy d-none d-lg-block">
            <h2>Organize o trabalho.<br>Entregue no prazo.</h2>
            <p>Um painel simples para planejar, priorizar e acompanhar tudo o que precisa ser feito.</p>
            <ul class="auth-points">
                <li><i class="bi bi-flag"></i> Prioridades alta, média e baixa</li>
                <li><i class="bi bi-funnel"></i> Filtros por status em um clique</li>
                <li><i class="bi bi-shield-lock"></i> Seus dados visíveis só para você</li>
            </ul>
        </div>

        <p class="auth-brand__foot d-none d-lg-block">&copy; <?= date('Y') ?> Task Manager</p>
    </aside>

    <main class="auth-main">
        <div class="auth-box">
            <?= flash_alerta($flash) ?>

<?php else: ?>
<body>

<header class="app-nav">
    <div class="container app-nav__inner">
        <a class="brand" href="<?= e($base) ?>index.php">
            <span class="brand-mark"><i class="bi bi-check2"></i></span>
            <span class="brand-name">Task<span>Manager</span></span>
        </a>

        <?php if (usuario_logado()): ?>
            <div class="nav-user">
                <div class="avatar" aria-hidden="true"><?= e(iniciais($_SESSION['usuario_nome'] ?? '')) ?></div>
                <span class="nav-user__name d-none d-sm-inline"><?= e($_SESSION['usuario_nome'] ?? '') ?></span>
                <!-- Logout via POST + CSRF evita saída forçada por links externos -->
                <form action="<?= e($base) ?>logout.php" method="POST" class="m-0">
                    <?= csrf_campo() ?>
                    <button type="submit" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-right"></i><span class="d-none d-sm-inline">Sair</span>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</header>

<main class="app-main container">
    <?= flash_alerta($flash) ?>

<?php endif; ?>
