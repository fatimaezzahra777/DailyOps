const html = document.documentElement;
const themeToggle = document.getElementById('theme-toggle');
const menuToggle = document.getElementById('menu-btn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const modalRoots = Array.from(document.querySelectorAll('[data-modal]'));
let activeModal = null;

const applyTheme = (theme) => {
    const isDark = theme === 'dark';
    html.classList.toggle('dark', isDark);
    localStorage.setItem('theme', theme);

    if (themeToggle) {
        themeToggle.setAttribute('aria-pressed', String(isDark));
        const label = themeToggle.querySelector('[data-theme-label]');
        if (label) {
            label.textContent = isDark ? 'Dark' : 'Light';
        }
    }
};

const savedTheme = localStorage.getItem('theme');
const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
applyTheme(savedTheme ?? (systemPrefersDark ? 'dark' : 'light'));

themeToggle?.addEventListener('click', () => {
    applyTheme(html.classList.contains('dark') ? 'light' : 'dark');
});

const closeSidebar = () => {
    sidebar?.classList.add('-translate-x-full');
    sidebarOverlay?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
};

const openSidebar = () => {
    sidebar?.classList.remove('-translate-x-full');
    sidebarOverlay?.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

menuToggle?.addEventListener('click', () => {
    if (!sidebar) {
        return;
    }

    if (sidebar.classList.contains('-translate-x-full')) {
        openSidebar();
        return;
    }

    closeSidebar();
});

sidebarOverlay?.addEventListener('click', closeSidebar);

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        sidebarOverlay?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        sidebar?.classList.remove('-translate-x-full');
        return;
    }

    closeSidebar();
});

const setModalState = (modal, open) => {
    if (!modal) {
        return;
    }

    modal.classList.toggle('hidden', !open);
    modal.setAttribute('aria-hidden', String(!open));

    if (open) {
        activeModal = modal;
        document.body.classList.add('overflow-hidden');
        modal.querySelector('input, textarea, select, button')?.focus();
        return;
    }

    if (activeModal === modal) {
        activeModal = null;
    }

    if (!modalRoots.some((item) => !item.classList.contains('hidden'))) {
        document.body.classList.remove('overflow-hidden');
    }
};

const openModalById = (id) => {
    modalRoots.forEach((modal) => {
        setModalState(modal, modal.id === id);
    });
};

const closeAllModals = () => {
    modalRoots.forEach((modal) => setModalState(modal, false));
};

document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        openModalById(trigger.dataset.modalOpen);
    });
});

document.querySelectorAll('[data-modal-close]').forEach((trigger) => {
    trigger.addEventListener('click', () => closeAllModals());
});

document.querySelectorAll('[data-modal-switch]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        openModalById(trigger.dataset.modalSwitch);
    });
});

window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeAllModals();
    }
});
