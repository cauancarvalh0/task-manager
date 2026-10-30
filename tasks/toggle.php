<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigir_login('../');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../dashboard.php');
}
csrf_validar();

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    try {
        // Troca pendente por concluída (e vice-versa).
        // O user_id garante que só o dono altera a própria tarefa.
        $stmt = $pdo->prepare(
            "UPDATE tarefas
                SET status = CASE WHEN status = 'pendente' THEN 'concluida' ELSE 'pendente' END
              WHERE id = :id AND user_id = :uid"
        );
        $stmt->execute([':id' => $id, ':uid' => $_SESSION['usuario_id']]);
    } catch (PDOException $e) {
        error_log('Erro ao alternar status: ' . $e->getMessage());
        definir_flash('danger', 'Erro ao atualizar a tarefa.');
    }
}

redirecionar('../dashboard.php');
