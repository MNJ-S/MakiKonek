<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MakiKonek</title>
    <link rel="stylesheet" href="assets/css/login.css?v=20260529m">
    <script defer src="assets/js/public.js?v=20260529c"></script>
</head>

<body class="auth-page">
    <main class="auth-shell">
        <!-- Community intro -->
        <section class="auth-intro" aria-label="MakiKonek introduction">
            <div class="welcome-ribbon" aria-label="Welcome message">
                <img src="assets/img/green-eco-banner.png" alt="" aria-hidden="true">
            </div>

            <img class="intro-logo" src="assets/img/logo2-makikonek.png" alt="MakiKonek logo">
            <div class="intro-copy">
                <p>Mag-login para ma-access ang mga serbisyo, anunsyo, at impormasyon ng Barangay Makiling.</p>
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

        <!-- Login form -->
        <section class="auth-card" aria-labelledby="login-title">
            <form class="auth-form" action="#" method="post">
                <h1 id="login-title">Mag-login</h1>
                <p class="form-subtitle">Piliin ang iyong account type</p>

                <fieldset class="role-selector">
                    <legend>Account role</legend>
                    <label>
                        <input type="radio" name="role" value="Residente" checked>
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9.5" cy="7" r="4"></circle>
                                <path d="M19 8v6"></path>
                                <path d="M22 11h-6"></path>
                            </svg>Residente</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="Opisyal">
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 21h18"></path>
                                <path d="M5 21V9l7-4 7 4v12"></path>
                                <path d="M9 21v-7"></path>
                                <path d="M15 21v-7"></path>
                                <path d="M9 9h6"></path>
                                <path d="M12 5V3"></path>
                            </svg>Opisyal</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="SK">
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>SK</span>
                    </label>
                    <label>
                        <input type="radio" name="role" value="Admin">
                        <span><svg class="role-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>Admin</span>
                    </label>
                </fieldset>

                <label class="field-label" for="email">Email Address</label>
                <input id="email" name="email" type="email" placeholder="Ilagay ang iyong email" autocomplete="email">

                <label class="field-label" for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Ilagay ang iyong password" autocomplete="current-password">

                <div class="form-row">
                    <label class="check-label"><input type="checkbox" name="remember"> Tandaan ako</label>
                    <a href="#">Nakalimutan ang password?</a>
                </div>

                <button class="btn btn-primary auth-submit" type="submit">Mag-login</button>

                <p class="auth-switch">Wala pang account? <a href="signup.php" data-auth-transition>Magrehistro dito.</a></p>
                <a class="btn btn-outline auth-back" href="public/index.php">← Bumalik sa Homepage</a>
            </form>
        </section>
    </main>
</body>

</html>