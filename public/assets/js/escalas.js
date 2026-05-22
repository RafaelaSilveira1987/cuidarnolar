/**
 * escalas.js — Central de Cobertura HomeCare
 * Gerencia interações da grade semanal, modais e alertas.
 */

const Escalas = (() => {
  // ---- Estado atual de filtros ----
  const state = {
    semana: null, // data base (Date)
    paciente_id: "",
    cuidador_id: "",
  };

  // ---- Inicialização ----
  function init() {
    _bindFiltros();
    _bindModal();
    _bindPlantaoCells();
    _highlightHoje();
  }

  // ---- Destaca a coluna do dia atual ----
  function _highlightHoje() {
    const hoje = new Date().toLocaleDateString("pt-BR", {
      day: "2-digit",
      month: "2-digit",
    });
    document.querySelectorAll("[data-date]").forEach((el) => {
      const d = new Date(el.dataset.date + "T00:00:00");
      const txt = d.toLocaleDateString("pt-BR", {
        day: "2-digit",
        month: "2-digit",
      });
      if (txt === hoje) el.classList.add("hoje");
    });
  }

  // ---- Filtros do topbar ----
  function _bindFiltros() {
    const selPac = document.getElementById("filtro-paciente");
    const selCol = document.getElementById("filtro-colaborador");
    const selSem = document.getElementById("filtro-semana");

    [selPac, selCol, selSem].forEach((el) => {
      if (!el) return;
      el.addEventListener("change", () => {
        state.paciente_id = selPac?.value || "";
        state.cuidador_id = selCol?.value || "";
        const semanaStr = selSem?.value || "";
        _recarregarGrade(semanaStr);
      });
    });
  }

  // ---- Recarrega via AJAX (ou submit normal) ----
  function _recarregarGrade(semana) {
    const params = new URLSearchParams();

    if (state.paciente_id) {
      params.set("paciente_id", state.paciente_id);
    }

    if (state.cuidador_id) {
      params.set("cuidador_id", state.cuidador_id);
    }

    if (semana) {
      params.set("semana", semana);
    }

    const base =
      document.querySelector('meta[name="base-url"]')?.content ??
      window.BASE_URL ??
      "";

    const url = `${base}/escala?${params.toString()}`;

    window.location.href = url;
  }

  // ---- Modal criar / editar plantão ----
  function _bindModal() {
    // Abrir modal ao clicar em [data-modal-escala]
    document.addEventListener("click", (e) => {
      const trigger = e.target.closest("[data-modal-escala]");
      if (trigger) {
        e.preventDefault();
        const data = trigger.dataset;
        _abrirModal({
          paciente_id: data.pacienteId || "",
          data_plantao: data.dataPlantao || "",
          turno: data.turno || "",
          cuidador_id: data.cuidadorId || "",
          escala_id: data.escalaId || "",
        });
      }

      // Fechar modal
      if (
        e.target.classList.contains("modal-overlay") ||
        e.target.closest("[data-modal-close]")
      ) {
        _fecharModal();
      }
    });

    // ESC fecha
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") _fecharModal();
    });
  }

  function _abrirModal(dados) {
    const overlay = document.getElementById("modal-escala");
    if (!overlay) {
      console.error(
        "[Escalas] #modal-escala não encontrado no DOM. Verifique se modal_criar.php está sendo incluído.",
      );
      return;
    }

    // Preenche campos
    _setVal("modal_paciente_id", dados.paciente_id);
    _setVal("modal_data_plantao", dados.data_plantao);
    _setVal("modal_turno", dados.turno);
    _setVal("modal_colaborador_id", dados.colaborador_id);
    _setVal("modal_escala_id", dados.escala_id);

    // Modo editar vs criar: ajusta título e label do botão submit
    const isEdicao = !!dados.escala_id;
    const titulo   = overlay.querySelector("[data-modal-titulo]");
    const btnSalvar = overlay.querySelector("[data-modal-submit]");
    if (titulo)    titulo.textContent    = isEdicao ? "Editar Plantão"    : "Novo Plantão";
    if (btnSalvar) btnSalvar.textContent = isEdicao ? "Salvar alterações" : "Alocar cuidador";

    overlay.style.display = "flex";
    overlay.querySelector("select, input")?.focus();
  }

  function _fecharModal() {
    document.getElementById("modal-escala")?.style &&
      (document.getElementById("modal-escala").style.display = "none");
    document.getElementById("modal-substituicao")?.style &&
      (document.getElementById("modal-substituicao").style.display = "none");
  }

  function _setVal(id, val) {
    const el = document.getElementById(id);
    if (el) el.value = val || "";
  }

  // ---- Clique nas células de plantão ---- //
  // Substitua _bindPlantaoCells() por esta versão:
  function _bindPlantaoCells() {
    document.addEventListener("click", (e) => {
      // ── Botão Editar ──────────────────────────────────────
      const btnEditar = e.target.closest('[data-action="editar"]');
      if (btnEditar) {
        e.stopPropagation();
        const cell = btnEditar.closest(".plantao-cell");
        if (!cell) return;
        const d = cell.dataset;
        _abrirModal({
          paciente_id: d.pacienteId,
          data_plantao: d.dataPlantao,
          turno: d.turno,
          colaborador_id: d.colaboradorId,
          escala_id: d.escalaId,
        });
        return;
      }

      const btnSub = e.target.closest('[data-action="substituir"]');

      if (btnSub) {
        e.stopPropagation();

        const cell = btnSub.closest(".plantao-cell");

        if (!cell) return;

        const d = cell.dataset;

        _abrirModalSub({
          escala_id: d.escalaId,
          colaborador_id: d.colaboradorId,
          data_plantao: d.dataPlantao,
        });

        return;
      }

      // ── Botão Excluir ─────────────────────────────────────
      const btnExcluir = e.target.closest('[data-action="excluir"]');
      if (btnExcluir) {
        e.stopPropagation();
        if (!confirm("Remover este plantão? Esta ação não pode ser desfeita."))
          return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content")
                  ?? document.querySelector('input[name="_csrf"]')?.value
                  ?? "";
        const form = document.createElement("form");
        form.method = "POST";
        form.action = `${window.BASE_URL}/escala/excluir`;
        form.innerHTML = `
          <input name="escala_id" value="${btnExcluir.dataset.escalaId}">
          <input name="_token"    value="${csrf}">
        `;
        document.body.appendChild(form);
        form.submit();
        return;
      }

      // ── Clique na célula vaga → abrir modal criar ─────────
      const cell = e.target.closest(".plantao-cell");
      if (!cell) return;
      const {
        status,
        pacienteId,
        dataPlantao,
        turno,
        escalaId,
        colaboradorId,
      } = cell.dataset;

      if (status === "vago") {
        _abrirModal({
          paciente_id: pacienteId,
          data_plantao: dataPlantao,
          turno,
        });
      } else if (status === "ok" || status === "sub") {
        _abrirModalSub({
          escala_id: escalaId,
          colaborador_id: colaboradorId,
          data_plantao: dataPlantao,
        });
      }
      // 'ok' → sem ação no clique da célula, use os botões
    });
  }

  function _abrirModalSub(dados) {
    const overlay = document.getElementById("modal-substituicao");
    if (!overlay) return;

    _setVal("sub_escala_id", dados.escala_id);
    _setVal("sub_colaborador_id", dados.colaborador_id);
    _setVal("sub_data", dados.data_plantao);

    overlay.style.display = "flex";
    overlay.querySelector("select, input")?.focus();
  }

  // ---- Calcula % de cobertura e atualiza barra ----
  // Chamado pelo PHP ou pode ser re-calculado no front
  function atualizarCobertura(wrapId, pct) {
    const wrap = document.getElementById(wrapId);
    if (!wrap) return;

    const fill = wrap.querySelector(".cobertura-bar__fill");
    const txt = wrap.querySelector(".cobertura-pct");

    const cls = pct >= 95 ? "ok" : pct >= 70 ? "warn" : "danger";

    if (fill) {
      fill.style.width = `${Math.min(pct, 100)}%`;
      fill.className = `cobertura-bar__fill cobertura-bar__fill--${cls}`;
    }
    if (txt) {
      txt.textContent = `${Math.round(pct)}%`;
      txt.className = `cobertura-pct cobertura-pct--${cls}`;
    }
  }

  // ---- Toast de feedback ----
  function toast(msg, tipo = "info") {
    const cores = {
      ok: "#065f46",
      warn: "#78350f",
      danger: "#991b1b",
      info: "#1e3a8a",
    };
    const t = document.createElement("div");
    t.style.cssText = [
      "position:fixed",
      "bottom:1.5rem",
      "right:1.5rem",
      "background:#fff",
      `border-left:4px solid ${cores[tipo]}`,
      "border-radius:8px",
      "padding:.75rem 1.25rem",
      "font-size:13px",
      "font-weight:500",
      "color:#111827",
      "box-shadow:0 4px 16px rgba(0,0,0,.12)",
      "z-index:2000",
      "animation:slideUp .2s ease",
      "max-width:300px",
    ].join(";");
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
  }

  // ---- API pública ----
  return { init, atualizarCobertura, toast };
})();

document.addEventListener("DOMContentLoaded", () => Escalas.init());