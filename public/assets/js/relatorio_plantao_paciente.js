/**
 * public/assets/js/relatorio_plantao_paciente.js
 *
 * Funcionalidades:
 * - Expandir e recolher cards de plantão
 * - Duplo clique no card para abrir visualização completa
 * - Impedir conflito de clique nos botões internos
 * - Compatível com:
 *   - paciente.php
 *   - card.php
 *   - show.php
 *   - form.php
 */

document.addEventListener('DOMContentLoaded', function () {
    // =====================================================
    // Toggle dos cards (expandir/recolher)
    // =====================================================
    document.querySelectorAll('.rp-card-header').forEach(function (header) {
        header.addEventListener('click', function (event) {
            event.preventDefault();

            const card = header.closest('.rp-card');
            if (!card) return;

            card.classList.toggle('expanded');
        });
    });

    // =====================================================
    // Duplo clique no card abre visualização completa
    // (rota definida em data-url)
    // =====================================================
    document.querySelectorAll('.rp-card[data-url]').forEach(function (card) {
        card.addEventListener('dblclick', function () {
            const url = card.getAttribute('data-url');

            if (url) {
                window.location.href = url;
            }
        });
    });

    // =====================================================
    // Impede propagação em links e botões internos
    // (Visualizar / Editar)
    // =====================================================
    document.querySelectorAll('.stop-propagation').forEach(function (element) {
        element.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });

    // =====================================================
    // Inputs datetime-local
    // Ajusta valores inválidos sem "T"
    // =====================================================
    document.querySelectorAll('input[type="datetime-local"]').forEach(function (input) {
        const value = input.value;

        if (
            value &&
            value.indexOf(' ') !== -1 &&
            value.indexOf('T') === -1
        ) {
            input.value = value.replace(' ', 'T');
        }
    });

    // =====================================================
    // Auto-resize de textareas
    // =====================================================
    document.querySelectorAll('textarea.rp-textarea').forEach(function (textarea) {
        const resize = function () {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        };

        textarea.addEventListener('input', resize);
        resize();
    });

    // =====================================================
    // Confirma disponibilidade de dados globais
    // =====================================================
    if (window.RELATORIO_DATA) {
        console.debug('Relatório de Plantão:', window.RELATORIO_DATA);
    }
});