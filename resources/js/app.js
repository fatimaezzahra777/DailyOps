const menuToggle = document.getElementById('menu-btn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const modalRoots = Array.from(document.querySelectorAll('[data-modal]'));
let activeModal = null;

const resetModalFields = (modal) => {
    if (!modal || modal.dataset.resetOnOpen !== 'true') {
        return;
    }

    const form = modal.querySelector('form');
    form?.reset();

    modal.querySelectorAll('[data-field-default]').forEach((field) => {
        field.value = field.dataset.fieldDefault ?? '';

        if (field.tagName === 'SELECT') {
            field.dispatchEvent(new Event('change', { bubbles: true }));
            return;
        }

        field.dispatchEvent(new Event('input', { bubbles: true }));
    });
};

document.documentElement.classList.remove('dark');
localStorage.setItem('theme', 'light');


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
        if (modal.id === id) {
            resetModalFields(modal);
        }

        setModalState(modal, modal.id === id);

        if (modal.id === id && modal.dataset.resetOnOpen === 'true') {
            requestAnimationFrame(() => resetModalFields(modal));
            setTimeout(() => resetModalFields(modal), 0);
        }
    });
};

const closeAllModals = () => {
    modalRoots.forEach((modal) => {
        setModalState(modal, false);
        resetModalFields(modal);
    });
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

import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
