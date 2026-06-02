import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const html = document.documentElement;
const themeToggle = document.getElementById('theme-toggle');
const menuToggle = document.getElementById('menu-btn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
const taskSearchRoot = document.querySelector('[data-task-search]');
const realtimeNotificationsRoot = document.querySelector('[data-realtime-notifications]');

let activeModal = null;

const getModalRoots = () => Array.from(document.querySelectorAll('[data-modal]'));

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

    modal.querySelectorAll('input:not([type="hidden"]):not([type="password"]):not([type="text"][tabindex="-1"]), textarea, select').forEach((field) => {
        if (field.hasAttribute('data-field-default')) {
            return;
        }

        if (field.tagName === 'SELECT') {
            field.selectedIndex = 0;
            field.dispatchEvent(new Event('change', { bubbles: true }));
            return;
        }

        if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = field.defaultChecked;
        } else {
            field.value = '';
        }

        field.dispatchEvent(new Event('input', { bubbles: true }));
    });
};

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

    if (!getModalRoots().some((item) => !item.classList.contains('hidden'))) {
        document.body.classList.remove('overflow-hidden');
    }
};

const openModalById = (id, afterOpen = null) => {
    getModalRoots().forEach((modal) => {
        const isTargetModal = modal.id === id;

        if (isTargetModal) {
            resetModalFields(modal);
        }

        setModalState(modal, isTargetModal);

        if (isTargetModal) {
            requestAnimationFrame(() => {
                resetModalFields(modal);
                afterOpen?.(modal);
            });
        }
    });
};

