/**
 * relatorio_plantao.js
 * Interatividade da tela de Relatório de Plantão
 */

(function () {
    'use strict';

    // -------------------------------------------------------------------------
    // Dados injetados via PHP (window.PLANTAO_DATA)
    // Fallback para array vazio caso a view não injete
    // -------------------------------------------------------------------------
    const RELATORIOS = window.PLANTAO_DATA || {};

    // -------------------------------------------------------------------------
    // Helpers de semáforo
    // -------------------------------------------------------------------------

    /**
     * Avalia os sinais vitais e retorna { status, texto }
     * Os valores já vêm pré-calculados do PHP, mas esta função pode
     * ser usada para recalcular em tempo real se necessário.
     */
    function avaliarSinal(label, valor) {
        const v = parseFloat(valor);
        switch (label.toUpperCase().replace(/[₂²]/g, '2')) {
            case 'PA': {
                const [sistolica, diastolica] = valor.split('/').map(Number);
                if (sistolica >= 140 || diastolica >= 90) return { status: 'critico',  texto: 'Elevada' };
                if (sistolica >= 130 || diastolica >= 85) return { status: 'atencao',  texto: 'Limítrofe' };
                return { status: 'normal', texto: 'Normal' };
            }
            case 'FC':
                if (v > 100 || v < 60) return { status: 'critico', texto: v > 100 ? 'Taquicardia' : 'Bradicardia' };
                if (v > 90)            return { status: 'atencao', texto: 'Taquicardia leve' };
                return { status: 'normal', texto: 'Normal' };
            case 'TEMP':
                if (v >= 38)   return { status: 'critico', texto: 'Febre' };
                if (v >= 37.5) return { status: 'atencao', texto: 'Febrícula' };
                return { status: 'normal', texto: 'Afebril' };
            case 'SPO2':
                if (v < 90) return { status: 'critico', texto: 'Crítico' };
                if (v < 95) return { status: 'critico', texto: 'Atenção' };
                return { status: 'normal', texto: 'Normal' };
            case 'HGT':
                if (v >= 180 || v < 70) return { status: 'critico', texto: 'Elevado' };
                if (v >= 140)           return { status: 'atencao', texto: 'Atenção' };
                return { status: 'normal', texto: 'Normal' };
            default:
                return { status: 'normal', texto: '' };
        }
    }

    // -------------------------------------------------------------------------
    // Renderização de componentes
    // -------------------------------------------------------------------------

    function renderSinaisVitais(sinais) {
        return sinais.map(s => `
            <div class="sinal-card sinal-card--${s.status}">
                <div class="sinal-card__label">${s.label}</div>
                <div class="sinal-card__valor">${s.valor}</div>
                <span class="sinal-card__unidade">${s.unidade}</span>
                <div class="sinal-card__status">${s.texto}</div>
            </div>
        `).join('');
    }

    function renderMedicacoes(meds) {
        const rows = meds.map(m => `
            <tr>
                <td>${escHtml(m.nome)}</td>
                <td style="color:var(--text-secondary)">${escHtml(m.via)}</td>
                <td style="color:var(--text-secondary)">${escHtml(m.horario)}</td>
                <td>
                    <span class="med-status med-status--${m.status}">
                        <span class="med-status__dot"></span>
                        ${m.status === 'administrado' ? 'Administrado' : 'Pendente'}
                    </span>
                </td>
            </tr>
        `).join('');

        return `
            <table class="med-table">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Via</th>
                        <th>Horário</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `;
    }

    function renderIntercorrencias(lista) {
        if (!lista.length) {
            return `<div class="no-intercorrencias">
                        <span>✓</span> Nenhuma intercorrência neste turno
                    </div>`;
        }
        return lista.map(i => `
            <div class="intercorrencia-item">
                <div class="intercorrencia-icon">⚠</div>
                <div class="intercorrencia-body">
                    <div class="intercorrencia-desc">${escHtml(i.descricao)}</div>
                    <div class="intercorrencia-time">🕐 ${escHtml(i.horario)}</div>
                </div>
            </div>
        `).join('');
    }

    function renderRodape(rel) {
        const iniciais = rel.enfermeiro.split(' ').slice(0, 2).map(p => p[0]).join('');
        const acaoHtml = rel.assinado
            ? `<span class="badge-assinado">✓ Relatório assinado</span>`
            : `<button class="btn-assinar" data-turno="${rel.turno}" id="btn-assinar">Assinar relatório</button>`;

        return `
            <div class="rp-footer">
                <div class="rp-footer__person">
                    <div class="rp-footer__avatar">${escHtml(iniciais)}</div>
                    <div>
                        <div class="rp-footer__name">${escHtml(rel.enfermeiro)}</div>
                        <div class="rp-footer__coren">${escHtml(rel.coren)}</div>
                    </div>
                </div>
                ${acaoHtml}
            </div>
        `;
    }

    // -------------------------------------------------------------------------
    // Carregamento do turno selecionado
    // -------------------------------------------------------------------------

    function carregarTurno(turnoKey) {
        const rel = RELATORIOS[turnoKey];
        if (!rel) return;

        const conteudo = document.getElementById('rp-conteudo');
        if (!conteudo) return;

        // Sinais vitais
        const sinaisEl = document.getElementById('rp-sinais-vitais');
        if (sinaisEl) sinaisEl.innerHTML = renderSinaisVitais(rel.sinais_vitais);

        // Medicações
        const medEl = document.getElementById('rp-medicacoes');
        if (medEl) medEl.innerHTML = renderMedicacoes(rel.medicacoes);

        // Evolução
        const evolEl = document.getElementById('rp-evolucao-text');
        if (evolEl) evolEl.textContent = rel.evolucao;

        // Intercorrências
        const interEl = document.getElementById('rp-intercorrencias-lista');
        if (interEl) interEl.innerHTML = renderIntercorrencias(rel.intercorrencias);

        // Rodapé
        const footerEl = document.getElementById('rp-footer-area');
        if (footerEl) {
            footerEl.innerHTML = renderRodape(rel);
            // Botão de assinatura
            const btnAssinar = document.getElementById('btn-assinar');
            if (btnAssinar) {
                btnAssinar.addEventListener('click', function () {
                    assinarRelatorio(turnoKey, this);
                });
            }
        }

        conteudo.classList.add('visible');
    }

    // -------------------------------------------------------------------------
    // Assinatura
    // -------------------------------------------------------------------------

    function assinarRelatorio(turnoKey, btn) {
        // Feedback imediato
        btn.disabled = true;
        btn.textContent = 'Assinando...';

        // Em produção: substituir por fetch('/relatorio-plantao/assinar', ...)
        setTimeout(function () {
            RELATORIOS[turnoKey].assinado = true;
            const footerEl = document.getElementById('rp-footer-area');
            if (footerEl) footerEl.innerHTML = renderRodape(RELATORIOS[turnoKey]);

            // Atualiza badge do card de turno
            const badge = document.querySelector(`.turno-card[data-turno="${turnoKey}"] .turno-card__badge`);
            if (badge && RELATORIOS[turnoKey].status === 'andamento') {
                // opcional: não altera status do turno, apenas mostra confirmação
            }
        }, 600);
    }

    // -------------------------------------------------------------------------
    // Navegação de data
    // -------------------------------------------------------------------------

    function initDateNav() {
        let dataAtual = new Date();

        const labelEl = document.getElementById('rp-data-label');
        function renderData() {
            if (!labelEl) return;
            labelEl.textContent = dataAtual.toLocaleDateString('pt-BR', {
                weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
            });
        }
        renderData();

        document.getElementById('rp-data-prev')?.addEventListener('click', function () {
            dataAtual.setDate(dataAtual.getDate() - 1);
            renderData();
        });
        document.getElementById('rp-data-next')?.addEventListener('click', function () {
            dataAtual.setDate(dataAtual.getDate() + 1);
            renderData();
        });
    }

    // -------------------------------------------------------------------------
    // Seleção de turno
    // -------------------------------------------------------------------------

    function initTurnoCards() {
        const cards = document.querySelectorAll('.turno-card');
        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                cards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                carregarTurno(this.dataset.turno);
            });
        });

        // Seleciona o turno ativo automaticamente
        const ativo = document.querySelector('.turno-card[data-auto-select="true"]');
        if (ativo) {
            ativo.classList.add('selected');
            carregarTurno(ativo.dataset.turno);
        }
    }

    // -------------------------------------------------------------------------
    // Init
    // -------------------------------------------------------------------------

    function init() {
        initDateNav();
        initTurnoCards();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // -------------------------------------------------------------------------
    // Utils
    // -------------------------------------------------------------------------

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

})();
