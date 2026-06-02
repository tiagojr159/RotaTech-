(() => {
  const $ = (s, root = document) => root.querySelector(s);
  const $$ = (s, root = document) => Array.from(root.querySelectorAll(s));
  const THEME_KEY = "rotatech-theme";
  const VOICE_KEY = "rotatech-voice-enabled";
  const IOS_INSTALL_DISMISSED_KEY = "rotatech-ios-install-dismissed";
  const getAppBaseUrl = () => {
    if (window.APP_BASE_URL) return window.APP_BASE_URL;
    const segments = window.location.pathname.split("/").filter(Boolean);
    return segments.length ? `/${segments[0]}/` : "/";
  };
  const getAppAbsoluteUrl = () => window.APP_ABSOLUTE_URL || `${window.location.origin}${getAppBaseUrl()}`;

  const isVoiceEnabled = () => {
    try {
      return (localStorage.getItem(VOICE_KEY) || "on") !== "off";
    } catch (_) {
      return true;
    }
  };

  const applyVoicePreference = (enabled) => {
    const value = enabled ? "on" : "off";
    try {
      localStorage.setItem(VOICE_KEY, value);
    } catch (_) {}

    $$("[data-voice-option]").forEach((button) => {
      const shouldBeActive = button.dataset.voiceOption === value;
      button.classList.toggle("active", shouldBeActive);
    });
  };

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
      "": "home",
      "index.php": "home",
      "home.php": "home",
      "programacao.php": "explorar",
      "restaurantes.php": "explorar",
      "hospedagem.php": "explorar",
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

  const applyTheme = (theme) => {
    const nextTheme = theme === "dark" ? "dark" : "normal";
    document.body.dataset.theme = nextTheme;
    try {
      localStorage.setItem(THEME_KEY, nextTheme);
    } catch (_) {}

    $$("[data-theme-option]").forEach((button) => {
      button.classList.toggle("active", button.dataset.themeOption === nextTheme);
    });
  };

  const setupThemeControls = () => {
    let savedTheme = "normal";
    try {
      savedTheme = localStorage.getItem(THEME_KEY) || "normal";
    } catch (_) {}

    applyTheme(savedTheme);
    $$("[data-theme-option]").forEach((button) => {
      button.addEventListener("click", () => {
        applyTheme(button.dataset.themeOption || "normal");
      });
    });
  };

  const setupVoiceControls = () => {
    applyVoicePreference(isVoiceEnabled());
    $$("[data-voice-option]").forEach((button) => {
      button.addEventListener("click", () => {
        const enabled = (button.dataset.voiceOption || "on") === "on";
        applyVoicePreference(enabled);

        if (!enabled) {
          try {
            if ("speechSynthesis" in window) {
              window.speechSynthesis.cancel();
            }
          } catch (_) {}
        }
      });
    });
  };

  const setupPasswordToggles = () => {
    $$("[data-password-button]").forEach((button) => {
      const wrapper = button.closest(".input-icon");
      const input = wrapper?.querySelector("[data-password-toggle]");
      const icon = $("i", button);
      if (!input || !icon) return;

      button.addEventListener("click", () => {
        const showing = input.type === "text";
        input.type = showing ? "password" : "text";
        button.setAttribute("aria-pressed", String(!showing));
        button.setAttribute("aria-label", showing ? "Mostrar senha" : "Ocultar senha");
        icon.className = showing ? "fa-regular fa-eye" : "fa-regular fa-eye-slash";
      });
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

  const setupVoiceGuides = () => {
    const buttons = $$("[data-voice-trigger]");
    if (!buttons.length) return;

    let activeButton = null;
    let activeUtterance = null;
    const player = new Audio();
    player.preload = "none";

    const clearState = () => {
      if (activeButton) {
        activeButton.disabled = false;
        activeButton.classList.remove("is-playing");
      }
      activeButton = null;
      activeUtterance = null;
    };

    const stopAll = () => {
      try {
        player.pause();
        player.currentTime = 0;
        player.removeAttribute("src");
      } catch (_) {}

      try {
        if ("speechSynthesis" in window) {
          window.speechSynthesis.cancel();
        }
      } catch (_) {}

      clearState();
    };

    const findPreferredBrowserVoice = () => {
      if (!("speechSynthesis" in window)) {
        return null;
      }

      const voices = window.speechSynthesis.getVoices() || [];
      const preferredPatterns = [/antonio/i, /ant[oô]nio/i, /fabio/i, /ricardo/i];

      for (const pattern of preferredPatterns) {
        const matched = voices.find((voice) => pattern.test(`${voice.name} ${voice.voiceURI}`));
        if (matched) {
          return matched;
        }
      }

      return voices.find((voice) => /^pt-BR/i.test(voice.lang))
        || voices.find((voice) => /^pt/i.test(voice.lang))
        || null;
    };

    const speakFallback = (button) => {
      const text = button.dataset.voiceText || "";
      if (!text || !("speechSynthesis" in window)) {
        return false;
      }

      const utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = "pt-BR";
      utterance.rate = 0.96;
      utterance.pitch = 1;
      const preferredVoice = findPreferredBrowserVoice();
      if (preferredVoice) {
        utterance.voice = preferredVoice;
      }
      utterance.onend = clearState;
      utterance.onerror = clearState;
      activeUtterance = utterance;
      window.speechSynthesis.cancel();
      window.speechSynthesis.speak(utterance);
      return true;
    };

    const playVoice = async (button, autoplay = false) => {
      if (!isVoiceEnabled()) {
        if (!autoplay) {
          showToast("A voz esta desligada no perfil.");
        }
        return;
      }

      if (activeButton === button) {
        stopAll();
        return;
      }

      stopAll();
      activeButton = button;
      activeButton.disabled = true;
      activeButton.classList.add("is-playing");

      const params = new URLSearchParams({
        context: button.dataset.voiceContext || "",
        _: String(Date.now()),
      });

      if (button.dataset.voiceScope) {
        params.set("scope", button.dataset.voiceScope);
      }

      player.src = `${getAppBaseUrl()}voice.php?${params.toString()}`;

      try {
        await player.play();
        activeButton.disabled = false;
      } catch (_) {
        const usedFallback = speakFallback(button);
        activeButton.disabled = false;

        if (!usedFallback && !autoplay) {
          showToast("Nao foi possivel iniciar o audio agora.");
          clearState();
        }

        if (!usedFallback && autoplay) {
          showToast("Toque em ouvir para reproduzir a narracao.");
          clearState();
        }
      }
    };

    player.addEventListener("ended", clearState);
    player.addEventListener("error", () => {
      if (!activeButton) return;
      const button = activeButton;
      const usedFallback = speakFallback(button);
      if (!usedFallback) {
        showToast("Nao foi possivel carregar a narracao.");
        clearState();
      }
    });

    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        playVoice(button, false);
      });
    });

    const autoplayButton = $("[data-voice-autoplay='true']");
    if (autoplayButton) {
      window.setTimeout(() => {
        playVoice(autoplayButton, true);
      }, 800);
    }
  };

  const setupAlbumPhotoModal = () => {
    const photoModal = $("#modal-album-photo");
    const photoPreview = $("#album-photo-preview");
    const photoLoading = $("#album-photo-loading");
    if (!photoModal || !photoPreview || !photoLoading) return;

    $$("[data-open-album-photo]").forEach((trigger) => {
      trigger.addEventListener("click", () => {
        const originalSrc = trigger.dataset.albumPhotoSrc || "";
        const separator = originalSrc.includes("?") ? "&" : "?";
        const previewSrc = `${originalSrc}${separator}modal=1&t=${Date.now()}`;

        photoLoading.classList.remove("hidden");
        photoPreview.classList.add("hidden");
        photoPreview.alt = trigger.dataset.albumPhotoAlt || "Foto do album";
        photoPreview.src = previewSrc;
      });
    });

    photoPreview.addEventListener("load", () => {
      photoLoading.classList.add("hidden");
      photoPreview.classList.remove("hidden");
    });

    photoPreview.addEventListener("error", () => {
      photoLoading.textContent = "Nao foi possivel carregar a foto ampliada.";
      photoLoading.classList.remove("hidden");
      photoPreview.classList.add("hidden");
    });

    $$("[data-close-modal]", photoModal).forEach((button) => {
      button.addEventListener("click", () => {
        photoModal.classList.add("hidden");
        photoLoading.textContent = "Carregando foto...";
        photoLoading.classList.remove("hidden");
        photoPreview.classList.add("hidden");
        photoPreview.removeAttribute("src");
        photoPreview.alt = "";
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

  const setupHospedagem = () => {
    const input = $("[data-hosp-search]");
    const cards = $$("[data-hosp-card]");
    if (!input || !cards.length) return;

    let cat = "todos";
    const apply = () => {
      const term = input.value.toLowerCase().trim();
      cards.forEach((card) => {
        const name = card.dataset.hospNome || "";
        const category = card.dataset.hospCat || "";
        const address = card.dataset.hospEndereco || "";
        const okTerm = term === "" || `${name} ${address}`.includes(term);
        const okCat = cat === "todos" || category === cat;
        card.classList.toggle("hidden", !(okTerm && okCat));
      });
    };

    input.addEventListener("input", apply);
    $$("[data-hosp-filter]").forEach((btn) => {
      btn.addEventListener("click", () => {
        cat = btn.dataset.hospFilter || "todos";
        $$("[data-hosp-filter]").forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        apply();
      });
    });
  };

  const setupAdminPanel = () => {
    const panel = $("[data-admin-panel]");
    if (!panel) return;

    $$("[data-edit-user]").forEach((button) => {
      button.addEventListener("click", () => {
        const form = $("#form-edit-user");
        if (!form) return;
        form.elements.id.value = button.dataset.id || "0";
        form.elements.nome.value = button.dataset.nome || "";
        form.elements.usuario.value = button.dataset.usuario || "";
        form.elements.email.value = button.dataset.email || "";
        form.elements.titulo.value = button.dataset.titulo || "NOVO USUARIO";
        form.elements.avatar_atual.value = button.dataset.avatar || "";
        form.elements.is_admin.checked = button.dataset.isAdmin === "1";
        const preview = $("[data-user-preview]", form);
        if (preview) preview.src = button.dataset.avatar || "assets/img/avatar-default.svg";
      });
    });

    $$("[data-edit-hospedagem]").forEach((button) => {
      button.addEventListener("click", () => {
        const form = $("#form-edit-hospedagem");
        if (!form) return;
        form.elements.id.value = button.dataset.id || "0";
        form.elements.nome.value = button.dataset.nome || "";
        form.elements.categoria.value = button.dataset.categoria || "hotel";
        form.elements.endereco.value = button.dataset.endereco || "";
        form.elements.cidade.value = button.dataset.cidade || "Arcoverde, Pernambuco";
        form.elements.latitude.value = button.dataset.latitude || "";
        form.elements.longitude.value = button.dataset.longitude || "";
        form.elements.imagem_atual.value = button.dataset.imagem || "";
      });
    });

    $$("[data-edit-restaurante]").forEach((button) => {
      button.addEventListener("click", () => {
        const form = $("#form-edit-restaurante");
        if (!form) return;
        form.elements.id.value = button.dataset.id || "0";
        form.elements.nome.value = button.dataset.nome || "";
        form.elements.categoria.value = button.dataset.categoria || "";
        form.elements.distancia.value = button.dataset.distancia || "";
        form.elements.avaliacao.value = button.dataset.avaliacao || "4.5";
        form.elements.faixa_preco.value = button.dataset.faixa || "$$";
        form.elements.aberto_ate.value = button.dataset.aberto || "23:00";
        form.elements.prato_destaque.value = button.dataset.prato || "";
        form.elements.preco_prato.value = button.dataset.preco || "";
        form.elements.descricao.value = button.dataset.descricao || "";
      });
    });

    $$("[data-edit-evento]").forEach((button) => {
      button.addEventListener("click", () => {
        const form = $("#form-edit-evento");
        if (!form) return;
        form.elements.id.value = button.dataset.id || "0";
        form.elements.artista.value = button.dataset.artista || "";
        form.elements.palco.value = button.dataset.palco || "";
        form.elements.data.value = button.dataset.data || "";
        form.elements.horario.value = button.dataset.horario || "";
        form.elements.categoria.value = button.dataset.categoria || "Show";
        form.elements.status.value = button.dataset.status || "em_breve";
        form.elements.descricao.value = button.dataset.descricao || "";
      });
    });

    $("[data-print-report]")?.addEventListener("click", () => {
      document.body.classList.add("report-print-mode");
      window.print();
    });

    window.addEventListener("afterprint", () => {
      document.body.classList.remove("report-print-mode");
    });
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

    const uploadForm = $("#form-upload-album-photo");
    const uploadInput = $("#album-photo-input");
    if (uploadForm && uploadInput) {
      uploadInput.addEventListener("change", async () => {
        if (!uploadInput.files?.length) return;
        const fd = new FormData(uploadForm);
        try {
          const json = await postApi(fd, true);
          showToast(json.message);
          window.location.reload();
        } catch (err) {
          showToast(err.message);
        } finally {
          uploadInput.value = "";
        }
      });
    }

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
      const searchInput = $("#group-user-search", groupForm);
      const hiddenUserId = $("#group-user-id", groupForm);
      const results = $("#group-user-results", groupForm);
      const selectedBox = $("#group-user-selected", groupForm);
      const selectedName = $("[data-selected-user-name]", groupForm);
      const selectedHandle = $("[data-selected-user-handle]", groupForm);
      const clearSelected = $("[data-clear-group-user]", groupForm);
      const options = $$("[data-group-user-option]", groupForm);

      const renderResults = () => {
        const term = (searchInput?.value || "").trim().toLowerCase();
        let visibleCount = 0;
        options.forEach((option) => {
          const haystack = `${option.dataset.userName || ""} ${option.dataset.userHandle || ""}`.toLowerCase();
          const match = term === "" || haystack.includes(term);
          option.classList.toggle("hidden", !match);
          if (match) visibleCount += 1;
        });
        results?.classList.toggle("hidden", visibleCount === 0 || !!hiddenUserId.value);
      };

      const selectUser = (option) => {
        hiddenUserId.value = option.dataset.userId || "";
        if (searchInput) {
          searchInput.value = option.dataset.userName || "";
        }
        if (selectedName) {
          selectedName.textContent = option.dataset.userName || "";
        }
        if (selectedHandle) {
          selectedHandle.textContent = `@${option.dataset.userHandle || ""}`;
        }
        selectedBox?.classList.remove("hidden");
        renderResults();
      };

      const clearUser = () => {
        hiddenUserId.value = "";
        if (searchInput) {
          searchInput.value = "";
          searchInput.focus();
        }
        selectedBox?.classList.add("hidden");
        renderResults();
      };

      searchInput?.addEventListener("focus", renderResults);
      searchInput?.addEventListener("input", () => {
        hiddenUserId.value = "";
        selectedBox?.classList.add("hidden");
        renderResults();
      });
      clearSelected?.addEventListener("click", clearUser);

      options.forEach((option) => {
        option.addEventListener("click", () => selectUser(option));
      });

      renderResults();

      groupForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!hiddenUserId?.value) {
          showToast("Escolha um usuario da lista para compartilhar.");
          searchInput?.focus();
          return;
        }
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

  const setupChatbot = () => {
    const widget = $("[data-chatbot-widget]");
    if (!widget) return;

    const panel = $("[data-chatbot-panel]", widget) || $("#chatbot-panel", widget);
    const toggle = $("[data-chatbot-toggle]", widget);
    const close = $("[data-chatbot-close]", widget);
    const form = $("[data-chatbot-form]", widget);
    const messages = $("[data-chatbot-messages]", widget);
    const input = $('input[name="message"]', widget);
    if (!panel || !toggle || !form || !messages || !input) return;

    const setOpen = (open) => {
      panel.classList.toggle("hidden", !open);
      toggle.setAttribute("aria-expanded", String(open));
      if (open) input.focus();
    };

    const appendMessage = (text, sender) => {
      const message = document.createElement("p");
      message.className = `chatbot-message ${sender}`;
      message.textContent = text;
      messages.appendChild(message);
      messages.scrollTop = messages.scrollHeight;
      return message;
    };

    toggle.addEventListener("click", () => {
      setOpen(panel.classList.contains("hidden"));
    });
    close?.addEventListener("click", () => setOpen(false));

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      const text = input.value.trim();
      if (!text) return;

      appendMessage(text, "user");
      input.value = "";
      input.disabled = true;
      const loading = appendMessage("Consultando...", "bot loading");

      try {
        const response = await fetch("chat.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ message: text }),
        });
        const json = await response.json();
        if (!response.ok || json.ok === false) {
          throw new Error(json.message || "Nao foi possivel consultar o guia.");
        }
        loading.textContent = json.reply;
        loading.classList.remove("loading");
      } catch (error) {
        loading.textContent = error.message || "Nao foi possivel consultar o guia agora.";
        loading.classList.remove("loading");
      } finally {
        input.disabled = false;
        input.focus();
      }
    });
  };

  const setupLocationTracker = () => {
    if (!$("[data-location-tracker]") || !navigator.geolocation) return;

    const storageKey = "rotatech-location-saved-at";
    try {
      const lastSavedAt = Number(localStorage.getItem(storageKey) || 0);
      if (Date.now() - lastSavedAt < 15 * 60 * 1000) return;
    } catch (_) {}

    navigator.geolocation.getCurrentPosition(async (position) => {
      try {
        await postApi({
          action: "registrar_localizacao",
          latitude: String(position.coords.latitude),
          longitude: String(position.coords.longitude),
          precisao: String(position.coords.accuracy || ""),
        });
        try {
          localStorage.setItem(storageKey, String(Date.now()));
        } catch (_) {}
      } catch (_) {}
    }, () => {}, {
      enableHighAccuracy: false,
      timeout: 10000,
      maximumAge: 5 * 60 * 1000,
    });
  };

  const setupNotifications = () => {
    const widget = $("[data-notification-widget]");
    if (!widget) return;

    const toggle = $("[data-notification-toggle]", widget);
    const close = $("[data-notification-close]", widget);
    const panel = $("#notification-panel", widget);
    const list = $("[data-notification-list]", widget);
    const count = $("[data-notification-count]", widget);
    if (!toggle || !panel || !list || !count) return;

    let audioContext = null;
    const getAudioContext = () => {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (!AudioContext) return null;
      audioContext ||= new AudioContext();
      return audioContext;
    };

    const unlockAudio = () => {
      const context = getAudioContext();
      context?.resume?.().catch(() => {});
    };
    document.addEventListener("pointerdown", unlockAudio, { once: true });

    const playAlertSound = async () => {
      const context = getAudioContext();
      if (!context) return false;
      try {
        await context.resume();
        if (context.state !== "running") return false;

        [0, 0.2, 0.4].forEach((delay) => {
          const oscillator = context.createOscillator();
          const gain = context.createGain();
          oscillator.type = "sine";
          oscillator.frequency.setValueAtTime(880, context.currentTime + delay);
          gain.gain.setValueAtTime(0.0001, context.currentTime + delay);
          gain.gain.exponentialRampToValueAtTime(0.2, context.currentTime + delay + 0.025);
          gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + delay + 0.16);
          oscillator.connect(gain);
          gain.connect(context.destination);
          oscillator.start(context.currentTime + delay);
          oscillator.stop(context.currentTime + delay + 0.18);
        });
        return true;
      } catch (_) {
        return false;
      }
    };

    const updateCount = (unreadCount) => {
      count.textContent = String(unreadCount);
      count.classList.toggle("hidden", unreadCount < 1);
    };

    const markAsRead = async (notificationId, item) => {
      if (item.classList.contains("is-read")) return;
      try {
        await postApi({ action: "mark_notification_read", notification_id: String(notificationId) });
        item.classList.add("is-read");
        updateCount(Math.max(0, Number(count.textContent || 0) - 1));
      } catch (_) {}
    };

    const renderNotifications = (notifications) => {
      list.replaceChildren();
      if (!notifications.length) {
        const empty = document.createElement("p");
        empty.className = "notification-empty";
        empty.textContent = "Nenhuma notificacao por enquanto.";
        list.appendChild(empty);
        return;
      }

      notifications.forEach((notification) => {
        const item = document.createElement("button");
        const title = document.createElement("strong");
        const description = document.createElement("span");
        const date = document.createElement("small");
        item.type = "button";
        item.className = `notification-item${notification.lida ? " is-read" : ""}`;
        title.textContent = notification.titulo || "Alerta";
        description.textContent = notification.descricao || "";
        date.textContent = notification.created_at_label || "Aviso do sistema";
        item.append(title, description, date);
        item.addEventListener("click", () => markAsRead(notification.id, item));
        list.appendChild(item);
      });
    };

    const refreshNotifications = async () => {
      try {
        const json = await postApi({ action: "notification_poll" });
        const notifications = json.notifications || [];
        updateCount(Number(json.unread_count || 0));
        renderNotifications(notifications);

        const soundNotifications = notifications.filter((notification) => notification.should_sound);
        if (soundNotifications.length && await playAlertSound()) {
          await Promise.all(soundNotifications.map((notification) => postApi({
            action: "mark_notification_sound_played",
            notification_id: String(notification.id),
          }).catch(() => null)));
        }
      } catch (_) {}
    };

    const setOpen = (open) => {
      panel.classList.toggle("hidden", !open);
      toggle.setAttribute("aria-expanded", String(open));
      if (open) refreshNotifications();
    };

    toggle.addEventListener("click", () => {
      unlockAudio();
      setOpen(panel.classList.contains("hidden"));
    });
    close?.addEventListener("click", () => setOpen(false));
    document.addEventListener("visibilitychange", () => {
      if (!document.hidden) refreshNotifications();
    });

    refreshNotifications();
    window.setInterval(refreshNotifications, 30000);
  };

  const setupAdminChatAlerts = () => {
    if (!window.IS_ADMIN_USER) return;

    let audioContext = null;
    const getAudioContext = () => {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (!AudioContext) return null;
      audioContext ||= new AudioContext();
      return audioContext;
    };

    const unlockAudio = () => {
      const context = getAudioContext();
      context?.resume?.().catch(() => {});
    };
    document.addEventListener("pointerdown", unlockAudio, { once: true });

    const playAdminChatSound = async () => {
      const context = getAudioContext();
      if (!context) return false;
      try {
        await context.resume();
        if (context.state !== "running") return false;

        [0, 0.16, 0.32, 0.48].forEach((delay, index) => {
          const oscillator = context.createOscillator();
          const gain = context.createGain();
          oscillator.type = "triangle";
          oscillator.frequency.setValueAtTime(index % 2 === 0 ? 740 : 980, context.currentTime + delay);
          gain.gain.setValueAtTime(0.0001, context.currentTime + delay);
          gain.gain.exponentialRampToValueAtTime(0.24, context.currentTime + delay + 0.02);
          gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + delay + 0.13);
          oscillator.connect(gain);
          gain.connect(context.destination);
          oscillator.start(context.currentTime + delay);
          oscillator.stop(context.currentTime + delay + 0.15);
        });
        return true;
      } catch (_) {
        return false;
      }
    };

    const pollAdminChatAlerts = async () => {
      try {
        const json = await postApi({ action: "admin_chat_alert_poll" });
        const alerts = json.alerts || [];
        if (!alerts.length) return;

        const latestAlert = alerts[alerts.length - 1];
        showToast(`Nova mensagem no chatbot: ${latestAlert.user_name}`);
        if (!await playAdminChatSound()) return;

        await Promise.all(alerts.map((alert) => postApi({
          action: "mark_admin_chat_alert_delivered",
          alert_id: String(alert.id),
        }).catch(() => null)));
      } catch (_) {}
    };

    pollAdminChatAlerts();
    window.setInterval(pollAdminChatAlerts, 10000);
  };

  const setupAdminAccessMap = () => {
    const element = $("[data-admin-access-map]");
    if (!element || !window.L) return;

    let locations = [];
    try {
      locations = JSON.parse(element.dataset.locations || "[]");
    } catch (_) {}

    const map = window.L.map(element).setView([-8.4333, -37.0667], 14);
    window.L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "&copy; OpenStreetMap",
    }).addTo(map);

    const markers = [];
    const coordinateTotals = new Map();
    const coordinateUses = new Map();
    locations.forEach((location) => {
      const latitude = Number(location.latitude);
      const longitude = Number(location.longitude);
      if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) return;
      const key = `${latitude.toFixed(6)},${longitude.toFixed(6)}`;
      coordinateTotals.set(key, (coordinateTotals.get(key) || 0) + 1);
    });

    locations.forEach((location) => {
      const latitude = Number(location.latitude);
      const longitude = Number(location.longitude);
      if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) return;

      const coordinateKey = `${latitude.toFixed(6)},${longitude.toFixed(6)}`;
      const totalAtCoordinate = coordinateTotals.get(coordinateKey) || 1;
      const indexAtCoordinate = coordinateUses.get(coordinateKey) || 0;
      coordinateUses.set(coordinateKey, indexAtCoordinate + 1);
      const angle = (Math.PI * 2 * indexAtCoordinate) / totalAtCoordinate;
      const radius = totalAtCoordinate > 1 ? 0.00009 : 0;
      const markerLatitude = latitude + (Math.sin(angle) * radius);
      const markerLongitude = longitude + (Math.cos(angle) * radius);

      const content = document.createElement("div");
      const name = document.createElement("strong");
      const updated = document.createElement("small");
      name.textContent = location.user_name || "Visitante";
      updated.textContent = location.updated_at_label || "";
      content.append(name, document.createElement("br"), updated);

      const marker = window.L.marker([markerLatitude, markerLongitude]).addTo(map).bindPopup(content);
      markers.push(marker);
    });

    if (markers.length) {
      const group = window.L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.25), { maxZoom: 16 });
    }
  };

  const setupPwaInstall = () => {
    const appBaseUrl = getAppBaseUrl();
    const postInstallUrl = getAppAbsoluteUrl();
    const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent || "");
    const isInStandalone = window.matchMedia?.("(display-mode: standalone)")?.matches || window.navigator.standalone === true;

    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.register(`${appBaseUrl}service-worker.js`, {
        scope: appBaseUrl,
      }).catch(() => {});
    }

    let hasRedirectedAfterInstall = false;
    const redirectAfterInstall = () => {
      if (hasRedirectedAfterInstall) return;
      hasRedirectedAfterInstall = true;
      window.location.href = postInstallUrl;
    };

    const removeInstallBanner = () => {
      $(".install-banner")?.remove();
    };

    const showIosInstallBanner = () => {
      try {
        if (localStorage.getItem(IOS_INSTALL_DISMISSED_KEY) === "1") return;
      } catch (_) {}
      if ($(".install-banner")) return;

      const banner = document.createElement("div");
      banner.className = "install-banner install-banner-ios";
      banner.innerHTML = `
        <div class="install-banner-copy">
          <strong>Instalar no iPhone</strong>
          <span>Toque em Compartilhar no Safari e depois em Adicionar à Tela de Início.</span>
        </div>
        <div class="install-banner-actions">
          <button type="button" data-dismiss-install>Fechar</button>
        </div>
      `;

      $("[data-dismiss-install]", banner)?.addEventListener("click", () => {
        try {
          localStorage.setItem(IOS_INSTALL_DISMISSED_KEY, "1");
        } catch (_) {}
        banner.remove();
      });

      document.body.appendChild(banner);
    };

    if (isIos && !isInStandalone) {
      showIosInstallBanner();
    }

    let deferredPrompt = null;
    window.addEventListener("beforeinstallprompt", (e) => {
      e.preventDefault();
      deferredPrompt = e;
      removeInstallBanner();
      const banner = document.createElement("div");
      banner.className = "install-banner";
      banner.innerHTML = `
        <div class="install-banner-copy">
          <strong>Instalar app</strong>
          <span>Adicione o RotaTech na tela inicial do seu celular.</span>
        </div>
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
    setupThemeControls();
    setupVoiceControls();
    setupProgramacaoFilters();
    setupVoiceGuides();
    setupAlbumPhotoModal();
    setupRestaurantes();
    setupHospedagem();
    setupAdminPanel();
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
    setupPasswordToggles();
    setupChatbot();
    setupNotifications();
    setupAdminChatAlerts();
    setupLocationTracker();
    setupAdminAccessMap();
    setupPwaInstall();
  });
})();
