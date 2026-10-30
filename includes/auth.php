<?php
/**
 * Funções usadas em todo o projeto:
 * sessão, mensagens flash, proteção CSRF e controle de acesso.
 */

// Inicia a sessão (uma vez só) com o cookie mais protegido
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,   // o JavaScript não consegue ler o cookie
        'samesite' => 'Lax',  // o cookie não é enviado em requisições vindas de outros sites
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // só por HTTPS, quando o site usa HTTPS
    ]);
    session_start();
}

/** Escapa o texto antes de mostrar na tela (previne XSS) */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Redireciona para outra página e encerra o script */
function redirecionar(string $destino): void
{
    header('Location: ' . $destino);
    exit;
}

/** Guarda uma mensagem na sessão para ser mostrada uma única vez */
function definir_flash(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

/** Pega a mensagem guardada e já apaga da sessão */
function obter_flash()
{
    if (isset($_SESSION['flash'])) {
        $mensagem = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $mensagem;
    }
    return null;
}

/** Monta o alerta do Bootstrap com a mensagem (tipo: success, danger, warning ou info) */
function flash_alerta($flash): string
{
    if (!$flash) {
        return '';
    }

    $tipo = in_array($flash['tipo'], ['success', 'danger', 'warning', 'info'], true) ? $flash['tipo'] : 'info';

    return '<div class="alert alert-' . $tipo . ' alert-dismissible fade show mt-3 mb-0" role="alert">'
         . e($flash['texto'])
         . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>'
         . '</div>';
}

/** Gera o token CSRF da sessão (uma vez só) */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Campo escondido com o token, para colocar dentro de todo formulário POST */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Confere se o token enviado é o mesmo da sessão; se não for, bloqueia a requisição */
function csrf_validar(): void
{
    $enviado  = $_POST['csrf_token'] ?? '';
    $esperado = $_SESSION['csrf_token'] ?? '';

    if ($esperado === '' || !is_string($enviado) || !hash_equals($esperado, $enviado)) {
        http_response_code(403);
        exit('Requisição inválida. Volte à página anterior, atualize e tente novamente.');
    }
}

/** Verifica se existe um usuário logado */
function usuario_logado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Páginas privadas: sem login, volta para a tela de login.
 * $base é '' na raiz do site e '../' dentro da pasta tasks/.
 */
function exigir_login(string $base = ''): void
{
    if (!usuario_logado()) {
        redirecionar($base . 'login.php');
    }
}

/** Login e cadastro: quem já está logado vai direto para o painel */
function redirecionar_se_logado(): void
{
    if (usuario_logado()) {
        redirecionar('dashboard.php');
    }
}
