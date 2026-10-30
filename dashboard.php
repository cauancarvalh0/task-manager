<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

exigir_login();

$userId = (int) $_SESSION['usuario_id'];

// Filtro por status: só aceita os valores permitidos
$filtro = $_GET['filtro'] ?? 'todas';
if (!in_array($filtro, ['todas', 'pendente', 'concluida'], true)) {
    $filtro = 'todas';
}

$tarefas = [];
$totais = ['pendente' => 0, 'concluida' => 0];
$erro = null;

try {
    // Toda consulta filtra por user_id: cada usuário só vê as próprias tarefas
    $sql = 'SELECT id, titulo, descricao, prioridade, status, data_criacao
              FROM tarefas
             WHERE user_id = :uid';
    $params = [':uid' => $userId];

    if ($filtro !== 'todas') {
        $sql .= ' AND status = :status';
        $params[':status'] = $filtro;
    }

    // Pendentes primeiro; dentro de cada grupo: alta, média e baixa; depois a mais recente
    $sql .= " ORDER BY CASE status WHEN 'pendente' THEN 1 ELSE 2 END,
                       CASE prioridade WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END,
                       data_criacao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tarefas = $stmt->fetchAll();

    // Quantas tarefas existem em cada status (para os cartões do topo)
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
$progresso = $total > 0 ? round($totais['concluida'] / $total * 100) : 0;
$primeiroNome = explode(' ', trim($_SESSION['usuario_nome']))[0];

// Texto e cor do selo de cada prioridade
$rotulos = ['alta' => 'Alta', 'media' => 'Média', 'baixa' => 'Baixa'];

$tituloPagina = 'Painel';
$heroTitulo   = 'Olá, ' . $primeiroNome;
$heroSub      = 'Aqui está o resumo das suas tarefas.';
require __DIR__ . '/includes/header.php';
?>

<?php if ($erro): ?>
    <div class="alert alert-danger"><?= e($erro) ?></div>
<?php endif; ?>

<!-- Cartões de resumo -->
<section class="row g-3 mb-4 sobe" aria-label="Resumo">
    <div class="col-6 col-lg-3">
        <div class="card estatistica">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="estatistica-rotulo">Total</div>
                        <div class="estatistica-valor"><?= $total ?></div>
                    </div>
                    <span class="icone-estatistica fundo-azul"><i class="bi bi-list-task"></i></span>
                </div>
                <p class="estatistica-nota">Todas as tarefas</p>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card estatistica">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="estatistica-rotulo">Pendentes</div>
                        <div class="estatistica-valor"><?= $totais['pendente'] ?></div>
                    </div>
                    <span class="icone-estatistica fundo-laranja"><i class="bi bi-hourglass-split"></i></span>
                </div>
                <p class="estatistica-nota">Aguardando conclusão</p>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card estatistica">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="estatistica-rotulo">Concluídas</div>
                        <div class="estatistica-valor"><?= $totais['concluida'] ?></div>
                    </div>
                    <span class="icone-estatistica fundo-verde"><i class="bi bi-check2-circle"></i></span>
                </div>
                <p class="estatistica-nota">Já finalizadas</p>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card estatistica">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="estatistica-rotulo">Progresso</div>
                        <div class="estatistica-valor"><?= $progresso ?>%</div>
                    </div>
                    <span class="icone-estatistica fundo-ciano"><i class="bi bi-graph-up-arrow"></i></span>
                </div>
                <div class="progress mt-3" role="progressbar" aria-label="Progresso geral"
                     aria-valuenow="<?= $progresso ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" style="width: <?= $progresso ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="row g-4 align-items-start">

    <!-- Formulário de nova tarefa -->
    <div class="col-12 col-lg-4 order-lg-2">
        <div class="card">
            <div class="card-header">
                <h2 class="h6 mb-0"><i class="bi bi-plus-circle me-2"></i>Nova tarefa</h2>
            </div>
            <div class="card-body">
                <form action="tasks/create.php" method="POST" class="needs-validation" novalidate>
                    <?= csrf_campo() ?>

                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo"
                               maxlength="150" placeholder="Ex.: Enviar proposta ao cliente" required>
                        <div class="invalid-feedback">Informe o título da tarefa.</div>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">
                            Descrição <span class="text-muted fw-normal">(opcional)</span>
                        </label>
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
        </div>
    </div>

    <!-- Lista de tarefas -->
    <div class="col-12 col-lg-8 order-lg-1">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h2 class="h6 mb-0"><i class="bi bi-card-checklist me-2"></i>Minhas tarefas</h2>

                <!-- Filtros (pílulas do Bootstrap) -->
                <ul class="nav nav-pills filtros">
                    <li class="nav-item">
                        <a class="nav-link <?= $filtro === 'todas' ? 'active' : '' ?>" href="dashboard.php">
                            Todas <span class="contador"><?= $total ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $filtro === 'pendente' ? 'active' : '' ?>" href="dashboard.php?filtro=pendente">
                            Pendentes <span class="contador"><?= $totais['pendente'] ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $filtro === 'concluida' ? 'active' : '' ?>" href="dashboard.php?filtro=concluida">
                            Concluídas <span class="contador"><?= $totais['concluida'] ?></span>
                        </a>
                    </li>
                </ul>
            </div>

            <?php if (empty($tarefas)): ?>
                <div class="vazio">
                    <i class="bi bi-inbox"></i>
                    <p class="fw-semibold mb-1">Nenhuma tarefa por aqui</p>
                    <p class="text-muted small mb-0">
                        <?= $filtro === 'todas' ? 'Use o formulário para criar a primeira.' : 'Nenhuma tarefa neste filtro.' ?>
                    </p>
                </div>
            <?php else: ?>
                <ul class="lista-tarefas">
                    <?php foreach ($tarefas as $t): ?>
                        <?php $concluida = $t['status'] === 'concluida'; ?>
                        <li class="tarefa <?= $concluida ? 'tarefa-concluida' : '' ?>">

                            <!-- Botão redondo: marca como concluída ou reabre -->
                            <form action="tasks/toggle.php" method="POST" class="m-0">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                <button type="submit" class="tarefa-check"
                                        aria-label="<?= $concluida ? 'Reabrir tarefa' : 'Concluir tarefa' ?>"
                                        title="<?= $concluida ? 'Reabrir' : 'Concluir' ?>">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>

                            <div class="tarefa-corpo">
                                <div class="tarefa-titulo"><?= e($t['titulo']) ?></div>
                                <?php if (!empty($t['descricao'])): ?>
                                    <div class="tarefa-descricao"><?= nl2br(e($t['descricao'])) ?></div>
                                <?php endif; ?>
                                <div class="tarefa-info">
                                    <span class="selo selo-<?= e($t['prioridade']) ?>">
                                        <i class="bi bi-flag-fill"></i> <?= e($rotulos[$t['prioridade']]) ?>
                                    </span>
                                    <span class="selo <?= $concluida ? 'selo-concluida' : 'selo-pendente' ?>">
                                        <?= $concluida ? 'Concluída' : 'Pendente' ?>
                                    </span>
                                    <span class="tarefa-data">
                                        <i class="bi bi-calendar3"></i>
                                        <?= date('d/m/Y H:i', strtotime($t['data_criacao'])) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="tarefa-acoes">
                                <a href="tasks/edit.php?id=<?= (int) $t['id'] ?>" class="botao-icone"
                                   aria-label="Editar tarefa" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <!-- Abre a janela de confirmação (modal do Bootstrap) -->
                                <button type="button" class="botao-icone botao-icone-perigo"
                                        data-bs-toggle="modal" data-bs-target="#modalExcluir<?= (int) $t['id'] ?>"
                                        aria-label="Excluir tarefa" title="Excluir">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Uma janela de confirmação para cada tarefa -->
<?php foreach ($tarefas as $t): ?>
    <div class="modal fade" id="modalExcluir<?= (int) $t['id'] ?>" tabindex="-1"
         aria-labelledby="tituloExcluir<?= (int) $t['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="tasks/delete.php" method="POST">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">

                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="tituloExcluir<?= (int) $t['id'] ?>">Excluir tarefa</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        Deseja excluir a tarefa <strong><?= e($t['titulo']) ?></strong>? Essa ação não pode ser desfeita.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
