<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigir_login('../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../dashboard.php');
}
csrf_validar();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id) {
    try {
        // Exclusão restrita ao dono da tarefa (id + user_id)
        $stmt = $pdo->prepare('DELETE FROM tarefas WHERE id = :id AND user_id = :uid');
        $stmt->execute([':id' => $id, ':uid' => $_SESSION['usuario_id']]);

        if ($stmt->rowCount() > 0) {
            definir_flash('success', 'Tarefa excluída.');
        }
    } catch (PDOException $e) {
        error_log('Erro ao excluir tarefa: ' . $e->getMessage());
        definir_flash('danger', 'Erro ao excluir a tarefa.');
    }
}

redirecionar('../dashboard.php');
