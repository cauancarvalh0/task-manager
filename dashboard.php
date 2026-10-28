<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

exigir_login();

$userId = (int) $_SESSION['usuario_id'];

// Filtro por status (whitelist para evitar valores inesperados)
$filtro = $_GET['filtro'] ?? 'todas';
if (!in_array($filtro, ['todas', 'pendente', 'concluida'], true)) {
    $filtro = 'todas';
}

$tarefas = [];
$totais = ['pendente' => 0, 'concluida' => 0];
$erro = null;

try {
    // Toda consulta é filtrada por user_id: o usuário só enxerga as próprias tarefas
    $sql = 'SELECT id, titulo, descricao, prioridade, status, data_criacao
              FROM tarefas
             WHERE user_id = :uid';
    $params = [':uid' => $userId];

    if ($filtro !== 'todas') {
        $sql .= ' AND status = :status';
        $params[':status'] = $filtro;
    }

    $sql .= " ORDER BY status ASC, FIELD(prioridade, 'alta', 'media', 'baixa'), data_criacao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tarefas = $stmt->fetchAll();

    // Contadores do resumo (independem do filtro aplicado)
    $stmt = $pdo->prepare('SELECT status, COUNT(*) AS total FROM tarefas WHERE user_id = :uid GROUP BY status');
    $stmt->execute([':uid' => $userId]);
    foreach ($stmt->fetchAll() as $linha) {
        $totais[$linha['status']] = (int) $linha['total'];
    }
} catch (PDOException $e) {
    error_log('Erro ao listar tarefas: ' . $e->getMessage());
    $erro = 'Não foi possível carregar suas tarefas.';
}

$total = $totais['pendente'] + $totais['concluida'];
$progresso = $total > 0 ? (int) round($totais['concluida'] / $total * 100) : 0;
$primeiroNome = explode(' ', trim($_SESSION['usuario_nome']))[0];

$prioridades = [
    'alta'  => ['Alta',  'pill--alta'],
    'media' => ['Média', 'pill--media'],
    'baixa' => ['Baixa', 'pill--baixa'],
];

$filtros = [
    'todas'     => ['Todas',      $total,                 'dashboard.php'],
    'pendente'  => ['Pendentes',  $totais['pendente'],    'dashboard.php?filtro=pendente'],
    'concluida' => ['Concluídas', $totais['concluida'],   'dashboard.php?filtro=concluida'],
];

$tituloPagina = 'Painel';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1 class="page-title">Olá, <?= e($primeiroNome) ?></h1>
        <p class="page-sub">Aqui está o resumo das suas tarefas.</p>
    </div>
</div>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= e($erro) ?></div>
<?php endif; ?>

