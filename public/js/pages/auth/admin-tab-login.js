(function () {
    function authorize() {
        try { sessionStorage.setItem('findback.admin.tab.authorized.v1', '1'); } catch (e) {}
    }
    document.addEventListener('submit', function (event) {
        if (event.target && event.target.matches('form.fb-auth-form')) authorize();
    }, true);
    document.addEventListener('click', function (event) {
        if (event.target && event.target.closest('a.fb-login-google')) authorize();
    }, true);
})();
