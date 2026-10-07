const themeButton = document.querySelector('.theme-button');
if (themeButton) {
    const labelTheme = () => {
        const dark = document.documentElement.dataset.theme === 'dark';
        themeButton.querySelector('.control-label').textContent = dark ? 'Tema terang' : 'Tema gelap';
        themeButton.title = dark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap';
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
    button.querySelector('.control-label').textContent = show ? 'Sembunyikan' : 'Tampilkan';
    button.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    button.title = show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';
    button.setAttribute('aria-pressed', String(show));
}));
const confirmation = document.getElementById('delete-confirmation');
let pendingDeletion, deletionButton, confirmedDeletion;
document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (form === confirmedDeletion) { confirmedDeletion = undefined; return; }
    event.preventDefault();
    if (form.dataset.submitting || !confirmation) return;
    pendingDeletion = form;
    deletionButton = event.submitter;
    document.getElementById('confirm-message').textContent = form.dataset.confirm;
    confirmation.returnValue = '';
    confirmation.showModal();
}));
confirmation?.addEventListener('close', () => {
    const form = pendingDeletion, button = deletionButton;
    pendingDeletion = deletionButton = undefined;
    button?.focus();
    if (confirmation.returnValue === 'delete' && form) {
        confirmedDeletion = form;
        form.requestSubmit(button);
    }
});
confirmation?.addEventListener('keydown', event => {
    if (event.key !== 'Tab') return;
    const buttons = [...confirmation.querySelectorAll('button')];
    const first = buttons[0], last = buttons.at(-1);
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
});
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
document.querySelectorAll('.table-wrap').forEach(table => {
    table.tabIndex = 0;
    table.setAttribute('role', 'region');
    table.setAttribute('aria-label', 'Tabel data. Gunakan tombol panah untuk menggeser tabel yang lebar.');
});
const choices = document.querySelectorAll('.course-choice');
const selectedSks = document.getElementById('selected-sks');
const countSks = () => {
    if (selectedSks) selectedSks.textContent = String([...choices].reduce((sum, choice) => sum + (choice.checked ? Number(choice.dataset.sks) : 0), 0));
};
choices.forEach(choice => choice.addEventListener('change', countSks));
countSks();
document.getElementById('error-summary')?.focus();

const home = document.querySelector('.home-body');
if (home) {
    home.classList.add('js-ready');
    const homeMenu = document.querySelector('.home-menu-button');
    const homeNav = document.getElementById('home-navigation');
    const closeHomeMenu = () => {
        homeNav.classList.remove('open');
        homeMenu.setAttribute('aria-expanded', 'false');
    };
    homeMenu.addEventListener('click', () => {
        const open = homeNav.classList.toggle('open');
        homeMenu.setAttribute('aria-expanded', String(open));
    });
    homeNav.addEventListener('click', event => {
        if (event.target.closest('a')) closeHomeMenu();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && homeNav.classList.contains('open')) {
            closeHomeMenu();
            homeMenu.focus();
        }
    });
    const roleButtons = [...document.querySelectorAll('[data-role]')];
    const showRole = role => {
        roleButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.role === role)));
        document.querySelectorAll('[data-role-panel]').forEach(panel => { panel.hidden = panel.dataset.rolePanel !== role; });
    };
    roleButtons.forEach(button => button.addEventListener('click', () => showRole(button.dataset.role)));
    showRole('mahasiswa');
    const reveals = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const revealObserver = new IntersectionObserver(entries => entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                revealObserver.unobserve(entry.target);
            }
        }), { threshold: .12 });
        reveals.forEach(element => revealObserver.observe(element));
    } else {
        reveals.forEach(element => element.classList.add('revealed'));
    }
    if ('IntersectionObserver' in window) {
        const nodes = [...document.querySelectorAll('.journey-node')];
        const lines = [...document.querySelectorAll('.journey-line')];
        const stages = [0, 0, 1, 2, 2, 3];
        const steps = [...document.querySelectorAll('[data-journey-step]')];
        const journeyObserver = new IntersectionObserver(entries => entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const stage = stages[steps.indexOf(entry.target)];
            nodes.forEach((node, index) => node.classList.toggle('active', index <= stage));
            lines.forEach((line, index) => line.classList.toggle('active', index < stage));
        }), { rootMargin: '-20% 0px -40% 0px' });
        steps.forEach(step => journeyObserver.observe(step));
    }
}
