<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    // Limpa os dados e destrói a sessão por completo
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();

    // Nova sessão apenas para exibir a mensagem de saída
    session_start();
    definir_flash('info', 'Você saiu da sua conta.');
}

redirecionar('login.php');
