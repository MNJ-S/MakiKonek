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
            
            resetPaymentToggle();
        });
    });

    clearButton?.addEventListener("click", showEmptyState);

    const sampleLink = document.querySelector(".sample-link");
    const receiptModal = document.getElementById("receiptModal");
    const modalBackBtn = document.querySelector(".modal-back-btn");

    sampleLink?.addEventListener("click", (event) => {
        event.preventDefault();
        if (receiptModal) {
            receiptModal.style.display = "flex";
        }
    });

    modalBackBtn?.addEventListener("click", () => {
        if (receiptModal) {
            receiptModal.style.display = "none";
        }
    });

    receiptModal?.addEventListener("click", (event) => {
        if (event.target === receiptModal) {
            receiptModal.style.display = "none";
        }
    });

    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const receiptInput = document.getElementById("payment_receipt");
    const receiptUploadBox = document.querySelector(".compact-upload");
    const sampleReceiptLink = document.querySelector(".receipt-upload-sub-block .sample-link");
    const qrCodeBlock = document.querySelector(".qr-code-sub-block");

    const handlePaymentChange = (value) => {
        if (value === "online") {
            if (receiptInput) {
                receiptInput.disabled = false;
                receiptInput.required = true;
            }
            if (receiptUploadBox) {
                receiptUploadBox.style.opacity = "1";
                receiptUploadBox.style.pointerEvents = "auto";
                receiptUploadBox.style.cursor = "pointer";
            }
            if (sampleReceiptLink) {
                sampleReceiptLink.style.opacity = "1";
                sampleReceiptLink.style.pointerEvents = "auto";
            }
            if (qrCodeBlock) {
                qrCodeBlock.style.opacity = "1";
                qrCodeBlock.style.pointerEvents = "auto";
            }
        } else if (value === "cash") {
            if (receiptInput) {
                receiptInput.disabled = true;
                receiptInput.required = false;
                receiptInput.value = "";
            }
            if (receiptUploadBox) {
                receiptUploadBox.style.opacity = "0.4";
                receiptUploadBox.style.pointerEvents = "none";
                receiptUploadBox.style.cursor = "not-allowed";
            }
            if (sampleReceiptLink) {
                sampleReceiptLink.style.opacity = "0.4";
                sampleReceiptLink.style.pointerEvents = "none";
            }
            if (qrCodeBlock) {
                qrCodeBlock.style.opacity = "0.4";
                qrCodeBlock.style.pointerEvents = "none";
            }
        }
    };

    paymentRadios.forEach((radio) => {
        radio.addEventListener("change", (e) => {
            handlePaymentChange(e.target.value);
        });
    });

    const resetPaymentToggle = () => {
        const defaultRadio = document.querySelector('input[name="payment_method"][value="online"]');
        if (defaultRadio) {
            defaultRadio.checked = true;
            handlePaymentChange("online");
        }
    };

    const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
    if (checkedRadio) {
        handlePaymentChange(checkedRadio.value);
    }
});