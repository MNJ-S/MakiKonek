document.addEventListener("input", (event) => {
    const field = event.target;
    if (!(field instanceof HTMLInputElement)) return;

    if (field.dataset.input === "name") {
        field.value = field.value.replace(/[^\p{L} .-]/gu, "");
    } else if (field.dataset.input === "phone") {
        field.value = field.value.replace(/\D/g, "").slice(0, 11);
    } else if (field.dataset.input === "digits") {
        const maxDigits = Number.parseInt(field.dataset.maxDigits || "", 10);
        const digits = field.value.replace(/\D/g, "");
        field.value = Number.isFinite(maxDigits) ? digits.slice(0, maxDigits) : digits;
    } else if (field.dataset.input === "numeric-id") {
        const maxDigits = Number.parseInt(field.dataset.maxDigits || "", 10);
        const digits = field.value.replace(/\D/g, "");
        field.value = Number.isFinite(maxDigits) ? digits.slice(0, maxDigits) : digits;
    } else if (field.dataset.input === "voter-id") {
        field.value = field.value.replace(/[^0-9A-Za-z-]/g, "").toUpperCase().slice(0, 21);
    }
});
