(() => {
    let pending = false;

    function revealPhoto(image) {
        if (image.complete && image.naturalWidth > 0) image.classList.add('is-loaded');
    }

    function revealLoadedPhotos() {
        document.querySelectorAll('.fb-account-photo-image').forEach(revealPhoto);
    }

    document.addEventListener('load', (event) => {
        if (event.target.matches?.('.fb-account-photo-image')) revealPhoto(event.target);
    }, true);
    document.addEventListener('fb:admin-page', revealLoadedPhotos);
    window.addEventListener('pageshow', revealLoadedPhotos);
    revealLoadedPhotos();

    async function refreshPhotos() {
        if (!document.querySelector('main [data-account-photo]')) return;
        const avatars = [...document.querySelectorAll('[data-account-photo]')];
        if (pending || document.hidden || avatars.length === 0) return;

        const pageUrl = location.href;
        pending = true;
        try {
            const response = await fetch(pageUrl, {
                cache: 'no-store',
                headers: { Accept: 'text/html' },
                signal: AbortSignal.timeout(8000),
            });
            if (!response.ok || response.redirected || location.href !== pageUrl) return;

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const updated = new Map([...page.querySelectorAll('[data-account-photo]')]
                .map((avatar) => [avatar.dataset.accountPhoto, avatar]));
            for (const avatar of avatars) {
                if (!avatar.isConnected) continue;
                const next = updated.get(avatar.dataset.accountPhoto);
                if (!next) continue;
                const currentImage = avatar.querySelector('img');
                const nextImage = next.querySelector('img');
                const currentSource = currentImage?.getAttribute('src');
                const nextSource = nextImage?.getAttribute('src');
                if (currentSource !== nextSource || avatar.textContent.trim() !== next.textContent.trim()) {
                    avatar.replaceChildren(...next.childNodes);
                    const image = avatar.querySelector('img');
                    if (image) revealPhoto(image);
                }
            }
        } catch {
            // Retry on the next refresh.
        } finally {
            pending = false;
        }
    }

    document.addEventListener('visibilitychange', refreshPhotos);
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) refreshPhotos();
    });
    document.addEventListener('fb:admin-page', refreshPhotos);
    window.setInterval(refreshPhotos, 5000);
})();
