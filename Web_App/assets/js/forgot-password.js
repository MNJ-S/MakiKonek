document.addEventListener("DOMContentLoaded", () => {
    const card = document.querySelector(".fp-card");
    if (!card) return;

    const apiUrl = card.dataset.fpApiUrl;
    const csrfToken = card.dataset.fpCsrf;
    const steps = card.querySelectorAll("[data-fp-step]");
    const stepperNodes = card.querySelectorAll("[data-fp-node]");
    const order = ["request", "otp", "reset", "success"];
    const state = {
        identifier: "",
        resetToken: "",
        username: "",
    };

    const showAlert = (step, message, success = false) => {
        const alertBox = card.querySelector(`[data-fp-alert="${step}"]`);
        if (!alertBox) return;
        alertBox.textContent = message;
        alertBox.hidden = !message;
        alertBox.classList.toggle("fp-alert-success", success);
    };

    const setBusy = (button, busy, busyText = "Sandali...") => {
        if (!button) return;
        if (busy) {
            button.dataset.originalText = button.textContent;
            button.textContent = busyText;
            button.disabled = true;
        } else {
            button.textContent = button.dataset.originalText || button.textContent;
            button.disabled = false;
        }
    };

    const callApi = async (payload) => {
        const response = await fetch(apiUrl, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "X-CSRF-Token": csrfToken,
            },
            body: JSON.stringify(payload),
        });

        let result;
        try {
            result = await response.json();
        } catch {
            throw new Error("Hindi mabasa ang sagot ng server.");
        }

        if (!response.ok || !result.ok) {
            throw new Error(result.message || "Hindi natuloy ang request.");
        }

        return result;
    };

    const goToStep = (name) => {
        steps.forEach((step) => {
            step.classList.toggle("is-active", step.dataset.fpStep === name);
        });

        const targetIndex = order.indexOf(name);
        stepperNodes.forEach((node) => {
            const nodeIndex = order.indexOf(node.dataset.fpNode);
            node.classList.toggle("is-active", node.dataset.fpNode === name);
            node.classList.toggle("is-complete", nodeIndex > -1 && nodeIndex < targetIndex);
        });
    };

    const requestForm = card.querySelector('[data-fp-step="request"]');
    const otpForm = card.querySelector('[data-fp-step="otp"]');
    const resetForm = card.querySelector('[data-fp-step="reset"]');
    const destinationLabel = card.querySelector("[data-fp-destination]");
    const usernameField = card.querySelector("[data-fp-username]");
    const otpInputs = Array.from(card.querySelectorAll("[data-fp-otp-input]"));
    const resendBtn = card.querySelector("[data-fp-resend]");
    let resendTimer = null;

    const clearOtpBoxes = () => {
        otpInputs.forEach((input) => {
            input.value = "";
            input.classList.remove("fp-otp-error");
        });
    };

    const startResendCooldown = (seconds = 60) => {
        if (!resendBtn) return;
        let remaining = seconds;
        resendBtn.disabled = true;
        resendBtn.textContent = `Magpadala ulit (${remaining}s)`;
        clearInterval(resendTimer);

        resendTimer = window.setInterval(() => {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(resendTimer);
                resendBtn.disabled = false;
                resendBtn.textContent = "Magpadala ulit ng OTP";
                return;
            }
            resendBtn.textContent = `Magpadala ulit (${remaining}s)`;
        }, 1000);
    };

    requestForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        showAlert("request", "");
        const identifier = requestForm.querySelector("#fp-identifier").value.trim();
        const submitButton = requestForm.querySelector("[data-fp-send-otp]");

        if (!identifier) {
            showAlert("request", "Pakilagay ang iyong email o username.");
            return;
        }

        setBusy(submitButton, true, "Ipinapadala...");
        try {
            const result = await callApi({ action: "request", identifier });
            state.identifier = identifier;
            state.resetToken = result.reset_token;
            state.username = "";
            destinationLabel.textContent = result.destination;
            clearOtpBoxes();
            goToStep("otp");
            startResendCooldown();
            otpInputs[0]?.focus();
        } catch (error) {
            showAlert("request", error.message);
        } finally {
            setBusy(submitButton, false);
        }
    });

    card.querySelectorAll('[data-fp-back="request"]').forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();
            showAlert("otp", "");
            goToStep("request");
        });
    });

    otpInputs.forEach((input, index) => {
        input.addEventListener("input", () => {
            input.value = input.value.replace(/[^0-9]/g, "").slice(0, 1);
            input.classList.remove("fp-otp-error");
            if (input.value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener("keydown", (event) => {
            if (event.key === "Backspace" && !input.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        input.addEventListener("paste", (event) => {
            const pasted = (event.clipboardData?.getData("text") || "").replace(/[^0-9]/g, "");
            if (!pasted) return;
            event.preventDefault();
            pasted.split("").slice(0, otpInputs.length).forEach((digit, digitIndex) => {
                otpInputs[digitIndex].value = digit;
            });
            otpInputs[Math.min(pasted.length, otpInputs.length - 1)]?.focus();
        });
    });

    resendBtn?.addEventListener("click", async () => {
        showAlert("otp", "");
        setBusy(resendBtn, true, "Ipinapadala...");
        try {
            const result = await callApi({ action: "request", identifier: state.identifier });
            state.resetToken = result.reset_token;
            destinationLabel.textContent = result.destination;
            clearOtpBoxes();
            showAlert("otp", "Naipadala muli ang OTP.", true);
            startResendCooldown();
            otpInputs[0]?.focus();
        } catch (error) {
            showAlert("otp", error.message);
            setBusy(resendBtn, false);
        }
    });

    otpForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        showAlert("otp", "");
        const code = otpInputs.map((input) => input.value).join("");
        const submitButton = otpForm.querySelector('button[type="submit"]');

        if (code.length !== otpInputs.length) {
            showAlert("otp", "Pakikumpleto ang 6-digit OTP.");
            otpInputs.forEach((input) => {
                if (!input.value) input.classList.add("fp-otp-error");
            });
            return;
        }

        setBusy(submitButton, true, "Vine-verify...");
        try {
            const result = await callApi({
                action: "verify",
                reset_token: state.resetToken,
                otp: code,
            });
            state.username = result.username;
            usernameField.value = result.username;
            goToStep("reset");
            card.querySelector("#fp-new-password")?.focus();
        } catch (error) {
            showAlert("otp", error.message);
            otpInputs.forEach((input) => input.classList.add("fp-otp-error"));
        } finally {
            setBusy(submitButton, false);
        }
    });

    resetForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        showAlert("reset", "");
        const newPassword = card.querySelector("#fp-new-password").value;
        const confirmPassword = card.querySelector("#fp-confirm-password").value;
        const submitButton = resetForm.querySelector('button[type="submit"]');

        if (newPassword.length < 8 || newPassword.length > 72) {
            showAlert("reset", "Dapat 8 hanggang 72 character ang password.");
            return;
        }
        if (newPassword !== confirmPassword) {
            showAlert("reset", "Hindi magkatugma ang dalawang password.");
            return;
        }

        setBusy(submitButton, true, "Ina-update...");
        try {
            await callApi({
                action: "reset",
                reset_token: state.resetToken,
                username: state.username,
                new_password: newPassword,
                confirm_password: confirmPassword,
            });
            goToStep("success");
        } catch (error) {
            showAlert("reset", error.message);
        } finally {
            setBusy(submitButton, false);
        }
    });

    card.querySelectorAll("[data-fp-toggle]").forEach((button) => {
        button.addEventListener("click", () => {
            const targetInput = document.getElementById(button.dataset.fpToggle);
            if (!targetInput) return;
            const isPassword = targetInput.type === "password";
            targetInput.type = isPassword ? "text" : "password";
            button.setAttribute("aria-label", isPassword ? "Itago ang password" : "Ipakita ang password");
        });
    });
});
