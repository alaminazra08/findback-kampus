(() => {
    function render() {
        const root = document.querySelector('.fb-settings');
        if (!root) return;

        const scoreInput = root.querySelector(
            'input[name="matching_min_score"]'
        );
        const scoreValue = root.querySelector(
            '[data-fb-score-value]'
        );
        const scoreProgress = root.querySelector(
            '[data-fb-score-progress]'
        );

        function updateScore() {
            if (!scoreInput || !scoreValue || !scoreProgress) return;

            const raw = scoreInput.value;
            const number = Number(raw);
            const valid = raw !== '' && Number.isFinite(number);
            const score = valid
                ? Math.min(100, Math.max(0, number))
                : 0;

            scoreValue.textContent = valid ? raw + '%' : '—';
            scoreProgress.value = score;
        }

        if (scoreInput) {
            scoreInput.addEventListener('input', updateScore);
            updateScore();
        }
    }

    document.addEventListener('fb:admin-page', render);
    render();
})();
