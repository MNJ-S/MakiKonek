<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | MakiKonek</title>
    <link rel="stylesheet" href="assets/css/signup.css?v=20260529d">
    <script defer src="assets/js/public.js?v=20260528k"></script>
</head>
<body class="auth-page">
    <main class="auth-shell signup-shell">
        <!-- Community intro -->
        <section class="auth-intro" aria-label="MakiKonek introduction">
            <img class="intro-logo" src="assets/img/logo-makikonek.png" alt="MakiKonek logo">
            <div class="intro-copy">
                <h1>
                    <span class="brand-word">
                        <span class="maki">Maki</span><span class="konek">Konek</span>
                    </span>
                    Serbisyong Barangay, Mas Madali.
                </h1>
                <p>Gumawa ng account para makagamit ng online services at updates ng Barangay Makiling.</p>
            </div>

            <div class="community-card">
                <div class="community-logos">
                    <img src="assets/img/Barangay_Makiling_Seal.png" alt="Barangay Makiling seal">
                    <img src="assets/img/Barangay_Makiling_SK.jpg" alt="Sangguniang Kabataan Makiling logo">
                </div>
                <div>
                    <strong>Barangay Makiling at SK Makiling</strong>
                    <span>Mabilis, maaasahan, at mas madaling transaksyon para sa komunidad.</span>
                </div>
            </div>
        </section>

        <!-- Sign up form -->
        <section class="auth-card signup-card" aria-labelledby="signup-title">
            <form class="auth-form" action="#" method="post">
                <h1 id="signup-title">Magrehistro</h1>
                <p class="form-subtitle">Ilagay ang iyong impormasyon</p>

                <div class="form-grid">
                    <div>
                        <label class="field-label" for="surname">Surname</label>
                        <input id="surname" name="surname" type="text" placeholder="Dela Cruz" autocomplete="family-name">
                    </div>
                    <div>
                        <label class="field-label" for="given_name">Given Name</label>
                        <input id="given_name" name="given_name" type="text" placeholder="Juan" autocomplete="given-name">
                    </div>
                    <div>
                        <label class="field-label" for="middle_name">Middle Name</label>
                        <input id="middle_name" name="middle_name" type="text" placeholder="Santos">
                    </div>
                    <div>
                        <label class="field-label" for="suffix">Suffix</label>
                        <input id="suffix" name="suffix" type="text" placeholder="Jr.">
                    </div>
                </div>

                <label class="field-label" for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="juandelacruz" autocomplete="username">

                <label class="field-label" for="signup_email">Email</label>
                <input id="signup_email" name="email" type="email" placeholder="juan@example.com" autocomplete="email">

                <label class="field-label" for="new_password">Password</label>
                <input id="new_password" name="password" type="password" placeholder="********" autocomplete="new-password">

                <label class="field-label" for="confirm_password">Confirm Password</label>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="********" autocomplete="new-password">

                <button class="btn btn-primary auth-submit" type="submit">Gumawa ng Account</button>
                <p class="auth-switch">May account na? <a href="login_reg.php">Mag-login dito.</a></p>
                <a class="btn btn-outline auth-back" href="public/index.php">← Bumalik sa Homepage</a>
            </form>
        </section>
    </main>
</body>
</html>
