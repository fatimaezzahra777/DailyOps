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

const applyCreateProjectDefaults = (trigger) => {
    if (trigger.dataset.modalOpen !== 'create-project-modal') {
        return;
    }

    const statusField = document.getElementById('create-project-status');
    const columnField = document.getElementById('create-project-column-id');

    if (statusField && trigger.dataset.createStatus) {
        statusField.value = trigger.dataset.createStatus;
        statusField.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (columnField) {
        columnField.value = trigger.dataset.createColumnId || '';
        columnField.dispatchEvent(new Event('change', { bubbles: true }));
    }
};

const closeAllModals = () => {
    modalRoots.forEach((modal) => {
        setModalState(modal, false);
        resetModalFields(modal);
    });
};

const setupBoardDragAndDrop = () => {
    const board = document.querySelector('[data-board]');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!board || !token) {
        return;
    }

    const cards = Array.from(board.querySelectorAll('[data-draggable-project]'));
    const zones = Array.from(board.querySelectorAll('[data-drop-zone]'));
    const baseUrl = board.dataset.projectsBaseUrl;
    let draggedCard = null;

    cards.forEach((card) => {
        card.addEventListener('dragstart', (event) => {
            draggedCard = card;
            card.classList.add('task-card-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.dataset.projectId);
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('task-card-dragging');
            zones.forEach((zone) => zone.classList.remove('board-drop-zone-active'));
            draggedCard = null;
        });
    });

    zones.forEach((zone) => {
        zone.addEventListener('dragover', (event) => {
            event.preventDefault();
            zone.classList.add('board-drop-zone-active');
            event.dataTransfer.dropEffect = 'move';
        });

        zone.addEventListener('dragleave', (event) => {
            if (!zone.contains(event.relatedTarget)) {
                zone.classList.remove('board-drop-zone-active');
            }
        });

        zone.addEventListener('drop', async (event) => {
            event.preventDefault();
            zone.classList.remove('board-drop-zone-active');

            const projectId = event.dataTransfer.getData('text/plain');

            if (!projectId || !draggedCard || zone.contains(draggedCard)) {
                return;
            }

            zone.appendChild(draggedCard);
            draggedCard.classList.add('opacity-60');

            const payload = {
                status: zone.dataset.dropStatus || null,
                column_id: zone.dataset.dropColumnId || null,
            };

            try {
                const response = await fetch(`${baseUrl}/${projectId}/move`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    throw new Error('Move failed');
                }

                window.location.reload();
            } catch (error) {
                window.location.reload();
            }
        });
    });
};

document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        openModalById(trigger.dataset.modalOpen);
        setTimeout(() => applyCreateProjectDefaults(trigger), 1);
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

setupBoardDragAndDrop();

window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeAllModals();
    }
});

import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
