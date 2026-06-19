<?php
session_start();

if (empty($_SESSION['forgot_password_csrf'])) {
    $_SESSION['forgot_password_csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nakalimutan ang Password | MakiKonek</title>
    <link rel="stylesheet" href="assets/css/login.css?v=20260620n">
    <link rel="stylesheet" href="assets/css/forgot-password.css?v=20260620b">
    <link rel="icon" href="assets/img/Barangay_Makiling_Seal.png" type="image/png">
    <script defer src="assets/js/public.js?v=20260529c"></script>
    <script defer src="assets/js/forgot-password.js?v=20260620b"></script>
</head>

<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-intro" aria-label="MakiKonek introduction">
            <div class="welcome-ribbon" aria-label="Welcome message">
                <img src="assets/img/green-eco-banner.png" alt="" aria-hidden="true">
            </div>

            <img class="intro-logo" src="assets/img/logo2-makikonek.png" alt="MakiKonek logo">
            <div class="intro-copy">
                <p>I-verify ang iyong pagkakakilanlan para makabalik sa iyong account nang ligtas.</p>
            </div>

            <div class="assistance-card">
                <div class="assistance-logos">
                    <img src="assets/img/Barangay_Makiling_Seal.png" alt="Barangay Makiling seal">
                    <img src="assets/img/Barangay_Makiling_SK.jpg" alt="Sangguniang Kabataan Makiling logo">
                </div>
                <div class="assistance-info">
                    <strong>Need Assistance?</strong>
                    <span>☎ (049) 123-4567</span>
                    <span>✉ makiling.barangay@gmail.com</span>
                    <span>◷ Monday - Friday, 8:00 AM - 5:00 PM</span>
                </div>
            </div>
        </section>

        <section
            class="auth-card fp-card"
            aria-labelledby="fp-title"
            data-fp-api-url="forgot_password_api.php"
            data-fp-csrf="<?php echo htmlspecialchars($_SESSION['forgot_password_csrf'], ENT_QUOTES, 'UTF-8'); ?>"
        >
            <ol class="fp-stepper" data-fp-stepper>
                <li class="fp-step-node is-active" data-fp-node="request">
                    <span class="fp-step-dot">1</span>
                    <span class="fp-step-label">Kumpirmahin</span>
                </li>
                <li class="fp-step-node" data-fp-node="otp">
                    <span class="fp-step-dot">2</span>
                    <span class="fp-step-label">I-verify</span>
                </li>
                <li class="fp-step-node" data-fp-node="reset">
                    <span class="fp-step-dot">3</span>
                    <span class="fp-step-label">Bagong Password</span>
                </li>
            </ol>

            <form class="auth-form fp-step is-active" data-fp-step="request" novalidate>
                <h1 id="fp-title">Nakalimutan ang Password</h1>
                <p class="form-subtitle">Ilagay ang iyong email o username para makatanggap ng OTP</p>
                <div class="fp-alert fp-alert-error" data-fp-alert="request" hidden></div>

                <label class="field-label" for="fp-identifier">Email o Username</label>
                <input id="fp-identifier" name="identifier" type="text" placeholder="Ilagay ang email o username" autocomplete="username" required>
<br>
                <button class="btn btn-primary auth-submit" type="submit" data-fp-send-otp>Magpadala ng OTP</button>
                <p class="auth-switch"><a href="login_reg.php">← Bumalik sa Mag-login</a></p>
            </form>

            <form class="auth-form fp-step" data-fp-step="otp" novalidate>
                <h1>I-verify ang OTP</h1>
                <p class="form-subtitle">
                    Ipinadala namin ang 6-digit code sa <strong data-fp-destination>—</strong>
                </p>
                <div class="fp-alert fp-alert-error" data-fp-alert="otp" hidden></div>

                <label class="field-label" for="fp-otp-1">One-Time PIN</label>
                <div class="fp-otp-group" data-fp-otp-group>
                    <input class="fp-otp-box" id="fp-otp-1" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="one-time-code" data-fp-otp-input>
                    <input class="fp-otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-fp-otp-input>
                    <input class="fp-otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-fp-otp-input>
                    <input class="fp-otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-fp-otp-input>
                    <input class="fp-otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-fp-otp-input>
                    <input class="fp-otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-fp-otp-input>
                </div>

                <div class="fp-resend-row">
                    <span>Hindi nakatanggap ng code?</span>
                    <button type="button" class="fp-resend-btn" data-fp-resend>Magpadala ulit ng OTP</button>
                </div>

                <button class="btn btn-primary auth-submit" type="submit">I-verify</button>
                <p class="auth-switch"><a href="#" data-fp-back="request">← Baguhin ang email/username</a></p>
            </form>

            <form class="auth-form fp-step" data-fp-step="reset" novalidate>
                <h1>Gumawa ng Bagong Password</h1>
                <p class="form-subtitle">Na-verify na ang OTP. Ilagay ang iyong bagong password.</p>
                <div class="fp-alert fp-alert-error" data-fp-alert="reset" hidden></div>

                <label class="field-label" for="fp-username">Username</label>
                <input id="fp-username" name="username" type="text" data-fp-username readonly>

                <label class="field-label" for="fp-new-password">Bagong Password</label>
                <div class="fp-password-field">
                    <input id="fp-new-password" name="new_password" type="password" placeholder="Ilagay ang bagong password" autocomplete="new-password" minlength="8" maxlength="72" required>
                    <button type="button" class="fp-toggle-visibility" data-fp-toggle="fp-new-password" aria-label="Ipakita ang password">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                <p class="fp-hint">Hindi bababa sa 8 character.</p>

                <label class="field-label" for="fp-confirm-password">Kumpirmahin ang Password</label>
                <div class="fp-password-field">
                    <input id="fp-confirm-password" name="confirm_password" type="password" placeholder="Ulitin ang bagong password" autocomplete="new-password" minlength="8" maxlength="72" required>
                    <button type="button" class="fp-toggle-visibility" data-fp-toggle="fp-confirm-password" aria-label="Ipakita ang password">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>

                <button class="btn btn-primary auth-submit" type="submit">I-reset ang Password</button>
            </form>

            <div class="auth-form fp-step fp-success" data-fp-step="success">
                <div class="fp-success-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="m4 12 6 6L20 6"></path></svg>
                </div>
                <h1>Matagumpay na Na-reset!</h1>
                <p class="form-subtitle">Pwede ka nang mag-login gamit ang iyong bagong password.</p>
                <a class="btn btn-primary auth-submit" href="login_reg.php">Mag-login Ngayon</a>
            </div>
        </section>
    </main>
</body>

</html>
