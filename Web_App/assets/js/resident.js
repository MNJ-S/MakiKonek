document.addEventListener("DOMContentLoaded", () => {
    const options = document.querySelectorAll(".document-option");
    const emptyState = document.querySelector("[data-empty-state]");
    const requestForm = document.querySelector("[data-request-form]");
    const selectedDocument = document.querySelector("[data-selected-document]");
    const selectedFee = document.querySelector("[data-selected-fee]");
    const processingTime = document.querySelector("[data-processing-time]");
    const clearButton = document.querySelector("[data-clear-request]");

    if (!options.length || !emptyState || !requestForm) {
        return;
    }

    const showEmptyState = () => {
        options.forEach((option) => option.classList.remove("active"));
        emptyState.style.display = "grid";
        requestForm.classList.remove("is-visible");
    };

    options.forEach((option) => {
        option.addEventListener("click", () => {
            options.forEach((item) => item.classList.remove("active"));
            option.classList.add("active");

            selectedDocument.textContent = option.dataset.documentName;
            selectedFee.textContent = option.dataset.documentFee;
            processingTime.textContent = option.dataset.documentTime;

            emptyState.style.display = "none";
            requestForm.classList.add("is-visible");
        });
    });

    clearButton?.addEventListener("click", showEmptyState);
});
