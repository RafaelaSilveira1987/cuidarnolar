(function () {
  "use strict";

  const {
    agrupado: AGRUPADO = {},
    datas: DATAS = [],
    cuidadores: CUIDADORES = {},
    pacienteUuid: PACIENTE_UUID = "",
    baseUrl: BASE_URL = "",
  } = window.RELATORIO_DATA || {};

  let dataAtual = DATAS.length ? DATAS[DATAS.length - 1] : null;

  const labelData = document.getElementById("labelData");
  const btnAnterior = document.getElementById("btnDiaAnterior");
  const btnProximo = document.getElementById("btnProximoDia");
  const turnosRow = document.getElementById("turnosRow");
  const turnoDetalhe = document.getElementById("turnoDetalhe");

  /* ================= Utils ================= */

  function esc(value) {
    if (value == null) return "";
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function fmtDataBR(iso) {
    if (!iso) return "—";

    const [y, m, d] = iso.split("-");
    const DIAS = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];
    const MESES = [
      "jan",
      "fev",
      "mar",
      "abr",
      "mai",
      "jun",
      "jul",
      "ago",
      "set",
      "out",
      "nov",
      "dez",
    ];

    const dt = new Date(`${iso}T12:00:00`);
    return `${DIAS[dt.getDay()]}, ${d} ${MESES[Number(m) - 1]} ${y}`;
  }

  function fmtHora(dateTime) {
    if (!dateTime) return "—";

    return new Date(dateTime.replace(" ", "T")).toLocaleTimeString("pt-BR", {
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function getCuidador(cuidadorId) {
    const id = String(cuidadorId);
    return CUIDADORES[id] || CUIDADORES[Number(id)] || null;
  }

  /* ================= Avaliações ================= */

  function badgeClass(type, label) {
    return `<span class="sv-badge ${type}">${label}</span>`;
  }

  function avalPA(v) {
    const [s, d] = String(v || "").split("/").map(Number);

    if (!s || !d) return "";
    if (s >= 140 || d >= 90) return badgeClass("badge-crit", "Elevada");
    if (s >= 130 || d >= 85) return badgeClass("badge-warn", "Limítrofe");
    return badgeClass("badge-ok", "Normal");
  }

  function avalFC(v) {
    const n = Number(v);
    if (!n) return "";

    if (n > 100) return badgeClass("badge-crit", "Taquicardia");
    if (n < 60) return badgeClass("badge-crit", "Bradicardia");
    if (n > 90) return badgeClass("badge-warn", "Atenção");
    return badgeClass("badge-ok", "Normal");
  }

  function avalTemp(v) {
    const n = Number(v);
    if (!n) return "";

    if (n >= 38) return badgeClass("badge-crit", "Febre");
    if (n >= 37.5) return badgeClass("badge-warn", "Febrícula");
    return badgeClass("badge-ok", "Afebril");
  }

  function avalSpo2(v) {
    const n = Number(v);
    if (!n) return "";

    if (n < 90) return badgeClass("badge-crit", "Crítico");
    if (n < 95) return badgeClass("badge-warn", "Atenção");
    return badgeClass("badge-ok", "Normal");
  }

  function avalHgt(v) {
    const n = Number(v);
    if (!n) return "";

    if (n >= 180 || n < 70) return badgeClass("badge-crit", "Alterado");
    if (n >= 140) return badgeClass("badge-warn", "Atenção");
    return badgeClass("badge-ok", "Normal");
  }

  function svCol(sigla, valor, sufixo, avaliador) {
    if (!valor && valor !== 0) return "";

    return `
      <div class="sv-col">
        <div class="sv-sigla">${sigla}</div>
        <div class="sv-valor">
          ${esc(valor)}
          <span class="sv-sufixo">${sufixo || ""}</span>
        </div>
        ${avaliador ? avaliador(valor) : ""}
      </div>
    `;
  }

  /* ================= Cards ================= */

  function renderCardsDia(data) {
    const lista = AGRUPADO[data] || [];

    if (!lista.length) {
      turnosRow.innerHTML = `
        <div class="turno-vazio">
          Nenhum relatório neste dia.
        </div>
      `;
      return;
    }

    turnosRow.innerHTML = lista
      .map((item) => {
        const cuidador = getCuidador(item.cuidador_id);

        return `
          <button class="plantao-card" onclick="abrirPlantao(${item.id})">
            <div class="plantao-card-top">
              <span>${fmtHora(item.data_inicio)}</span>
              <span>${esc(item.status || "rascunho")}</span>
            </div>

            <strong>${esc(cuidador?.nome || "Sem cuidador")}</strong>

            <small>
              ${fmtHora(item.data_inicio)}
              ${item.data_fim ? " - " + fmtHora(item.data_fim) : ""}
            </small>
          </button>
        `;
      })
      .join("");
  }

  /* ================= Detalhe ================= */

  function renderDetalhePlantao(p) {
    if (!p) return;

    const cuidador = getCuidador(p.cuidador_id);

    turnoDetalhe.innerHTML = `
      <div class="plantao-acoes">
        <span class="acao-meta">
          <i class="ti ti-clock"></i>
          ${fmtHora(p.data_inicio)}
          ${p.data_fim ? " → " + fmtHora(p.data_fim) : ""}
        </span>

        <div class="acao-btns">
          <a href="${BASE_URL}/relatorio-plantao/plantao/${p.id}/editar" class="btn-outline">
            <i class="ti ti-pencil"></i> Editar
          </a>

          ${
            !p.assinado
              ? `
              <a href="${BASE_URL}/relatorio-plantao/plantao/${p.id}/assinar" class="btn-assinar-link">
                <i class="ti ti-pen"></i> Assinar
              </a>
            `
              : ""
          }
        </div>
      </div>

      <div class="bloco">
        <div class="bloco-titulo">
          <i class="ti ti-user"></i> Cuidador responsável
        </div>
        <div class="evolucao-txt">
          ${esc(cuidador?.nome || "Sem cuidador")}
        </div>
      </div>

      <div class="bloco">
        <div class="bloco-titulo">
          <i class="ti ti-notes"></i> Evolução
        </div>
        <div class="evolucao-txt">
          ${esc(p.evolucao || "Sem evolução registrada")}
        </div>
      </div>

      <div class="bloco">
        <div class="bloco-titulo">
          <i class="ti ti-heart-rate-monitor"></i> Sinais vitais
        </div>

        <div class="sv-row">
          ${svCol("PA", p.pa, "mmHg", avalPA)}
          ${svCol("FC", p.fc, "bpm", avalFC)}
          ${svCol("TEMP", p.temperatura, "°C", avalTemp)}
          ${svCol("SpO2", p.spo2, "%", avalSpo2)}
          ${svCol("HGT", p.hgt, "mg/dL", avalHgt)}
        </div>
      </div>
    `;
  }

  /* ================= Navegação ================= */

  function irParaData(data) {
    dataAtual = data;

    labelData.textContent = fmtDataBR(data);

    const idx = DATAS.indexOf(dataAtual);
    btnAnterior.disabled = idx <= 0;
    btnProximo.disabled = idx >= DATAS.length - 1;

    renderCardsDia(dataAtual);

    // turnoDetalhe.innerHTML = `
    //   <div class="turno-vazio">
    //     Selecione um relatório acima
    //   </div>
    // `;
  }

  window.abrirPlantao = function (id) {
    for (const data of DATAS) {
      const lista = AGRUPADO[data] || [];
      const plantao = lista.find((p) => Number(p.id) === Number(id));

      if (plantao) {
        renderDetalhePlantao(plantao);
        return;
      }
    }
  };

  /* ================= Eventos ================= */

  btnAnterior?.addEventListener("click", () => {
    const idx = DATAS.indexOf(dataAtual);
    if (idx > 0) irParaData(DATAS[idx - 1]);
  });

  btnProximo?.addEventListener("click", () => {
    const idx = DATAS.indexOf(dataAtual);
    if (idx < DATAS.length - 1) irParaData(DATAS[idx + 1]);
  });

  /* ================= Init ================= */

  if (DATAS.length) {
    irParaData(dataAtual);
  } else {
    labelData.textContent = "—";
    btnAnterior.disabled = true;
    btnProximo.disabled = true;

    turnosRow.innerHTML = `
      <div class="turno-vazio">
        Nenhum relatório encontrado.
      </div>
    `;
  }
})();