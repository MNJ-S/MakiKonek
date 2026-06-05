document.addEventListener("DOMContentLoaded", () => {
    const options = document.querySelectorAll(".document-option");
    const emptyState = document.querySelector("[data-empty-state]");
    const requestForm = document.querySelector("[data-request-form]");
    const selectedDocument = document.querySelector("[data-selected-document]");
    const selectedFee = document.querySelector("[data-selected-fee]");
    const processingTime = document.querySelector("[data-processing-time]");
    const clearButton = document.querySelector("[data-clear-request]");
    const documentTypeInput = document.querySelector("[data-document-type-input]");
    const documentFeeInput = document.querySelector("[data-document-fee-input]");
    const serviceExtras = document.querySelectorAll("[data-service-extra]");

    if (!options.length || !emptyState || !requestForm) {
        return;
    }

    const updateServiceExtras = (documentName = "") => {
        serviceExtras.forEach((section) => {
            const isActive = section.dataset.serviceExtra === documentName;
            section.style.display = isActive ? "" : "none";
            section.querySelectorAll("input, select, textarea").forEach((field) => {
                field.disabled = !isActive;
                if (field.dataset.optional !== "true") {
                    field.required = isActive;
                }
                if (!isActive) {
                    field.value = "";
                }
            });
        });
    };

    const showEmptyState = () => {
        options.forEach((option) => option.classList.remove("active"));
        emptyState.style.display = "grid";
        requestForm.classList.remove("is-visible");
        if (documentTypeInput) {
            documentTypeInput.value = "";
        }
        if (documentFeeInput) {
            documentFeeInput.value = "";
        }
        updateServiceExtras();
    };

    options.forEach((option) => {
        option.addEventListener("click", () => {
            options.forEach((item) => item.classList.remove("active"));
            option.classList.add("active");

            selectedDocument.textContent = option.dataset.documentName;
            selectedFee.textContent = option.dataset.documentFee;
            processingTime.textContent = option.dataset.documentTime;
            if (documentTypeInput) {
                documentTypeInput.value = option.dataset.documentName;
            }
            if (documentFeeInput) {
                documentFeeInput.value = option.dataset.documentFee;
            }
            updateServiceExtras(option.dataset.documentName);

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
    const paymentBlock = document.querySelector(".payment-main-wrapper-5050");
    const paymentMethodBlock = document.querySelector(".payment-method-sub-block");
    const receiptInput = document.getElementById("payment_receipt");
    const receiptUploadBox = document.querySelector(".compact-upload");
    const sampleReceiptLink = document.querySelector(".receipt-upload-sub-block .sample-link");
    const qrCodeBlock = document.querySelector(".qr-code-sub-block");
    const receiptBlock = document.querySelector(".receipt-upload-sub-block");

    const setPaymentAvailability = (isRequired) => {
        if (paymentBlock) {
            paymentBlock.style.display = isRequired ? "" : "none";
        }
        if (paymentMethodBlock) {
            paymentMethodBlock.style.display = isRequired ? "" : "none";
        }
        if (receiptBlock) {
            receiptBlock.style.display = isRequired ? "" : "none";
        }
        if (qrCodeBlock) {
            qrCodeBlock.style.display = isRequired ? "" : "none";
        }
        paymentRadios.forEach((radio) => {
            radio.disabled = !isRequired;
            if (!isRequired) {
                radio.checked = false;
            }
        });
        if (receiptInput) {
            receiptInput.disabled = !isRequired;
            receiptInput.required = false;
            if (!isRequired) {
                receiptInput.value = "";
            }
        }
        if (!isRequired && receiptModal) {
            receiptModal.style.display = "none";
        }
    };

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
        const selectedDocName = documentTypeInput?.value || "";
        const selectedFeeText = documentFeeInput?.value || "";
        const isFreeService = selectedDocName === "Certificate of Indigency" || selectedFeeText.toLowerCase() === "free";

        if (isFreeService) {
            setPaymentAvailability(false);
            return;
        }

        setPaymentAvailability(true);
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

document.addEventListener('DOMContentLoaded', function () {
    const fileInputs = document.querySelectorAll('input[type="file"]');

    fileInputs.forEach(input => {
        input.addEventListener('change', function (e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : "No file chosen";

            const uploadBox = this.closest('.upload-box');

            if (uploadBox) {
                const subText = uploadBox.querySelector('.upload-sub');
                if (subText) {
                    subText.textContent = "Selected: " + fileName;
                    subText.style.color = "#2e6f40";
                    subText.style.fontWeight = "bold";
                }
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const toasts = document.querySelectorAll('.toast-notification');

    toasts.forEach(toast => {
        setTimeout(() => {
            toast.remove();
        }, 5000);
    });
});
