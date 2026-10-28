<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

redirecionar_se_logado();

$erros = [];
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $senha     = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    // Validação no servidor (a do navegador pode ser contornada)
    if ($nome === '' || mb_strlen($nome) > 100) {
        $erros[] = 'Informe um nome com até 100 caracteres.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (strlen($senha) < 8) {
        $erros[] = 'A senha deve ter no mínimo 8 caracteres.';
    }
    if ($senha !== $confirmar) {
        $erros[] = 'As senhas não coincidem.';
    }

    if (empty($erros)) {
        try {
            // Verifica se o e-mail já está cadastrado
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
            $stmt->execute([':email' => $email]);

            if ($stmt->fetch()) {
                $erros[] = 'Este e-mail já está cadastrado.';
            } else {
                // password_hash gera um hash seguro com salt automático
                $hash = password_hash($senha, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    'INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :hash)'
                );
                $stmt->execute([
                    ':nome'  => $nome,
                    ':email' => $email,
                    ':hash'  => $hash,
                ]);

                definir_flash('success', 'Cadastro realizado! Faça login para continuar.');
                redirecionar('login.php');
            }
        } catch (PDOException $e) {
            error_log('Erro no cadastro: ' . $e->getMessage());
            $erros[] = 'Erro ao cadastrar. Tente novamente.';
        }
    }
}

$layout = 'auth';
$tituloPagina = 'Criar conta';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-card">
    <h1 class="auth-title">Criar sua conta</h1>
    <p class="auth-sub">Leva menos de um minuto. Suas tarefas ficam visíveis só para você.</p>

    <?php if ($erros): ?>
        <div class="alert alert-danger py-2" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ($erros as $erro): ?>
                    <li><?= e($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="needs-validation" novalidate>
        <?= csrf_campo() ?>

        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome"
                   autocomplete="name" maxlength="100" placeholder="Seu nome completo"
                   required autofocus value="<?= e($nome) ?>">
            <div class="invalid-feedback">Informe seu nome.</div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email"
                   autocomplete="username" inputmode="email" maxlength="150"
                   placeholder="voce@empresa.com" required value="<?= e($email) ?>">
            <div class="invalid-feedback">Informe um e-mail válido.</div>
        </div>

        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <div class="input-group has-validation">
                <input type="password" class="form-control" id="senha" name="senha"
                       autocomplete="new-password" minlength="8" placeholder="Mínimo de 8 caracteres" required>
                <button type="button" class="btn btn-outline-dark btn-toggle-senha"
                        data-toggle-senha="#senha" aria-label="Mostrar senha">
                    <i class="bi bi-eye"></i>
                </button>
                <div class="invalid-feedback">Mínimo de 8 caracteres.</div>
            </div>
        </div>

        <div class="mb-4">
            <label for="confirmar_senha" class="form-label">Confirmar senha</label>
            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha"
                   autocomplete="new-password" placeholder="Repita a senha" required>
            <div class="invalid-feedback">As senhas precisam ser iguais.</div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">Criar conta</button>
    </form>

    <p class="auth-alt">
        Já tem conta? <a href="login.php" class="auth-link">Entrar</a>
    </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