<!-- Resumo -->
<section class="row g-3 mb-4" aria-label="Resumo">
    <div class="col-12 col-md-4">
        <div class="stat">
            <div class="stat__icon"><i class="bi bi-list-task"></i></div>
            <div>
                <div class="stat__label">Total de tarefas</div>
                <div class="stat__value"><?= $total ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat">
            <div class="stat__icon stat__icon--warn"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat__label">Pendentes</div>
                <div class="stat__value"><?= $totais['pendente'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat">
            <div class="stat__icon stat__icon--ok"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div class="stat__label">Concluídas</div>
                <div class="stat__value"><?= $totais['concluida'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="progress-card">
            <div class="d-flex justify-content-between small mb-2">
                <span class="fw-semibold">Progresso geral</span>
                <span class="text-muted"><?= $progresso ?>% concluído</span>
            </div>
            <div class="progress" role="progressbar" aria-label="Progresso geral"
                 aria-valuenow="<?= $progresso ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: <?= $progresso ?>%"></div>
            </div>
        </div>
    </div>
</section>

<div class="row g-4 align-items-start">
    <!-- Formulário de nova tarefa -->
    <div class="col-12 col-lg-4">
        <section class="panel sticky-lg-top">
            <div class="panel__head">
                <h2 class="panel__title"><i class="bi bi-plus-circle"></i> Nova tarefa</h2>
            </div>
            <div class="panel__body">
                <form action="tasks/create.php" method="POST" class="needs-validation" novalidate>
                    <?= csrf_campo() ?>

                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo"
                               maxlength="150" placeholder="Ex.: Enviar proposta ao cliente" required>
                        <div class="invalid-feedback">Informe o título da tarefa.</div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição <span class="text-muted fw-normal">(opcional)</span></label>
                        <textarea class="form-control" id="descricao" name="descricao"
                                  rows="3" maxlength="1000" placeholder="Detalhes, links, observações..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="prioridade" class="form-label">Prioridade</label>
                        <select class="form-select" id="prioridade" name="prioridade">
                            <option value="baixa">Baixa</option>
                            <option value="media" selected>Média</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Adicionar tarefa</button>
                </form>
            </div>
        </section>
    </div>

    <!-- Lista de tarefas -->
    <div class="col-12 col-lg-8">
        <section class="panel">
            <div class="panel__head panel__head--split">
                <h2 class="panel__title"><i class="bi bi-card-checklist"></i> Minhas tarefas</h2>

                <nav class="segmented" aria-label="Filtrar tarefas">
                    <?php foreach ($filtros as $chave => [$rotulo, $qtd, $url]): ?>
                        <a href="<?= e($url) ?>" class="<?= $filtro === $chave ? 'is-active' : '' ?>"
                           <?= $filtro === $chave ? 'aria-current="page"' : '' ?>>
                            <?= e($rotulo) ?> <span class="segmented__count"><?= (int) $qtd ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <?php if (empty($tarefas)): ?>
                <div class="empty">
                    <div class="empty__icon"><i class="bi bi-inbox"></i></div>
                    <p class="fw-semibold mb-1">Nenhuma tarefa por aqui</p>
                    <p class="text-muted small mb-0">
                        <?= $filtro === 'todas' ? 'Use o formulário ao lado para criar a primeira.' : 'Nenhuma tarefa neste filtro.' ?>
                    </p>
                </div>
            <?php else: ?>
                <ul class="task-list">
                    <?php foreach ($tarefas as $t): ?>
                        <?php
                            $concluida = $t['status'] === 'concluida';
                            [$rotulo, $classePill] = $prioridades[$t['prioridade']];
                        ?>
                        <li class="task <?= $concluida ? 'is-done' : '' ?>">
                            <!-- Alternar entre pendente e concluída -->
                            <form action="tasks/toggle.php" method="POST" class="m-0">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                <button type="submit" class="task__check"
                                        aria-label="<?= $concluida ? 'Reabrir tarefa' : 'Concluir tarefa' ?>"
                                        title="<?= $concluida ? 'Reabrir' : 'Concluir' ?>">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>

                            <div class="task__body">
                                <div class="task__title"><?= e($t['titulo']) ?></div>
                                <?php if (!empty($t['descricao'])): ?>
                                    <div class="task__desc"><?= nl2br(e($t['descricao'])) ?></div>
                                <?php endif; ?>
                                <div class="task__meta">
                                    <span class="pill <?= $classePill ?>"><i class="bi bi-flag-fill"></i> <?= $rotulo ?></span>
                                    <span class="task__date"><i class="bi bi-calendar3"></i> <?= date('d/m/Y H:i', strtotime($t['data_criacao'])) ?></span>
                                </div>
                            </div>

                            <div class="task__actions">
                                <a href="tasks/edit.php?id=<?= (int) $t['id'] ?>" class="icon-btn"
                                   aria-label="Editar tarefa" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="tasks/delete.php" method="POST" class="m-0"
                                      data-confirmar="Excluir a tarefa “<?= e($t['titulo']) ?>”? Essa ação não pode ser desfeita.">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                    <button type="submit" class="icon-btn icon-btn--danger"
                                            aria-label="Excluir tarefa" title="Excluir">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
