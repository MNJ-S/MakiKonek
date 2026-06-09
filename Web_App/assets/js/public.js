document.addEventListener("DOMContentLoaded", () => {
    const navToggle = document.querySelector(".nav-toggle");
    const navMenu = document.querySelector("[data-nav-menu]");

    if (navToggle && navMenu && !navMenu.dataset.navInit) {
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

        navMenu.dataset.navInit = "true";
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

    const calendarGrid = document.querySelector("[data-calendar-grid]");
    const calendarTitle = document.querySelector("[data-calendar-title]");
    const calendarPrev = document.querySelector("[data-calendar-prev]");
    const calendarNext = document.querySelector("[data-calendar-next]");
    const calendarToday = document.querySelector("[data-calendar-today]");

    if (calendarGrid && calendarTitle) {
        const today = new Date();
        let visibleDate = new Date(today.getFullYear(), today.getMonth(), 1);
        const dayLabels = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
        const events = window.publicCalendarEvents || {};

        const formatKey = (date) => {
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            return `${date.getFullYear()}-${month}-${day}`;
        };

        const renderCalendar = () => {
            const year = visibleDate.getFullYear();
            const month = visibleDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const previousLastDay = new Date(year, month, 0).getDate();
            const cells = [];

            calendarTitle.textContent = visibleDate.toLocaleDateString("en-US", {
                month: "long",
                year: "numeric",
            });
            calendarGrid.setAttribute("aria-label", `${calendarTitle.textContent} activities calendar`);

            dayLabels.forEach((label) => {
                const dayLabel = document.createElement("div");
                dayLabel.className = "day-label";
                dayLabel.textContent = label;
                cells.push(dayLabel);
            });

            for (let index = firstDay.getDay() - 1; index >= 0; index -= 1) {
                const day = previousLastDay - index;
                const cell = document.createElement("div");
                cell.className = "day muted";
                cell.innerHTML = `<span>${day}</span>`;
                cells.push(cell);
            }

            for (let day = 1; day <= lastDay.getDate(); day += 1) {
                const cellDate = new Date(year, month, day);
                const cell = document.createElement("div");
                const key = formatKey(cellDate);
                cell.className = "day";

                if (formatKey(cellDate) === formatKey(today)) {
                    cell.classList.add("today");
                }

                const dateLabel = document.createElement("span");
                dateLabel.textContent = String(day);
                cell.append(dateLabel);

                (events[key] || []).forEach((event) => {
                    const eventNode = document.createElement("strong");
                    eventNode.className = `event ${event.type}`;
                    eventNode.textContent = event.title;

                    const details = [];
                    if (event.time) {
                        details.push(event.time.replace(" to ", " - "));
                    }
                    if (event.location) {
                        details.push(event.location);
                    }

                    details.forEach((detail) => {
                        eventNode.append(document.createElement("br"), document.createTextNode(detail));
                    });
                    cell.append(eventNode);
                });

                cells.push(cell);
            }

            const totalCellsWithoutLabels = cells.length - dayLabels.length;
            const trailingCells = (7 - (totalCellsWithoutLabels % 7)) % 7;
            for (let day = 1; day <= trailingCells; day += 1) {
                const cell = document.createElement("div");
                cell.className = "day muted";
                cell.innerHTML = `<span>${day}</span>`;
                cells.push(cell);
            }

            calendarGrid.replaceChildren(...cells);
        };

        calendarPrev?.addEventListener("click", () => {
            visibleDate = new Date(visibleDate.getFullYear(), visibleDate.getMonth() - 1, 1);
            renderCalendar();
        });

        calendarNext?.addEventListener("click", () => {
            visibleDate = new Date(visibleDate.getFullYear(), visibleDate.getMonth() + 1, 1);
            renderCalendar();
        });

        calendarToday?.addEventListener("click", () => {
            visibleDate = new Date(today.getFullYear(), today.getMonth(), 1);
            renderCalendar();
        });

        renderCalendar();
    }

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
