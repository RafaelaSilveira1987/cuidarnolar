(function () {
  const BASE_URL = (window.APP_BASE_URL || window.BASE_URL || '').replace(/\/$/, '');

  function qs(selector, ctx = document) {
    return ctx.querySelector(selector);
  }

  function qsa(selector, ctx = document) {
    return Array.from(ctx.querySelectorAll(selector));
  }

  function abrirModal(selector) {
    const modal = qs(selector);
    if (!modal) return;
    modal.hidden = false;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
  }

  function fecharModal(modal) {
    if (!modal) return;
    modal.hidden = true;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
  }

  function setValue(modal, selector, value) {
    const el = qs(selector, modal);
    if (el && value !== undefined && value !== null) el.value = value;
  }

  function atualizarHorarioCustom() {
    const turno = qs('#modal_turno');
    const grupo = qs('#grupo-horario-custom');
    if (!turno || !grupo) return;
    grupo.style.display = turno.value === 'personalizado' ? 'grid' : 'none';
  }

  qsa('[data-modal-close]').forEach((btn) => {
    btn.addEventListener('click', () => fecharModal(btn.closest('.modal-overlay')));
  });

  qsa('.modal-overlay').forEach((modal) => {
    modal.addEventListener('click', (event) => {
      if (event.target === modal) fecharModal(modal);
    });
  });

  qs('#modal_turno')?.addEventListener('change', atualizarHorarioCustom);

  qsa('.js-escala-editar, [data-escala-novo]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const modal = qs('#modal-escala');
      if (!modal) return;

      const form = qs('#form-escala', modal) || qs('form', modal);
      if (form) {
        form.action = `${BASE_URL}/escala/salvar`;
        form.reset();
      }

      setValue(modal, '#modal_escala_id', btn.dataset.id || '');
      setValue(modal, '#modal_paciente_id', btn.dataset.paciente || btn.dataset.pacienteUuid || '');
      setValue(modal, '#modal_colaborador_id', btn.dataset.cuidador || btn.dataset.cuidadorUuid || '');
      setValue(modal, '#modal_data_plantao', btn.dataset.data || '');
      setValue(modal, '#modal_inicio', (btn.dataset.inicio || '07:00').substring(0, 5));
      setValue(modal, '#modal_fim', (btn.dataset.fim || '19:00').substring(0, 5));
      setValue(modal, '#modal_turno', btn.dataset.turno || 'personalizado');
      atualizarHorarioCustom();

      const titulo = qs('#modal-escala-title', modal);
      if (titulo) titulo.innerHTML = btn.dataset.id ? '<i class="ti ti-calendar-plus" aria-hidden="true"></i> Editar plantão' : '<i class="ti ti-calendar-plus" aria-hidden="true"></i> Alocar plantão';

      abrirModal('#modal-escala');
    });
  });

  qsa('.js-escala-substituir').forEach((btn) => {
    btn.addEventListener('click', () => {
      const modal = qs('#modal-substituicao');
      if (!modal) return;

      const form = qs('#form-substituicao', modal) || qs('form', modal);
      if (form) form.action = `${BASE_URL}/escala/substituir`;

      setValue(modal, '#sub_escala_id', btn.dataset.id || '');
      setValue(modal, '#sub_colaborador_id', btn.dataset.cuidadorId || '');
      setValue(modal, '#sub_data', btn.dataset.data || '');
      setValue(modal, '#sub_motivo', '');
      setValue(modal, '#sub_substituto_id', '');
      setValue(modal, '#sub_obs', '');

      abrirModal('#modal-substituicao');
    });
  });

  let draggedId = null;

  qsa('.escala-shift[draggable="true"]').forEach((shift) => {
    shift.addEventListener('dragstart', (event) => {
      draggedId = shift.dataset.escalaId || null;
      if (!draggedId) {
        event.preventDefault();
        return;
      }
      event.dataTransfer.effectAllowed = 'move';
      event.dataTransfer.setData('text/plain', draggedId);
      shift.classList.add('is-dragging');
    });

    shift.addEventListener('dragend', () => {
      shift.classList.remove('is-dragging');
      qsa('.escala-shift.is-drop-target').forEach((target) => target.classList.remove('is-drop-target'));
      draggedId = null;
    });

    shift.addEventListener('dragover', (event) => {
      if (!shift.dataset.escalaId) return;
      event.preventDefault();
      shift.classList.add('is-drop-target');
    });

    shift.addEventListener('dragleave', () => shift.classList.remove('is-drop-target'));

    shift.addEventListener('drop', async (event) => {
      event.preventDefault();
      shift.classList.remove('is-drop-target');

      const origemId = draggedId || event.dataTransfer.getData('text/plain');
      const destinoId = shift.dataset.escalaId || '';
      if (!origemId || !destinoId || origemId === destinoId) return;

      const params = new URLSearchParams(location.search);
      const body = new URLSearchParams();
      body.set('_csrf', qs('input[name="_csrf"]')?.value || '');
      body.set('origem_id', origemId);
      body.set('destino_id', destinoId);
      body.set('modo', qs('[name="modo"]')?.value || params.get('modo') || 'semana');
      body.set('periodo', qs('[name="periodo"]')?.value || params.get('periodo') || '');
      body.set('paciente_uuid', shift.dataset.pacienteUuid || params.get('paciente') || '');

      try {
        const response = await fetch(`${BASE_URL}/escala/trocar`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body,
        });

        if (!response.ok) throw new Error('Falha ao trocar cuidadores.');
        window.location.reload();
      } catch (error) {
        alert(error.message || 'Não foi possível trocar os cuidadores.');
      }
    });
  });
})();
