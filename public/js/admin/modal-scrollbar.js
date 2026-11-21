(() => {
    const root = document.documentElement;
    const modalSelector = '.fb-review-dialog:modal, .fb-proof-dialog:modal, #fb-claim-reject-dialog:modal, #fb-confirm-global-v14:modal';

    function measureGutter() {
        if (document.querySelector(modalSelector)) return;

        const gutter = Math.max(0, window.innerWidth - root.getBoundingClientRect().width);
        root.style.setProperty('--fb-admin-scrollbar-space', gutter + 'px');
    }

    measureGutter();
    window.addEventListener('resize', measureGutter);
    document.addEventListener('close', measureGutter, true);
})();
