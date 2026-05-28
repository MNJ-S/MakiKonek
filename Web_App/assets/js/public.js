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
                loginButton.textContent = `Mag-login bilang ${input.value}`;
            }
        });
    });
});
