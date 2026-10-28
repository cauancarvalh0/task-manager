<?php
/**
 * Sessão, segurança básica e funções auxiliares usadas em todo o projeto.
 */

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

// Inicia a sessão uma única vez, com cookie mais seguro
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,   // só envia o cookie por HTTPS quando o site usa HTTPS
        'httponly' => true,     // JavaScript não consegue ler o cookie
        'samesite' => 'Lax',    // reduz risco de CSRF entre sites
    ]);
    session_start();
}

// Cabeçalhos de segurança + evita mostrar páginas privadas pelo botão "voltar"
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: no-store, no-cache, must-revalidate');
}

/** Escapa texto para exibir em HTML (previne XSS) */
function e($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Redireciona para outra página e encerra o script */
function redirecionar(string $destino): void {
    header('Location: ' . $destino);
    exit;
}

/** Guarda uma mensagem flash na sessão (tipo: success, danger, info, warning) */
function definir_flash(string $tipo, string $texto): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

/** Retorna e apaga a mensagem flash (se existir) */
function obter_flash() {
    if (isset($_SESSION['flash'])) {
        $mensagem = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $mensagem;
    }
    return null;
}

/** Monta o HTML do alerta de uma mensagem flash */
function flash_alerta($flash): string {
    if (!$flash) {
        return '';
    }
    $icones = [
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        'info'    => 'bi-info-circle-fill',
    ];
    $tipo = isset($icones[$flash['tipo']]) ? $flash['tipo'] : 'info';

    return '<div class="alert alert-' . $tipo . ' alert-dismissible fade show d-flex align-items-start gap-2" role="alert" data-auto-fechar>'
         . '<i class="bi ' . $icones[$tipo] . ' mt-1"></i>'
         . '<div class="flex-grow-1">' . e($flash['texto']) . '</div>'
         . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>'
         . '</div>';
}

/** Iniciais do nome para o avatar (ex.: "Cauan Carvalho" -> "CC") */
function iniciais(string $nome): string {
    $partes = preg_split('/\s+/', trim($nome), -1, PREG_SPLIT_NO_EMPTY);
    if (!$partes) {
        return '?';
    }
    $texto = mb_substr($partes[0], 0, 1);
    if (count($partes) > 1) {
        $texto .= mb_substr(end($partes), 0, 1);
    }
    return mb_strtoupper($texto);
}

/** Gera (ou reaproveita) o token CSRF da sessão */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Campo hidden com o token CSRF, para usar dentro dos formulários */
function csrf_campo(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Valida o token CSRF enviado via POST */
function csrf_validar(): void {
    $enviado  = $_POST['csrf_token'] ?? '';
    $esperado = $_SESSION['csrf_token'] ?? '';

    if ($esperado === '' || !is_string($enviado) || !hash_equals($esperado, $enviado)) {
        http_response_code(403);
        exit('Requisição inválida. Volte à página anterior, atualize e tente novamente.');
    }
}

/** Verifica se o usuário está logado */
function usuario_logado(): bool {
    return isset($_SESSION['usuario_id']);
}

/**
 * Exige login. $base é o prefixo de caminho:
 * '' na raiz e '../' dentro da pasta tasks/.
 */
function exigir_login(string $base = ''): void {
    if (!usuario_logado()) {
        redirecionar($base . 'login.php');
    }
}

/** Se já estiver logado, manda para o painel (usado em login/cadastro) */
function redirecionar_se_logado(): void {
    if (usuario_logado()) {
        redirecionar('dashboard.php');
    }
}
