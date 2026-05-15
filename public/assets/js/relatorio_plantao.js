/**
 * relatorio_plantao.js
 * JavaScript para funcionalidades do relatório de plantão
 */

function assinarPlantao(plantaoId) {
    if (!plantaoId || plantaoId <= 0) {
        alert('ID do plantão inválido');
        return;
    }

    // Confirmar ação
    if (!confirm('Deseja assinar este plantão? Esta ação não pode ser desfeita.')) {
        return;
    }

    fetch('/relatorio-plantao/assinar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            plantao_id: plantaoId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Plantão assinado com sucesso!');
            location.reload();
        } else {
            alert('Erro ao assinar plantão: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao assinar plantão: ' + error.message);
    });
}

/**
 * Carrega dados de um plantão específico
 * @param {number} plantaoId - ID do plantão a carregar
 */
function carregarPlantao(plantaoId) {
    if (!plantaoId || plantaoId <= 0) {
        alert('ID do plantão inválido');
        return;
    }

    fetch(`/relatorio-plantao/carregar/${plantaoId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            preencherPlantao(data.plantao);
        } else {
            alert('Erro ao carregar plantão: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao carregar plantão: ' + error.message);
    });
}

/**
 * Preenche o formulário com dados do plantão
 * @param {object} plantao - Objeto com dados do plantão
 */
function preencherPlantao(plantao) {
    // Preencher campos do formulário
    document.getElementById('plantao-id').value = plantao.id;
    document.getElementById('plantao-data').value = plantao.data;
    document.getElementById('plantao-periodo').value = plantao.periodo;
    document.getElementById('plantao-enfermeiro').value = plantao.enfermeiro;
    document.getElementById('plantao-evolucao').value = plantao.evolucao || '';
    document.getElementById('plantao-sinais-vitais').value = plantao.sinais_vitais || '';

    // Mostrar modal ou área de edição
    mostrarAreaEdicao(plantao.id);
}

/**
 * Mostra a área de edição do plantão
 * @param {number} plantaoId - ID do plantão a editar
 */
function mostrarAreaEdicao(plantaoId) {
    // Localizar elementos do formulário
    const formulario = document.getElementById('plantao-form');
    const botaoCancelar = document.getElementById('botao-cancelar');

    if (formulario && botaoCancelar) {
        formulario.style.display = 'block';
        botaoCancelar.style.display = 'inline-block';

        // Enviar para rota de edição
        fetch(`/relatorio-plantao/${plantaoId}/editar`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Inserir HTML do formulário de edição
            const container = document.getElementById('conteudo-edicao');
            if (container) {
                container.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar formulário de edição: ' + error.message);
        });
    }
}

/**
 * Salva a evolução do plantão
 */
function salvarEvolucao() {
    const plantaoId = document.getElementById('plantao-id')?.value;
    const evolucao = document.getElementById('plantao-evolucao')?.value;

    if (!plantaoId) {
        alert('ID do plantão não informado');
        return;
    }

    fetch('/relatorio-plantao/salvar-evolucao', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            plantao_id: plantaoId,
            evolucao: evolucao
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Evolução salva com sucesso!');
            location.reload();
        } else {
            alert('Erro ao salvar evolução: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar evolução: ' + error.message);
    });
}

/**
 * Salva os sinais vitais do plantão
 */
function salvarSinaisVitais() {
    const plantaoId = document.getElementById('plantao-id')?.value;
    const sinaisVitais = document.getElementById('plantao-sinais-vitais')?.value;

    if (!plantaoId) {
        alert('ID do plantão não informado');
        return;
    }

    fetch('/relatorio-plantao/salvar-sinais', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            plantao_id: plantaoId,
            sinais_vitais: sinaisVitais
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Sinais vitais salvos com sucesso!');
            location.reload();
        } else {
            alert('Erro ao salvar sinais vitais: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar sinais vitais: ' + error.message);
    });
}

/**
 * Salva uma intercorrência do plantão
 */
function salvarIntercorrencia() {
    const plantaoId = document.getElementById('plantao-id')?.value;
    const intercorrencia = document.getElementById('plantao-intercorrencia')?.value;

    if (!plantaoId) {
        alert('ID do plantão não informado');
        return;
    }

    if (!intercorrencia || intercorrencia.trim() === '') {
        alert('A intercorrência deve ser informada');
        return;
    }

    fetch('/relatorio-plantao/salvar-intercorrencia', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            plantao_id: plantaoId,
            intercorrencia: intercorrencia
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Intercorrência salva com sucesso!');
            location.reload();
        } else {
            alert('Erro ao salvar intercorrência: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar intercorrência: ' + error.message);
    });
}

/**
 * Inicializa a funcionalidade do relatório de plantão
 */
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar event listeners para botões de assinatura
    const botaoAssinar = document.querySelector('.rp-btn--primary');
    if (botaoAssinar) {
        botaoAssinar.addEventListener('click', function() {
            // Buscar ID do plantão a partir do botão ou contexto
            const plantaoId = this.getAttribute('data-plantao-id') || 0;
            if (plantaoId) {
                assinarPlantao(parseInt(plantaoId));
            }
        });
    }

    // Adicionar event listener para formulários de edição
    const formularioSalvar = document.getElementById('plantao-form');
    if (formularioSalvar) {
        formularioSalvar.addEventListener('submit', function(e) {
            e.preventDefault();
            // Determinar qual botão foi clicado baseado no valor do atributo data-action
            const botaoSalvar = document.querySelector('[data-action="salvar"]');
            if (botaoSalvar) {
                const action = botaoSalvar.getAttribute('data-action');
                if (action === 'evolucao') {
                    salvarEvolucao();
                } else if (action === 'sinais') {
                    salvarSinaisVitais();
                } else if (action === 'intercorrencia') {
                    salvarIntercorrencia();
                }
            }
        });
    }
});