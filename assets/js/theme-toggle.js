(function () {
    const storageKey = 'civicsolve-theme';
    const root = document.documentElement;

    function createToggleButton() {
        const button = document.createElement('button');
        button.id = 'theme-toggle';
        button.type = 'button';
        button.className = 'theme-toggle';
        button.setAttribute('aria-label', 'Toggle dark mode');
        button.addEventListener('click', () => {
            const nextTheme = root.classList.contains('dark-mode') ? 'light' : 'dark';
            applyTheme(nextTheme);
        });
        document.body.appendChild(button);
        return button;
    }

    function applyTheme(theme) {
        const isDark = theme === 'dark';
        root.classList.toggle('dark-mode', isDark);
        localStorage.setItem(storageKey, theme);
        const nextLabel = isDark ? '☀️' : '🌙';
        const nextTitle = isDark ? 'Switch to bright mode' : 'Switch to dark mode';
        const button = document.getElementById('theme-toggle');
        if (button) {
            button.textContent = nextLabel;
            button.title = nextTitle;
        }
    }

    function loadTheme() {
        const savedTheme = localStorage.getItem(storageKey);
        const defaultTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        applyTheme(savedTheme || defaultTheme);
    }

    document.addEventListener('DOMContentLoaded', () => {
        createToggleButton();
        loadTheme();
    });
})();