const applyCreateProjectDefaults = (trigger, modal = document) => {
    if (trigger.dataset.modalOpen !== 'create-project-modal') {
        return;
    }

    const statusField = modal.querySelector('#create-project-status');
    const columnField = modal.querySelector('#create-project-column-id');
    const startDateField = modal.querySelector('[name="create_start_date"]');
    const endDateField = modal.querySelector('[name="create_end_date"]');

    if (statusField && trigger.dataset.createStatus) {
        statusField.value = trigger.dataset.createStatus;
        statusField.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (columnField) {
        columnField.value = trigger.dataset.createColumnId || '';
        columnField.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (trigger.dataset.createDate) {
        [startDateField, endDateField].forEach((field) => {
            if (!field) {
                return;
            }

            field.value = trigger.dataset.createDate;
            field.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
};

const closeAllModals = () => {
    getModalRoots().forEach((modal) => {
        setModalState(modal, false);
        resetModalFields(modal);
    });
};

const resetClosedModals = () => {
    getModalRoots()
        .filter((modal) => modal.classList.contains('hidden'))
        .forEach((modal) => resetModalFields(modal));
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

const setupCalendarDayCreate = () => {
    document.querySelectorAll('[data-calendar-create-date]').forEach((day) => {
        day.addEventListener('click', (event) => {
            if (event.target.closest('a, button, input, select, textarea')) {
                return;
            }

            openModalById('create-project-modal', (modal) => applyCreateProjectDefaults({
                dataset: {
                    modalOpen: 'create-project-modal',
                    createDate: day.dataset.calendarCreateDate,
                },
            }, modal));
        });
    });
};

document.addEventListener('click', (event) => {
    const openTrigger = event.target.closest('[data-modal-open]');
    if (openTrigger) {
        openModalById(openTrigger.dataset.modalOpen, (modal) => applyCreateProjectDefaults(openTrigger, modal));
        return;
    }

    const closeTrigger = event.target.closest('[data-modal-close]');
    if (closeTrigger) {
        closeAllModals();
        return;
    }

    const switchTrigger = event.target.closest('[data-modal-switch]');
    if (switchTrigger) {
        openModalById(switchTrigger.dataset.modalSwitch);
    }
});

setupBoardDragAndDrop();
setupCalendarDayCreate();
resetClosedModals();

window.addEventListener('pageshow', resetClosedModals);

const setupRealtimeNotifications = () => {
    if (!realtimeNotificationsRoot || !window.Echo) {
        return;
    }

    const userId = realtimeNotificationsRoot.dataset.notificationUserId;
    const button = realtimeNotificationsRoot.querySelector('[data-notification-toggle]');
    const badge = realtimeNotificationsRoot.querySelector('[data-notification-badge]');
    const list = realtimeNotificationsRoot.querySelector('[data-notification-list]');
    const empty = realtimeNotificationsRoot.querySelector('[data-notification-empty]');
    const panel = realtimeNotificationsRoot.querySelector('[data-notification-panel]');
    const toastContainer = document.getElementById('notification-toast-container');
    let unreadCount = 0;

    if (!userId || !badge || !list || !toastContainer) {
        return;
    }

    const updateBadge = () => {
        badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
        badge.classList.toggle('hidden', unreadCount === 0);
    };

    const createNotificationItem = (notification) => {
        const link = document.createElement('a');
        link.href = notification.url ?? '#';
        link.className = 'flex gap-3 border-b border-[var(--line)] px-4 py-3 last:border-0 hover:bg-[var(--card-soft)]';

        const icon = document.createElement('span');
        icon.className = 'material-symbols-rounded mt-0.5 text-[20px] text-[var(--accent)]';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = notification.type === 'project' ? 'work' : 'task_alt';

        const content = document.createElement('span');
        content.className = 'min-w-0 flex-1';

        const title = document.createElement('p');
        title.className = 'text-sm font-semibold text-[var(--text-strong)]';
        title.textContent = notification.title ?? 'Notification';

        const message = document.createElement('p');
        message.className = 'mt-1 text-xs leading-5 text-[var(--muted)]';
        message.textContent = notification.message ?? '';

        content.append(title, message);
        link.append(icon, content);

        return link;
    };

    const showToast = (notification) => {
        const toast = document.createElement('a');
        toast.href = notification.url ?? '#';
        toast.className = 'flex w-80 gap-3 rounded-md border border-[var(--accent-line)] bg-white p-4 text-sm shadow-[0_12px_30px_rgba(0,0,0,0.12)] transition hover:-translate-y-0.5';

        const icon = document.createElement('span');
        icon.className = 'material-symbols-rounded mt-0.5 text-[22px] text-[var(--accent)]';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = notification.type === 'project' ? 'work' : 'task_alt';

        const content = document.createElement('span');
        content.className = 'min-w-0 flex-1';

        const title = document.createElement('p');
        title.className = 'font-semibold text-[var(--text-strong)]';
        title.textContent = notification.title ?? 'Notification';

        const message = document.createElement('p');
        message.className = 'mt-1 leading-5 text-[var(--muted)]';
        message.textContent = notification.message ?? '';

        content.append(title, message);
        toast.append(icon, content);
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 6500);
    };

    const pushNotification = (notification) => {
        unreadCount += 1;
        empty?.classList.add('hidden');
        list.prepend(createNotificationItem(notification));
        updateBadge();
        showToast(notification);
    };

    button?.addEventListener('click', () => {
        panel?.classList.toggle('hidden');
        unreadCount = 0;
        updateBadge();
    });

    window.Echo.private(`App.Models.User.${userId}`)
        .listen('.assignment.notification', pushNotification);
};

setupRealtimeNotifications();

window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeAllModals();
    }
});

if (taskSearchRoot) {
    const filterForm = document.getElementById('task-filter-form');
    const tasksContainer = document.getElementById('tasks-container');
    const tasksPagination = document.getElementById('tasks-pagination');
    const searchInput = document.getElementById('task-search-input');
    let searchTimer;

    const buildTaskQuery = () => {
        const formData = new FormData(filterForm);
        return new URLSearchParams(
            [...formData.entries()].filter(([, value]) => String(value).trim() !== ''),
        );
    };

    const refreshTasks = async (url = null) => {
        const queryString = buildTaskQuery().toString();
        const targetUrl = url ?? (queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname);

        const response = await fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        tasksContainer.innerHTML = data.results;
        tasksPagination.innerHTML = data.pagination;
        window.history.replaceState({}, '', targetUrl);
    };

    filterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        refreshTasks();
    });

    filterForm?.querySelectorAll('select').forEach((select) => {
        select.addEventListener('change', () => refreshTasks());
    });

    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => refreshTasks(), 280);
    });

    tasksPagination?.addEventListener('click', (event) => {
        const link = event.target.closest('a');

        if (!link) {
            return;
        }

        event.preventDefault();
        refreshTasks(link.href);
    });
}
