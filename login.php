<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

redirecionar_se_logado();

$erro = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, nome, senha_hash FROM usuarios WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            // password_verify compara a senha digitada com o hash salvo
            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                // Gera novo ID de sessão após o login (previne session fixation)
                session_regenerate_id(true);

                $_SESSION['usuario_id']   = (int) $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                redirecionar('dashboard.php');
            }

            // Mensagem genérica: não revela se o e-mail existe ou não
            $erro = 'E-mail ou senha incorretos.';
        } catch (PDOException $e) {
            error_log('Erro no login: ' . $e->getMessage());
            $erro = 'Erro ao entrar. Tente novamente.';
        }
    }
}

$layout = 'auth';
$tituloPagina = 'Entrar';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-card">
    <h1 class="auth-title">Entrar na sua conta</h1>
    <p class="auth-sub">Acesse seu painel para gerenciar suas tarefas.</p>

    <?php if ($erro): ?>
        <div class="alert alert-danger d-flex align-items-start gap-2 py-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
            <div><?= e($erro) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <?= csrf_campo() ?>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email"
                   autocomplete="username" inputmode="email" placeholder="voce@empresa.com"
                   required autofocus value="<?= e($email) ?>">
            <div class="invalid-feedback">Informe um e-mail válido.</div>
        </div>

        <div class="mb-4">
            <label for="senha" class="form-label">Senha</label>
            <div class="input-group has-validation">
                <input type="password" class="form-control" id="senha" name="senha"
                       autocomplete="current-password" placeholder="Sua senha" required>
                <button type="button" class="btn btn-outline-dark btn-toggle-senha"
                        data-toggle-senha="#senha" aria-label="Mostrar senha">
                    <i class="bi bi-eye"></i>
                </button>
                <div class="invalid-feedback">Informe sua senha.</div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">Entrar</button>
    </form>

    <p class="auth-alt">
        Ainda não tem conta? <a href="register.php" class="auth-link">Cadastre-se</a>
    </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
