<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

redirecionar_se_logado();

// Mensagem mostrada depois que o logout redireciona para cá
if (isset($_GET['saiu'])) {
    definir_flash('info', 'Você saiu da sua conta.');
}

$maxTentativas   = 3;  // erros de senha permitidos
$minutosBloqueio = 5;  // tempo de espera depois de errar demais

// O contador de erros fica guardado na sessão
if (!isset($_SESSION['tentativas'])) {
    $_SESSION['tentativas'] = 0;
}

// Se o tempo de bloqueio já passou, libera e zera o contador
if (isset($_SESSION['bloqueado_ate']) && time() >= $_SESSION['bloqueado_ate']) {
    unset($_SESSION['bloqueado_ate']);
    $_SESSION['tentativas'] = 0;
}

$bloqueado = isset($_SESSION['bloqueado_ate']);
$erro = null;
$email = '';

if ($bloqueado) {
    $minutosRestantes = ceil(($_SESSION['bloqueado_ate'] - time()) / 60);
    $erro = 'Excedeu o número de tentativas. Tente novamente em ' . $minutosRestantes . ' minuto(s).';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bloqueado) {
    csrf_validar();

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } else {
        try {
            // Busca o usuário pelo e-mail (prepared statement)
            $stmt = $pdo->prepare('SELECT id, nome, senha_hash FROM usuarios WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            // password_verify compara a senha digitada com o hash salvo no banco
            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                // Novo ID de sessão após o login (previne session fixation)
                session_regenerate_id(true);

                $_SESSION['usuario_id']   = (int) $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['tentativas']   = 0; // acertou: zera o contador

                redirecionar('dashboard.php');
            }

            // Errou: conta a tentativa
            $_SESSION['tentativas']++;

            if ($_SESSION['tentativas'] >= $maxTentativas) {
                // Chegou no limite: bloqueia por alguns minutos
                $_SESSION['bloqueado_ate'] = time() + $minutosBloqueio * 60;
                $bloqueado = true;
                $erro = 'Excedeu o número de tentativas. Tente novamente em ' . $minutosBloqueio . ' minuto(s).';
            } else {
                // Mensagem genérica: não revela se o e-mail existe ou não
                $restantes = $maxTentativas - $_SESSION['tentativas'];
                $erro = 'E-mail ou senha incorretos. Restam ' . $restantes . ' tentativa(s).';
            }
        } catch (PDOException $e) {
            error_log('Erro no login: ' . $e->getMessage());
            $erro = 'Erro ao entrar. Tente novamente.';
        }
    }
}

$tituloPagina  = 'Entrar';
$heroTitulo    = 'Bem-vindo!';
$heroSub       = 'Entre para organizar suas tarefas.';
$authLinkUrl   = 'register.php';
$authLinkTexto = 'Criar conta';
require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center sobe">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card">
            <div class="card-body p-4">
                <h2 class="h4 mb-1">Entrar na sua conta</h2>
                <p class="text-muted mb-4">Use seu e-mail e sua senha.</p>

                <?php if ($erro): ?>
                    <div class="alert alert-danger" role="alert"><?= e($erro) ?></div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <?= csrf_campo() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="voce@empresa.com" required autofocus
                                   value="<?= e($email) ?>">
                            <div class="invalid-feedback">Informe um e-mail válido.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha"
                                   placeholder="Sua senha" required>
                            <button type="button" class="btn btn-outline-secondary btn-toggle-senha"
                                    data-alvo="#senha" aria-label="Mostrar senha">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="invalid-feedback">Informe sua senha.</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" <?= $bloqueado ? 'disabled' : '' ?>>Entrar</button>
                </form>
            </div>
        </div>

        <p class="text-center text-muted mt-4">
            Ainda não tem conta? <a href="register.php" class="fw-semibold">Cadastre-se</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
