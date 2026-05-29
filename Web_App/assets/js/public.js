document.addEventListener("DOMContentLoaded", () => {
    const navToggle = document.querySelector(".nav-toggle");
    const navMenu = document.querySelector("[data-nav-menu]");

    if (navToggle && navMenu) {
        navToggle.addEventListener("click", () => {
            const isOpen = navMenu.classList.toggle("is-open");
            navToggle.setAttribute("aria-expanded", String(isOpen));
        });

        navMenu.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", () => {
                navMenu.classList.remove("is-open");
                navToggle.setAttribute("aria-expanded", "false");
            });
        });
    }

    const roleInputs = document.querySelectorAll("input[name='role']");
    const loginButton = document.querySelector("[data-login-button]");

    roleInputs.forEach((input) => {
        input.addEventListener("change", () => {
            if (loginButton && input.checked) {
                loginButton.textContent = "Mag-login";
            }
        });
    });

    if (document.body.classList.contains("auth-page")) {
        requestAnimationFrame(() => {
            document.body.classList.add("auth-ready");
        });

        document.querySelectorAll("[data-auth-transition]").forEach((link) => {
            link.addEventListener("click", (event) => {
                const target = link.getAttribute("href");

                if (!target || link.target || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                event.preventDefault();
                document.body.classList.add("auth-morphing");
                window.setTimeout(() => {
                    window.location.href = target;
                }, 190);
            });
        });
    }
});
