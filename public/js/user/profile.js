document.addEventListener("DOMContentLoaded", () => {

document.querySelectorAll('.profile-nav-link[href^="#"]').forEach(link => {
    link.addEventListener('click', function () {
        document.querySelectorAll('.profile-nav-link').forEach(item => {
            item.classList.remove('active');
        });

        this.classList.add('active');
    });
});

});
