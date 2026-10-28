<?php
$base   = $base ?? '';
$layout = $layout ?? 'app';
$versaoJs = @filemtime(__DIR__ . '/../assets/js/validation.js') ?: 1;
?>
<?php if ($layout === 'auth'): ?>
        </div><!-- /.auth-box -->
    </main>
</div><!-- /.auth-shell -->
<?php else: ?>
</main>

<footer class="app-footer">
    <div class="container">
        Task Manager &middot; PHP, MySQL e Bootstrap 5
    </div>
</footer>

<!-- Modal de confirmação (exclusão de tarefas) -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="modalConfirmarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">
                <div class="modal-icon"><i class="bi bi-trash3"></i></div>
                <h2 class="h5 mb-2" id="modalConfirmarTitulo">Confirmar exclusão</h2>
                <p class="text-muted mb-4" data-modal-texto></p>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" data-modal-confirmar>Excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e($base) ?>assets/js/validation.js?v=<?= (int) $versaoJs ?>"></script>
</body>
</html>
