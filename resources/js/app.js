const bindPasswordToggles = () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);

            if (!input) {
                return;
            }

            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.innerHTML = `<iconify-icon icon="lucide:${visible ? 'eye' : 'eye-off'}"></iconify-icon>`;
            button.setAttribute('aria-label', visible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        });
    });
};

const bindMenus = () => {
    document.querySelectorAll('[data-mobile-menu-toggle], [data-landing-menu-toggle]').forEach((toggle) => {
        const panel = document.getElementById(toggle.getAttribute('aria-controls'));

        if (!panel) {
            return;
        }

        const icon = toggle.querySelector('[data-mobile-menu-icon], [data-landing-menu-icon]');
        const closeMenu = () => {
            panel.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Buka menu navigasi');
            icon?.setAttribute('icon', 'lucide:menu');
            document.body.classList.remove('overflow-hidden');
        };

        toggle.addEventListener('click', () => {
            const isOpen = toggle.getAttribute('aria-expanded') === 'true';

            if (isOpen) {
                closeMenu();

                return;
            }

            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', 'Tutup menu navigasi');
            icon?.setAttribute('icon', 'lucide:x');
            document.body.classList.add('overflow-hidden');
        });

        panel.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    });
};

const bindLogoutConfirmation = () => {
    document.querySelectorAll('[data-logout-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitted === 'true') {
                event.preventDefault();

                return;
            }

            if (!window.confirm('Keluar dari CBT.EDUKA?')) {
                event.preventDefault();

                return;
            }

            form.dataset.submitted = 'true';
            const button = form.querySelector('button[type="submit"]');
            button?.setAttribute('disabled', 'disabled');
            button?.setAttribute('aria-busy', 'true');
            button?.classList.add('cursor-wait', 'opacity-60');
        });
    });
};

const bindLandingSectionState = () => {
    const links = [...document.querySelectorAll('[data-landing-nav-link]')];
    const sections = links
        .map((link) => document.getElementById(link.getAttribute('href')?.slice(1)))
        .filter(Boolean);

    if (!links.length || !sections.length || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            links.forEach((link) => link.dataset.active = link.getAttribute('href') === `#${entry.target.id}` ? 'true' : 'false');
        });
    }, { rootMargin: '-35% 0px -55% 0px', threshold: 0 });

    sections.forEach((section) => observer.observe(section));
};

const bindAiDraftEditor = () => {
    const form = document.querySelector('[data-ai-save-form]');

    if (!form) {
        return;
    }

    const serializeDraft = (draft) => {
        const definitionField = draft.querySelector('[data-ai-definition]');
        let definition = {};

        try {
            definition = JSON.parse(definitionField.value || '{}');
        } catch {
            definitionField.classList.add('border-error');
            throw new Error('Definition JSON belum valid.');
        }

        definitionField.classList.remove('border-error');

        return {
            subject_id: Number(draft.querySelector('[data-ai-subject-id]').value),
            type: draft.querySelector('[data-ai-type]').value,
            prompt: draft.querySelector('[data-ai-prompt]').value,
            options: [...draft.querySelectorAll('[data-ai-option]')].map((option) => ({
                key: option.querySelector('[data-ai-option-key]').value,
                label: option.querySelector('[data-ai-option-label]').value,
                is_correct: option.querySelector('[data-ai-option-correct]').checked,
            })),
            definition,
            explanation: draft.querySelector('[data-ai-explanation]').value,
            points: Number(draft.querySelector('[data-ai-points]').value),
            difficulty: 'medium',
        };
    };

    const syncPayload = () => {
        const selected = [...form.querySelectorAll('[data-ai-draft]')]
            .filter((draft) => draft.querySelector('[data-ai-selected]').checked)
            .map(serializeDraft);

        form.querySelector('[data-ai-payload]').value = JSON.stringify(selected);

        return selected;
    };

    form.querySelectorAll('[data-ai-remove]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('[data-ai-draft]')?.remove();
        });
    });

    form.addEventListener('submit', (event) => {
        try {
            const selected = syncPayload();

            if (!selected.length) {
                event.preventDefault();
                window.alert('Pilih minimal satu draft soal untuk disimpan.');
            }
        } catch (error) {
            event.preventDefault();
            window.alert(error.message);
        }
    });
};

bindPasswordToggles();
bindMenus();
bindLogoutConfirmation();
bindLandingSectionState();
bindAiDraftEditor();
