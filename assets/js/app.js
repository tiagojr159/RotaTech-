(() => {
  const $ = (s, root = document) => root.querySelector(s);
  const $$ = (s, root = document) => Array.from(root.querySelectorAll(s));

  const showToast = (message) => {
    if (!message) return;
    const old = $(".toast");
    if (old) old.remove();
    const toast = document.createElement("div");
    toast.className = "toast";
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2600);
  };

  const postApi = async (data, isFormData = false) => {
    const options = { method: "POST" };
    if (isFormData) {
      options.body = data;
    } else {
      options.headers = { "Content-Type": "application/x-www-form-urlencoded" };
      options.body = new URLSearchParams(data).toString();
    }
    const res = await fetch("api.php", options);
    const json = await res.json();
    if (!res.ok || json.ok === false) {
      throw new Error(json.message || "Erro ao processar solicitação");
    }
    return json;
  };

  const markActiveBottomTab = () => {
    const file = location.pathname.split("/").pop() || "";
    const map = {
      "home.php": "home",
      "programacao.php": "explorar",
      "restaurantes.php": "explorar",
      "album.php": "album",
      "roteiro.php": "roteiro",
      "grupos.php": "roteiro",
      "criar-grupo.php": "roteiro",
      "detalhes-grupo.php": "roteiro",
      "perfil.php": "perfil"
    };
    const active = map[file];
    $$("[data-bottom-nav] .nav-item").forEach((item) => {
      item.classList.toggle("active", item.dataset.tab === active);
    });
  };

  const setupProgramacaoFilters = () => {
    const cards = $$("[data-event-card]");
    if (!cards.length) return;
    let date = "all";
    let palco = "todos";
    const apply = () => {
      cards.forEach((card) => {
        const okDate = date === "all" || card.dataset.date === date;
        const okPalco = palco === "todos" || card.dataset.palco.includes(palco);
        card.classList.toggle("hidden", !(okDate && okPalco));
      });
    };

    $$("[data-programacao-dates] [data-date]").forEach((btn) => {
      btn.addEventListener("click", () => {
        date = btn.dataset.date || "all";
        $$("[data-programacao-dates] [data-date]").forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        apply();
      });
    });

    $$("[data-programacao-palcos] [data-palco]").forEach((btn) => {
      btn.addEventListener("click", () => {
        palco = btn.dataset.palco || "todos";
        $$("[data-programacao-palcos] [data-palco]").forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        apply();
      });
    });
  };

  const setupRestaurantes = () => {
    const input = $("[data-rest-search]");
    const cards = $$("[data-rest-card]");
    if (!input || !cards.length) return;
    let filterOpen = false;

    const toMinutes = (hhmm) => {
      const [h, m] = hhmm.split(":").map(Number);
      return h * 60 + m;
    };

    const apply = () => {
      const term = input.value.toLowerCase().trim();
      const now = new Date();
      const nowMinutes = now.getHours() * 60 + now.getMinutes();

      cards.forEach((card) => {
        const txt = `${card.dataset.restNome} ${card.dataset.restCat}`;
        const okTerm = term === "" || txt.includes(term);
        const openUntil = card.dataset.open || "00:00";
        const openMinutes = toMinutes(openUntil);
        const okOpen = !filterOpen || nowMinutes <= openMinutes;
        card.classList.toggle("hidden", !(okTerm && okOpen));
      });
    };

    input.addEventListener("input", apply);
    const filterBtn = $("[data-filter-open]");
    if (filterBtn) {
      filterBtn.addEventListener("click", () => {
        filterOpen = !filterOpen;
        filterBtn.classList.toggle("active", filterOpen);
        apply();
      });
    }

    const toggle = $("[data-view-toggle]");
    if (toggle) {
      const lista = $("[data-view-lista]");
      const mapa = $("[data-view-mapa]");
      $$("[data-view]", toggle).forEach((btn) => {
        btn.addEventListener("click", () => {
          $$("[data-view]", toggle).forEach((b) => b.classList.remove("active"));
          btn.classList.add("active");
          const view = btn.dataset.view;
          if (lista && mapa) {
            lista.classList.toggle("hidden", view !== "lista");
            mapa.classList.toggle("hidden", view !== "mapa");
          }
        });
      });
    }
  };

  const setupModals = () => {
    $$("[data-open-modal]").forEach((btn) => {
      btn.addEventListener("click", () => {
        const modal = document.getElementById(btn.dataset.openModal);
        if (modal) modal.classList.remove("hidden");
      });
    });
    $$("[data-close-modal]").forEach((el) => {
      el.addEventListener("click", () => el.closest(".modal")?.classList.add("hidden"));
    });
  };

  const setupFriendSelection = () => {
    const input = $("[data-selected-input]");
    const count = $("[data-selected-count]");
    const list = $("[data-friends-list]");
    if (!input || !count || !list) return;
    const selected = new Set();

    const sync = () => {
      input.value = Array.from(selected).join(",");
      count.textContent = String(selected.size);
    };

    $$("[data-friend-id]", list).forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = btn.dataset.friendId;
        if (!id) return;
        if (selected.has(id)) {
          selected.delete(id);
          btn.classList.remove("selected");
          btn.innerHTML = '<i class="fa-solid fa-plus"></i>';
        } else {
          selected.add(id);
          btn.classList.add("selected");
          btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        }
        sync();
      });
    });

    const search = $("[data-friend-search]");
    if (search) {
      search.addEventListener("input", () => {
        const term = search.value.toLowerCase().trim();
        $$("[data-friend-card]", list).forEach((card) => {
          card.classList.toggle("hidden", !card.dataset.name.includes(term));
        });
      });
    }
  };

  const setupPrivacyPicker = () => {
    const picker = $("[data-privacy-picker]");
    if (!picker) return;
    const hidden = $('input[name="privacidade"]');
    $$("[data-value]", picker).forEach((btn) => {
      btn.addEventListener("click", () => {
        $$("[data-value]", picker).forEach((x) => x.classList.remove("active"));
        btn.classList.add("active");
        if (hidden) hidden.value = btn.dataset.value || "publico";
      });
    });
  };

  const setupInviteActions = () => {
    $$("[data-invite-action]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const inviteId = btn.dataset.inviteId;
        const action = btn.dataset.inviteAction === "aceitar" ? "aceitar_convite" : "recusar_convite";
        if (!inviteId) return;
        try {
          const json = await postApi({ action, invite_id: inviteId });
          showToast(json.message);
          btn.closest(".invite-card")?.remove();
        } catch (e) {
          showToast(e.message);
        }
      });
    });
  };

  const setupEnterCode = () => {
    const form = $("#form-enter-code");
    if (!form) return;
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const data = new FormData(form);
      data.append("action", "entrar_com_codigo");
      try {
        const json = await postApi(data, true);
        showToast(json.message);
        if (json.redirect) window.location.href = json.redirect;
      } catch (err) {
        showToast(err.message);
      }
    });
  };

  const setupCreateGroup = () => {
    const form = $("#form-criar-grupo");
    if (!form) return;
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const data = new FormData(form);
      data.append("action", "criar_grupo");
      try {
        const json = await postApi(data, true);
        showToast(json.message);
        if (json.redirect) window.location.href = json.redirect;
      } catch (err) {
        showToast(err.message);
      }
    });
  };

  const setupAlbumActions = () => {
    const grid = $("[data-sticker-grid]");
    if (!grid) return;

    const collect = async (stickerId = "") => {
      try {
        const payload = { action: "coletar_figurinha" };
        if (stickerId) payload.sticker_id = stickerId;
        const json = await postApi(payload);
        showToast("Conquista desbloqueada!");
        const target = $(`[data-sticker-id="${json.sticker_id}"]`);
        if (target) {
          target.classList.remove("locked");
          target.classList.add("unlocked");
          const lock = $(".lock", target);
          if (lock) lock.remove();
        }
      } catch (err) {
        showToast(err.message);
      }
    };

    const takePhoto = $("[data-collect-next]");
    if (takePhoto) takePhoto.addEventListener("click", () => collect());

    $$("[data-sticker-id]", grid).forEach((item) => {
      item.addEventListener("click", () => collect(item.dataset.stickerId));
    });
  };

  const setupFavoritos = () => {
    $$("[data-favorite-id]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.dataset.favoriteId;
        if (!id) return;
        try {
          const json = await postApi({ action: "favoritar_atracao", atracao_id: id });
          const icon = $("i", btn);
          if (icon) {
            icon.classList.toggle("fa-solid", json.state === "adicionado");
            icon.classList.toggle("fa-regular", json.state !== "adicionado");
          }
          showToast(json.message);
        } catch (err) {
          showToast(err.message);
        }
      });
    });
  };

  const setupAddRoteiro = () => {
    $$("[data-add-roteiro]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const data = btn.dataset.addRoteiro;
        if (!data) return;
        const parsed = JSON.parse(data);
        try {
          const json = await postApi({
            action: "adicionar_roteiro",
            tipo: "pessoal",
            horario: parsed.horario,
            titulo: parsed.titulo,
            local: parsed.local,
            categoria: parsed.tipo || "show"
          });
          showToast(json.message);
        } catch (err) {
          showToast(err.message);
        }
      });
    });

    const personalForm = $("#form-add-roteiro");
    if (personalForm) {
      personalForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const fd = new FormData(personalForm);
        fd.append("action", "adicionar_roteiro");
        try {
          const json = await postApi(fd, true);
          showToast(json.message);
          window.location.reload();
        } catch (err) {
          showToast(err.message);
        }
      });
    }

    const groupForm = $("#form-add-group-item");
    if (groupForm) {
      groupForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const fd = new FormData(groupForm);
        fd.append("action", "adicionar_roteiro");
        try {
          const json = await postApi(fd, true);
          showToast(json.message);
          window.location.reload();
        } catch (err) {
          showToast(err.message);
        }
      });
    }
  };

  const setupRemoveRoteiro = () => {
    $$("[data-remove-roteiro]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        try {
          const json = await postApi({
            action: "remover_roteiro",
            roteiro_id: btn.dataset.roteiroId || "",
            item_id: btn.dataset.itemId || ""
          });
          showToast(json.message);
          btn.closest(".roteiro-card")?.parentElement?.remove();
        } catch (err) {
          showToast(err.message);
        }
      });
    });
  };

  const setupRoteiroToggle = () => {
    const root = $("[data-roteiro-toggle]");
    if (!root) return;
    $$("[data-target]", root).forEach((btn) => {
      btn.addEventListener("click", () => {
        $$("[data-target]", root).forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        const target = btn.dataset.target;
        $$("[data-roteiro-panel]").forEach((p) => p.classList.add("hidden"));
        $(`[data-roteiro-panel="${target}"]`)?.classList.remove("hidden");
      });
    });
  };

  const setupProfileEdit = () => {
    const form = $("#form-edit-profile");
    if (!form) return;
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      fd.append("action", "atualizar_perfil");
      try {
        const json = await postApi(fd, true);
        showToast(json.message);
        window.location.reload();
      } catch (err) {
        showToast(err.message);
      }
    });
  };

  const setupToastButtons = () => {
    $$("[data-toast]").forEach((btn) => {
      btn.addEventListener("click", () => showToast(btn.dataset.toast));
    });
  };

  const setupPwaInstall = () => {
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.register("service-worker.js").catch(() => {});
    }

    const postInstallUrl = "https://ki6.com.br/rotatech/";
    let hasRedirectedAfterInstall = false;
    const redirectAfterInstall = () => {
      if (hasRedirectedAfterInstall) return;
      hasRedirectedAfterInstall = true;
      window.location.href = postInstallUrl;
    };

    let deferredPrompt = null;
    window.addEventListener("beforeinstallprompt", (e) => {
      e.preventDefault();
      deferredPrompt = e;
      if ($(".install-banner")) return;
      const banner = document.createElement("div");
      banner.className = "install-banner";
      banner.innerHTML = `
        <div>Instalar app no celular</div>
        <button type="button">Instalar</button>
      `;
      const btn = $("button", banner);
      btn?.addEventListener("click", async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const choice = await deferredPrompt.userChoice;
        deferredPrompt = null;
        banner.remove();
        if (choice?.outcome === "accepted") {
          redirectAfterInstall();
        }
      });
      document.body.appendChild(banner);
    });

    window.addEventListener("appinstalled", () => {
      redirectAfterInstall();
    });
  };

  document.addEventListener("DOMContentLoaded", () => {
    markActiveBottomTab();
    setupProgramacaoFilters();
    setupRestaurantes();
    setupModals();
    setupFriendSelection();
    setupPrivacyPicker();
    setupInviteActions();
    setupEnterCode();
    setupCreateGroup();
    setupAlbumActions();
    setupFavoritos();
    setupAddRoteiro();
    setupRemoveRoteiro();
    setupRoteiroToggle();
    setupProfileEdit();
    setupToastButtons();
    setupPwaInstall();
  });
})();
