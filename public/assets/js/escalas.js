(function () {
  const BASE_URL = window.BASE_URL || '';

  const qs = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  function buildUrl() {
    const url = new URL(BASE_URL + '/escala', window.location.origin);
    const paciente = qs('#filtro-paciente')?.value || '';
    const cuidador = qs('#filtro-colaborador')?.value || '';
    const semana = qs('#filtro-semana')?.value || '';

    if (paciente) url.searchParams.set('paciente_uuid', paciente);
    if (cuidador) url.searchParams.set('colaborador_uuid', cuidador);
    if (semana) url.searchParams.set('semana', semana);

    return url.toString();
  }

  ['#filtro-paciente', '#filtro-colaborador', '#filtro-semana'].forEach((sel) => {
    qs(sel)?.addEventListener('change', () => {
      window.location.href = buildUrl();
    });
  });

  function openModal() {
    const modal = qs('#modal-escala');
    if (modal) modal.style.display = 'flex';
  }

  function closeModals() {
    qsa('.modal-overlay').forEach((m) => (m.style.display = 'none'));
  }

  qsa('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeModals));
  qsa('[data-modal-escala]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const form = qs('#form-escala');
      form?.reset();
      qs('#modal_escala_id') && (qs('#modal_escala_id').value = '');
      qs('#grupo-horario-custom') && (qs('#grupo-horario-custom').style.display = 'none');
      openModal();
    });
  });

  function preencherModalPorCelula(cell) {
    qs('#modal_escala_id') && (qs('#modal_escala_id').value = cell.dataset.escalaId || '');
    qs('#modal_paciente_id') && (qs('#modal_paciente_id').value = cell.dataset.pacienteUuid || '');
    qs('#modal_colaborador_id') && (qs('#modal_colaborador_id').value = cell.dataset.colaboradorUuid || '');
    qs('#modal_data_plantao') && (qs('#modal_data_plantao').value = cell.dataset.dataPlantao || '');

    const turno = cell.dataset.turno || 'diurno';
    const turnoEl = qs('#modal_turno');
    if (turnoEl) turnoEl.value = turno;

    const custom = qs('#grupo-horario-custom');
    if (custom) custom.style.display = turno === 'personalizado' ? 'grid' : 'none';

    if (turno === 'personalizado') {
      qs('#modal_inicio') && (qs('#modal_inicio').value = cell.dataset.inicio || '08:00');
      qs('#modal_fim') && (qs('#modal_fim').value = cell.dataset.fim || '16:00');
    }

    openModal();
  }

  qsa('.plantao-cell[role="button"]').forEach((cell) => {
    cell.addEventListener('click', (event) => {
      const action = event.target.closest('[data-action]');
      if (action) return;
      preencherModalPorCelula(cell);
    });

    cell.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        preencherModalPorCelula(cell);
      }
    });
  });

  qsa('[data-action="editar"]').forEach((btn) => {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const cell = btn.closest('.plantao-cell');
      if (cell) preencherModalPorCelula(cell);
    });
  });



  function openSubModal(cell) {
    const modal = qs('#modal-substituicao');
    if (!modal || !cell) return;

    const escalaId = cell.dataset.escalaId || '';
    if (!escalaId) {
      alert('Salve o plantão antes de registrar uma substituição.');
      return;
    }

    qs('#form-substituicao')?.reset();
    qs('#sub_escala_id') && (qs('#sub_escala_id').value = escalaId);
    qs('#sub_colaborador_id') && (qs('#sub_colaborador_id').value = cell.dataset.colaboradorId || '');
    qs('#sub_data') && (qs('#sub_data').value = cell.dataset.dataPlantao || '');

    modal.hidden = false;
    modal.style.display = 'flex';
  }

  qsa('[data-action="substituir"]').forEach((btn) => {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const cell = btn.closest('.plantao-cell');
      openSubModal(cell);
    });
  });

  qsa('[data-action="excluir"]').forEach((btn) => {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const escalaId = btn.dataset.escalaId || btn.closest('.plantao-cell')?.dataset.escalaId || '';
      if (!escalaId) return;
      if (!confirm('Remover este plantão da escala?')) return;

      const form = document.createElement('form');
      form.method = 'POST';
      form.action = BASE_URL + '/escala/excluir';
      form.innerHTML = `
        <input type="hidden" name="_csrf" value="${document.querySelector('input[name="_csrf"]')?.value || ''}">
        <input type="hidden" name="escala_id" value="${escalaId}">
      `;
      document.body.appendChild(form);
      form.submit();
    });
  });

  qs('#modal_turno')?.addEventListener('change', function () {
    const g = qs('#grupo-horario-custom');
    if (g) g.style.display = this.value === 'personalizado' ? 'grid' : 'none';
  });
})();
