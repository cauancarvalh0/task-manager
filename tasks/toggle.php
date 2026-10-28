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
        // A cláusula user_id garante que só o dono altere a própria tarefa
        $stmt = $pdo->prepare(
            "UPDATE tarefas
                SET status = IF(status = 'pendente', 'concluida', 'pendente')
              WHERE id = :id AND user_id = :uid"
        );
        $stmt->execute([':id' => $id, ':uid' => $_SESSION['usuario_id']]);
    } catch (PDOException $e) {
        error_log('Erro ao alternar status: ' . $e->getMessage());
        definir_flash('danger', 'Erro ao atualizar a tarefa.');
    }
}

redirecionar('../dashboard.php');
