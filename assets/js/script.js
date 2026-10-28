/**
 * Melhorias de UX e validação no lado do cliente (JavaScript puro).
 * Importante: a validação real continua no servidor (PHP).
 */
document.addEventListener('DOMContentLoaded', () => {

    // 1) Validação nativa do Bootstrap: bloqueia o envio de formulários inválidos
    document.querySelectorAll('.needs-validation').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            if (!form.checkValidity()) {
                evento.preventDefault();
                evento.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // 2) Confirmação de senha: os dois campos precisam ser iguais
    const senha = document.getElementById('senha');
    const confirmar = document.getElementById('confirmar_senha');

    if (senha && confirmar) {
        const verificar = () => {
            confirmar.setCustomValidity(
                confirmar.value !== senha.value ? 'As senhas não coincidem.' : ''
            );
        };
        senha.addEventListener('input', verificar);
        confirmar.addEventListener('input', verificar);
    }

    // 3) Botão para mostrar/ocultar senha (troca o ícone do olho)
    document.querySelectorAll('[data-toggle-senha]').forEach((botao) => {
        botao.addEventListener('click', () => {
            const campo = document.querySelector(botao.dataset.toggleSenha);
            if (!campo) return;

            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            botao.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');

            const icone = botao.querySelector('i');
            if (icone) {
                icone.className = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
            }
        });
    });

    // 4) Confirmação antes de ações destrutivas (modal do Bootstrap)
    const modalEl = document.getElementById('modalConfirmar');
    const temModal = modalEl && window.bootstrap;
    const modal = temModal ? new bootstrap.Modal(modalEl) : null;
    let formPendente = null;

    document.querySelectorAll('form[data-confirmar]').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            evento.preventDefault();

            if (!modal) {
                // Plano B: se o Bootstrap não carregar, usa a confirmação do navegador
                if (window.confirm(form.dataset.confirmar)) form.submit();
                return;
            }

            formPendente = form;
            modalEl.querySelector('[data-modal-texto]').textContent = form.dataset.confirmar;
            modal.show();
        });
    });

    if (modal) {
        modalEl.querySelector('[data-modal-confirmar]').addEventListener('click', () => {
            if (formPendente) formPendente.submit();
        });
    }

    // 5) Mensagens de aviso somem sozinhas depois de alguns segundos
    if (window.bootstrap) {
        document.querySelectorAll('[data-auto-fechar]').forEach((alerta) => {
            setTimeout(() => {
                bootstrap.Alert.getOrCreateInstance(alerta).close();
            }, 6000);
        });
    }
});
