const themeButton = document.querySelector('.theme-button');
if (themeButton) {
    const labelTheme = () => {
        const dark = document.documentElement.dataset.theme === 'dark';
        themeButton.textContent = dark ? 'Tema terang' : 'Tema gelap';
        themeButton.setAttribute('aria-label', dark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
    };
    labelTheme();
    themeButton.addEventListener('click', () => {
        const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = theme;
        try { localStorage.setItem('sip-theme', theme); } catch {}
        labelTheme();
    });
}
const menu = document.querySelector('.menu-button');
const sidebar = document.querySelector('.sidebar');
const backdrop = document.querySelector('.nav-backdrop');
const desktopNavigation = window.matchMedia('(min-width:781px)');
const closeMenu = () => {
    sidebar?.classList.remove('open');
    if (sidebar) sidebar.inert = !desktopNavigation.matches;
    menu?.setAttribute('aria-expanded', 'false');
    if (backdrop) backdrop.hidden = true;
};
menu?.addEventListener('click', () => {
    const open = menu.getAttribute('aria-expanded') !== 'true';
    sidebar.classList.toggle('open', open);
    sidebar.inert = !open;
    backdrop.hidden = !open;
    menu.setAttribute('aria-expanded', String(open));
    if (open) sidebar.querySelector('a').focus();
});
backdrop?.addEventListener('click', () => { closeMenu(); menu.focus(); });
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && sidebar?.classList.contains('open')) { closeMenu(); menu.focus(); }
});
if (sidebar) sidebar.inert = !desktopNavigation.matches;
desktopNavigation.addEventListener('change', closeMenu);
sidebar?.addEventListener('keydown', event => {
    if (event.key !== 'Tab' || !sidebar.classList.contains('open')) return;
    const links = [...sidebar.querySelectorAll('a[href]')];
    const first = links[0], last = links.at(-1);
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
});
document.querySelectorAll('.password-toggle').forEach(button => button.addEventListener('click', () => {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    button.textContent = show ? 'Sembunyikan' : 'Tampilkan';
    button.setAttribute('aria-pressed', String(show));
}));
document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
}));
document.querySelectorAll('form[data-busy]').forEach(form => form.addEventListener('submit', event => {
    if (event.defaultPrevented) return;
    if (form.dataset.submitting) { event.preventDefault(); return; }
    form.dataset.submitting = 'true';
    form.classList.add('is-busy');
    form.setAttribute('aria-busy', 'true');
    const status = form.querySelector('.form-status');
    if (status) status.textContent = 'Menyimpan, mohon tunggu…';
}));
window.addEventListener('pageshow', () => document.querySelectorAll('form[data-busy]').forEach(form => {
    delete form.dataset.submitting;
    form.classList.remove('is-busy');
    form.removeAttribute('aria-busy');
    const status = form.querySelector('.form-status');
    if (status) status.textContent = '';
}));
document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', () => window.print()));
const choices = document.querySelectorAll('.course-choice');
const selectedSks = document.getElementById('selected-sks');
const countSks = () => {
    if (selectedSks) selectedSks.textContent = String([...choices].reduce((sum, choice) => sum + (choice.checked ? Number(choice.dataset.sks) : 0), 0));
};
choices.forEach(choice => choice.addEventListener('change', countSks));
countSks();
document.getElementById('error-summary')?.focus();
