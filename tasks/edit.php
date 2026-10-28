<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

exigir_login('../');

$userId = (int) $_SESSION['usuario_id'];
$erro = null;

// Aceita o ID tanto via GET (abrir formulário) quanto via POST (salvar)
$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$id) {
    redirecionar('../dashboard.php');
}

try {
    // Busca a tarefa garantindo que pertence ao usuário logado
    $stmt = $pdo->prepare('SELECT id, titulo, descricao, prioridade FROM tarefas WHERE id = :id AND user_id = :uid');
    $stmt->execute([':id' => $id, ':uid' => $userId]);
    $tarefa = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Erro ao buscar tarefa: ' . $e->getMessage());
    $tarefa = false;
}

if (!$tarefa) {
    definir_flash('danger', 'Tarefa não encontrada.');
    redirecionar('../dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $titulo     = trim($_POST['titulo'] ?? '');
    $descricao  = trim($_POST['descricao'] ?? '');
    $prioridade = $_POST['prioridade'] ?? 'media';

    if ($titulo === '' || mb_strlen($titulo) > 150 || mb_strlen($descricao) > 1000) {
        $erro = 'Verifique o título (até 150) e a descrição (até 1000 caracteres).';
    } elseif (!in_array($prioridade, ['baixa', 'media', 'alta'], true)) {
        $erro = 'Prioridade inválida.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'UPDATE tarefas
                    SET titulo = :titulo, descricao = :descricao, prioridade = :prioridade
                  WHERE id = :id AND user_id = :uid'
            );
            $stmt->execute([
                ':titulo'     => $titulo,
                ':descricao'  => $descricao !== '' ? $descricao : null,
                ':prioridade' => $prioridade,
                ':id'         => $id,
                ':uid'        => $userId,
            ]);
            definir_flash('success', 'Tarefa atualizada!');
            redirecionar('../dashboard.php');
        } catch (PDOException $e) {
            error_log('Erro ao editar tarefa: ' . $e->getMessage());
            $erro = 'Erro ao salvar as alterações.';
        }
    }

    // Em caso de erro, mantém o que o usuário digitou
    $tarefa['titulo'] = $titulo;
    $tarefa['descricao'] = $descricao;
    $tarefa['prioridade'] = $prioridade;
}

$base = '../';
$tituloPagina = 'Editar tarefa';
require __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-9 col-lg-6">
        <a href="../dashboard.php" class="back-link"><i class="bi bi-arrow-left"></i> Voltar ao painel</a>

        <section class="panel">
            <div class="panel__head">
                <h1 class="panel__title"><i class="bi bi-pencil-square"></i> Editar tarefa</h1>
            </div>
            <div class="panel__body">

                <?php if ($erro): ?>
                    <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                        <div><?= e($erro) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int) $tarefa['id'] ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo"
                               maxlength="150" required value="<?= e($tarefa['titulo']) ?>">
                        <div class="invalid-feedback">Informe o título da tarefa.</div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao"
                                  rows="4" maxlength="1000"><?= e($tarefa['descricao']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="prioridade" class="form-label">Prioridade</label>
                        <select class="form-select" id="prioridade" name="prioridade">
                            <?php foreach (['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'] as $valor => $rotulo): ?>
                                <option value="<?= $valor ?>" <?= $tarefa['prioridade'] === $valor ? 'selected' : '' ?>>
                                    <?= $rotulo ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="../dashboard.php" class="btn btn-outline-dark">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
