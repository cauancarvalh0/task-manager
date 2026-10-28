<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigir_login('../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../dashboard.php');
}
csrf_validar();

$titulo     = trim($_POST['titulo'] ?? '');
$descricao  = trim($_POST['descricao'] ?? '');
$prioridade = $_POST['prioridade'] ?? 'media';

// Validações no servidor
if ($titulo === '' || mb_strlen($titulo) > 150 || mb_strlen($descricao) > 1000) {
    definir_flash('danger', 'Dados inválidos. Verifique o título e a descrição.');
    redirecionar('../dashboard.php');
}
if (!in_array($prioridade, ['baixa', 'media', 'alta'], true)) {
    $prioridade = 'media';
}

try {
    // O user_id vem da sessão, nunca do formulário
    $stmt = $pdo->prepare(
        'INSERT INTO tarefas (user_id, titulo, descricao, prioridade)
         VALUES (:uid, :titulo, :descricao, :prioridade)'
    );
    $stmt->execute([
        ':uid'        => $_SESSION['usuario_id'],
        ':titulo'     => $titulo,
        ':descricao'  => $descricao !== '' ? $descricao : null,
        ':prioridade' => $prioridade,
    ]);
    definir_flash('success', 'Tarefa adicionada!');
} catch (PDOException $e) {
    error_log('Erro ao adicionar tarefa: ' . $e->getMessage());
    definir_flash('danger', 'Erro ao adicionar a tarefa.');
}

redirecionar('../dashboard.php');
