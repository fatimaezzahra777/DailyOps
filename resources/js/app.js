const html = document.documentElement;
const themeToggle = document.getElementById('theme-toggle');
const menuToggle = document.getElementById('menu-btn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');

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
