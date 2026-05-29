/**
 * public/assets/js/relatorio_plantao_paciente.js
 * Accordion dos relatórios do paciente.
 */

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.rp-card-header').forEach(function (header) {
        header.addEventListener('click', function (event) {
            event.preventDefault();

            const card = header.closest('.rp-card');
            if (!card) return;

            const estavaAberto = card.classList.contains('expanded');

            document.querySelectorAll('.rp-card.expanded').forEach(function (item) {
                item.classList.remove('expanded');

                const btn = item.querySelector('.rp-card-header');
                if (btn) {
                    btn.setAttribute('aria-expanded', 'false');
                }
            });

            if (!estavaAberto) {
                card.classList.add('expanded');
                header.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.querySelectorAll('.stop-propagation').forEach(function (element) {
        element.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });

    document.querySelectorAll('input[type="datetime-local"]').forEach(function (input) {
        const value = input.value;
        if (value && value.indexOf(' ') !== -1 && value.indexOf('T') === -1) {
            input.value = value.replace(' ', 'T');
        }
    });

    document.querySelectorAll('textarea.rp-textarea').forEach(function (textarea) {
        const resize = function () {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        };
        textarea.addEventListener('input', resize);
        resize();
    });
});
