<script>
    (() => {
        let theme = 'light';
        try {
            const saved = localStorage.getItem('sip-theme');
            if (saved === 'dark' || saved === 'light') theme = saved;
        } catch {}
        document.documentElement.dataset.theme = theme;
    })();
</script>
