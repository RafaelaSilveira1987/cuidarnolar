/**
 * public/assets/js/relatorio_plantao_form.js
 *
 * Responsabilidades:
 *  1. Check-groups (single/multi) — pills clicáveis sincronizados com inputs
 *  2. Semáforo de sinais vitais em tempo real
 *  3. Range de dor com badge colorido
 *  4. Toggle de medicamento: Pendente → Administrado → Recusado
 *  5. Mostrar/ocultar detalhes de diurese e evacuação
 *  6. Intercorrências: adicionar / remover
 *  7. Checkbox "sem intercorrências"
 *  8. Gerador automático de evolução
 *  9. Confirmar assinatura antes de submeter
 */

document.addEventListener("DOMContentLoaded", () => {
  // ─────────────────────────────────────────────────────────────
  // 1. CHECK-GROUPS — pills clicáveis
  // ─────────────────────────────────────────────────────────────
  document.querySelectorAll(".check-group").forEach((group) => {
    const mode = group.dataset.mode ?? "single"; // 'single' | 'multi'
    const opts = group.querySelectorAll(".check-opt");

    opts.forEach((opt) => {
      const inp = opt.querySelector("input");
      if (!inp) return;

      // Estado inicial
      if (inp.checked) opt.classList.add("sel");

      opt.addEventListener("click", (e) => {
        // Não duplica quando o input nativo dispara
        if (e.target === inp) {
          syncOpt(opt, inp, group, mode, opts);
          return;
        }
        // Clique no label — toggle manual
        if (mode === "single") {
          inp.checked = true;
        } else {
          inp.checked = !inp.checked;
        }
        syncOpt(opt, inp, group, mode, opts);
      });

      // Sincroniza quando o input é alterado externamente
      inp.addEventListener("change", () => {
        syncOpt(opt, inp, group, mode, opts);
      });
    });
  });

  function syncOpt(opt, inp, group, mode, allOpts) {
    if (mode === "single") {
      allOpts.forEach((o) => {
        const i = o.querySelector("input");
        o.classList.toggle("sel", i && i.checked);
      });
    } else {
      opt.classList.toggle("sel", inp.checked);
    }
  }

  // ─────────────────────────────────────────────────────────────
  // 2. SEMÁFORO DE SINAIS VITAIS
  // ─────────────────────────────────────────────────────────────
  window.avaliarSinal = function (inp, tipo) {
    const v = parseFloat(inp.value);
    let cls = "",
      txt = "";

    if (isNaN(v) || inp.value === "") {
      setBadge("badge-" + tipo, "hidden", "");
      return;
    }

    switch (tipo) {
      case "pa": {
        const [sys, dia] = inp.value.split(/[\/x\-]/).map(Number);
        if (!isNaN(sys)) {
          if (sys >= 140 || (!isNaN(dia) && dia >= 90)) {
            cls = "badge-crit";
            txt = "Elevada";
          } else if (sys >= 130 || (!isNaN(dia) && dia >= 85)) {
            cls = "badge-warn";
            txt = "Limítrofe";
          } else if (sys < 90) {
            cls = "badge-crit";
            txt = "Hipotensão";
          } else {
            cls = "badge-ok";
            txt = "Normal";
          }
        }
        break;
      }
      case "fc":
        if (v > 100) {
          cls = "badge-crit";
          txt = "Taquicardia";
        } else if (v < 60) {
          cls = "badge-crit";
          txt = "Bradicardia";
        } else if (v > 90) {
          cls = "badge-warn";
          txt = "Atenção";
        } else {
          cls = "badge-ok";
          txt = "Normal";
        }
        break;
      case "temp":
        if (v >= 38) {
          cls = "badge-crit";
          txt = "Febre";
        } else if (v >= 37.5) {
          cls = "badge-warn";
          txt = "Febrícula";
        } else if (v < 35.5) {
          cls = "badge-warn";
          txt = "Hipotermia";
        } else {
          cls = "badge-ok";
          txt = "Afebril";
        }
        break;
      case "spo2":
        if (v < 90) {
          cls = "badge-crit";
          txt = "Crítico";
        } else if (v < 95) {
          cls = "badge-warn";
          txt = "Atenção";
        } else {
          cls = "badge-ok";
          txt = "Normal";
        }
        break;
      case "hgt":
        if (v >= 180 || v < 70) {
          cls = "badge-crit";
          txt = "Alterado";
        } else if (v >= 140) {
          cls = "badge-warn";
          txt = "Atenção";
        } else {
          cls = "badge-ok";
          txt = "Normal";
        }
        break;
    }

    setBadge("badge-" + tipo, cls, txt);
  };

  window.setTurno = function (valor, btnClicado) {
    // Remove active de todos os pills do seletor
    document
      .querySelectorAll("#turno-selector .turno-pill")
      .forEach((p) => p.classList.remove("active"));

    // Ativa o clicado
    btnClicado.classList.add("active");

    // Atualiza o campo hidden
    const input = document.getElementById("input-turno");
    if (input) input.value = valor;
  };

  // Estado inicial: garante que o pill correto está ativo ao carregar a página
  document.addEventListener("DOMContentLoaded", () => {
    const inputTurno = document.getElementById("input-turno");
    if (!inputTurno) return;

    const turnoAtual = inputTurno.value;

    document.querySelectorAll("#turno-selector .turno-pill").forEach((p) => {
      p.classList.toggle("active", p.dataset.turno === turnoAtual);
    });
  });

  function setBadge(id, cls, txt) {
    const el = document.getElementById(id);
    if (!el) return;
    el.className = "sinal-badge" + (cls ? " " + cls : " hidden");
    el.textContent = txt;
  }

  // Dispara avaliação inicial se houver valores salvos
  ["pa", "fc", "temp", "spo2", "hgt"].forEach((t) => {
    const inp = document.getElementById("inp-" + t);
    if (inp && inp.value) avaliarSinal(inp, t);
  });

  // ─────────────────────────────────────────────────────────────
  // 3. RANGE DE DOR
  // ─────────────────────────────────────────────────────────────
  window.updateDor = function (inp) {
    const v = parseInt(inp.value, 10);
    const valEl = document.getElementById("dor-val");
    const badgeEl = document.getElementById("dor-badge");
    if (valEl) valEl.textContent = v;

    let cls = "",
      txt = "";
    if (v === 0) {
      cls = "badge-ok";
      txt = "Sem dor";
    } else if (v <= 3) {
      cls = "badge-ok";
      txt = "Leve";
    } else if (v <= 6) {
      cls = "badge-warn";
      txt = "Moderada";
    } else {
      cls = "badge-crit";
      txt = "Intensa";
    }

    if (badgeEl) {
      badgeEl.className = "sinal-badge " + cls;
      badgeEl.textContent = txt;
    }
  };

  const dorRange = document.getElementById("dor-range");
  if (dorRange && dorRange.value) updateDor(dorRange);

  // ─────────────────────────────────────────────────────────────
  // 4. TOGGLE MEDICAMENTOS
  // ─────────────────────────────────────────────────────────────
  window.setMedStatus = function (idx, status, btnClicado) {
    // Atualiza hidden input
    const hidden = document.getElementById("med-status-" + idx);
    if (hidden) hidden.value = status;

    // Atualiza visual: remove active de todos os pills do grupo, ativa o clicado
    const container = btnClicado.closest(".med-toggle-group");
    if (container) {
      container
        .querySelectorAll(".med-pill")
        .forEach((p) => p.classList.remove("active"));
      btnClicado.classList.add("active");
    }
  };

  // ─────────────────────────────────────────────────────────────
  // 5. SHOW/HIDE DETALHES (diurese / evacuação)
  // ─────────────────────────────────────────────────────────────
  window.toggleDetalhes = function (id, mostrar) {
    const el = document.getElementById(id);
    if (el) el.style.display = mostrar ? "block" : "none";
  };

  // ─────────────────────────────────────────────────────────────
  // 6. INTERCORRÊNCIAS — adicionar / remover
  // ─────────────────────────────────────────────────────────────
  let interCount = window.FORM_DATA?.interCount ?? 0;

  window.addInter = function () {
    const list = document.getElementById("inter-list");
    if (!list) return;

    const div = document.createElement("div");
    div.className = "inter-item";
    div.innerHTML = `
            <i class="ti ti-alert-triangle inter-icon"></i>
            <textarea name="intercorrencias[${interCount}][descricao]"
                      class="rp-input inter-textarea" rows="2"
                      placeholder="Descreva a intercorrência..."></textarea>
            <button type="button" class="inter-remove" onclick="removeInter(this)">
                <i class="ti ti-trash"></i>
            </button>`;
    list.appendChild(div);
    div.querySelector("textarea")?.focus();
    interCount++;
  };

  window.removeInter = function (btn) {
    btn.closest(".inter-item")?.remove();
  };

  // ─────────────────────────────────────────────────────────────
  // 7. "SEM INTERCORRÊNCIAS" CHECKBOX
  // ─────────────────────────────────────────────────────────────
  window.toggleSemInter = function (chk) {
    const list = document.getElementById("inter-list");
    const btnAdd = document.getElementById("btn-add-inter");

    if (chk.checked) {
      if (list) list.innerHTML = "";
      if (btnAdd) btnAdd.style.display = "none";
    } else {
      if (btnAdd) btnAdd.style.display = "";
    }
  };

  // Estado inicial
  const semInterChk = document.getElementById("sem-inter-chk");
  if (semInterChk) toggleSemInter(semInterChk);

  // ─────────────────────────────────────────────────────────────
  // 8. GERADOR DE EVOLUÇÃO AUTOMÁTICA
  // ─────────────────────────────────────────────────────────────
  window.gerarEvolucao = function () {
    const partes = [];
    const fd = window.FORM_DATA ?? {};

    // Diagnóstico
    if (fd.diagnostico) {
      const cid = fd.cid ? ` (CID ${fd.cid})` : "";
      partes.push(
        `Paciente em acompanhamento domiciliar por ${fd.diagnostico}${cid}.`,
      );
    }

    // Estado geral + consciência
    const consciencia = document.querySelector(
      'input[name="consciencia"]:checked',
    )?.value;
    if (consciencia) partes.push(`Nível de consciência: ${consciencia}.`);

    // Sinais vitais
    const pa = val("pa");
    const fc = val("fc");
    const temp = val("temperatura");
    const spo2 = val("spo2");
    const hgt = val("hgt");
    const svParts = [];
    if (pa) svParts.push(`PA ${pa} mmHg`);
    if (fc) svParts.push(`FC ${fc} bpm`);
    if (temp) svParts.push(`Temp ${temp}°C`);
    if (spo2) svParts.push(`SpO₂ ${spo2}%`);
    if (hgt) svParts.push(`HGT ${hgt} mg/dL`);
    if (svParts.length) {
      partes.push(`Sinais vitais: ${svParts.join(", ")}.`);
    }

    // Dor
    const dorVal = document.getElementById("dor-range")?.value;
    if (dorVal !== undefined && dorVal !== "0") {
      const dorLabel =
        parseInt(dorVal) <= 3
          ? "leve"
          : parseInt(dorVal) <= 6
            ? "moderada"
            : "intensa";
      partes.push(`Relata dor de intensidade ${dorLabel} (${dorVal}/10).`);
    }

    // Dispositivos
    document.querySelectorAll(".dispositivo-item").forEach((item) => {
      const tipo = item.querySelector("strong")?.textContent?.trim();
      const status = item.querySelector('input[type="radio"]:checked')?.value;
      const obs = item.querySelector('input[type="text"]')?.value?.trim();
      if (tipo && status) {
        const label =
          status === "cuidados_realizados"
            ? "cuidados realizados"
            : status === "com_intercorrencia"
              ? "intercorrência registrada"
              : "sem intercorrências";
        partes.push(`${tipo}: ${label}${obs ? ". " + obs : ""}.`);
      }
    });

    // Medicamentos administrados
    const medsAdm = [];
    document.querySelectorAll(".med-check-item").forEach((item) => {
      const statusInp = item.querySelector('input[id^="med-status-"]');
      if (statusInp?.value === "administrado") {
        const nome =
          item.querySelector('input[name$="[medicamento]"]')?.value ?? "";
        const hora =
          item.querySelector('input[name$="[horario]"]')?.value ?? "";
        medsAdm.push(hora !== "—" ? `${nome} às ${hora}` : nome);
      }
    });
    if (medsAdm.length) {
      partes.push(
        `Medicamentos administrados conforme prescrição: ${medsAdm.join("; ")}.`,
      );
    }

    // Alimentação
    const alim = document.querySelector(
      'input[name="alimentacao"]:checked',
    )?.value;
    const via = document.querySelector(
      'input[name="alimentacao_via"]:checked',
    )?.value;
    if (alim) {
      partes.push(
        `Alimentação: ${alim}${via ? " via " + via.toLowerCase() : ""}.`,
      );
    }

    // Hidratação
    const hidra = val("hidratacao_ml");
    if (hidra && hidra !== "0")
      partes.push(`Hidratação oferecida: ${hidra} mL.`);

    // Higiene
    const higiene = checked("higiene[]");
    if (higiene.length && !higiene.includes("Não realizado")) {
      partes.push(`Realizados cuidados de higiene: ${higiene.join(", ")}.`);
    } else if (higiene.includes("Não realizado")) {
      partes.push("Cuidados de higiene não realizados no turno.");
    }

    // Diurese
    const diurPresente = document.querySelector(
      'input[name="diurese[presente]"]:checked',
    )?.value;
    if (diurPresente === "Sim") {
      const cor =
        document.querySelector('input[name="diurese[cor]"]:checked')?.value ??
        "";
      const odor =
        document.querySelector('input[name="diurese[odor]"]:checked')?.value ??
        "";
      const volume =
        document.querySelector('input[name="diurese[volume]"]:checked')
          ?.value ?? "";
      let d = "Diurese presente";
      if (cor) d += `, coloração ${cor.toLowerCase()}`;
      if (odor && odor !== "Sem alterações")
        d += `, odor ${odor.toLowerCase()}`;
      if (volume) d += `, volume ${volume.toLowerCase()}`;
      partes.push(d + ".");
    } else if (diurPresente === "Não") {
      partes.push("Diurese ausente no turno.");
    }

    // Evacuação
    const evacPresente = document.querySelector(
      'input[name="evacuacao[presente]"]:checked',
    )?.value;
    if (evacPresente === "Sim") {
      const consist =
        document.querySelector('input[name="evacuacao[consistencia]"]:checked')
          ?.value ?? "";
      const cores = checked("evacuacao[cor][]");
      let e = "Evacuação presente";
      if (consist) e += `, fezes ${consist.toLowerCase()}`;
      if (cores.length) e += `, ${cores.join(", ").toLowerCase()}`;
      partes.push(e + ".");
    } else if (evacPresente === "Não") {
      partes.push("Sem evacuação no turno.");
    }

    // Decúbito
    if (fd.acamado) {
      const posicoes = checked("decubito[]");
      const freq =
        document.querySelector('input[name="decubito_frequencia"]:checked')
          ?.value ?? "";
      if (posicoes.length) {
        partes.push(
          `Mudanças de decúbito realizadas${freq ? " " + freq.toLowerCase() : ""}: ${posicoes.join(", ")}.`,
        );
      }
    }

    // Sono
    const sono = document.querySelector('input[name="sono"]:checked')?.value;
    if (sono) partes.push(`Sono: ${sono.toLowerCase()}.`);

    // Intercorrências
    const semInter = document.getElementById("sem-inter-chk")?.checked;
    if (semInter) {
      partes.push("Turno transcorreu sem intercorrências.");
    } else {
      const inters = [
        ...document.querySelectorAll("#inter-list .inter-item textarea"),
      ]
        .map((t) => t.value.trim())
        .filter(Boolean);
      if (inters.length) {
        partes.push("Intercorrências: " + inters.join("; ") + ".");
      }
    }

    // Aplica no textarea
    const ta = document.getElementById("evolucao");
    if (ta) {
      ta.value = partes.join("\n");
      ta.focus();
    }
  };

  document
    .getElementById("btn-gerar-evolucao")
    ?.addEventListener("click", gerarEvolucao);

  // ─────────────────────────────────────────────────────────────
  // 9. CONFIRMAR ASSINATURA
  // ─────────────────────────────────────────────────────────────
  window.confirmarAssinatura = function () {
    return confirm(
      "Deseja assinar e finalizar este relatório? Esta ação não poderá ser desfeita.",
    );
  };

  // Também intercepta pelo form submit para qualquer botão assinar
  document.getElementById("relatorio-form")?.addEventListener("submit", (e) => {
    const btn = document.activeElement;
    if (btn && btn.value === "assinar") {
      if (
        !confirm(
          "Deseja assinar e finalizar este relatório? Esta ação não poderá ser desfeita.",
        )
      ) {
        e.preventDefault();
      }
    }
  });

  // ─────────────────────────────────────────────────────────────
  // HELPERS INTERNOS
  // ─────────────────────────────────────────────────────────────
  function val(name) {
    const el = document.querySelector(`[name="${name}"]`);
    return el ? el.value.trim() : "";
  }

  function checked(name) {
    return [...document.querySelectorAll(`input[name="${name}"]:checked`)].map(
      (el) => el.value,
    );
  }
});
