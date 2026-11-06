(function () {
    try {
        if (
            window.matchMedia("(min-width:961px)").matches &&
            window.localStorage.getItem("findback-sidebar-compact-v44") === "1"
        ) {
            document.documentElement.classList.add("fb45-precompact");
        }
    } catch (e) {}
})();
