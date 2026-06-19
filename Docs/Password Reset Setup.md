# MakiKonek Password Reset Setup

## 1. Update the database

Run `Database/migrations/20260620_password_reset_otps.sql` against the live database.

The reset flow does not add `new_password` or `confirm_password` columns:

- `username` already exists in `users`.
- The final password replaces `users.password`.
- `confirm_password` is validation-only and must never be stored.
- OTP records are temporary rows in `password_reset_otps`.

## 2. Configure Gmail

1. Sign in to `makikonek.support@gmail.com`.
2. Enable Google Account 2-Step Verification.
3. Open Google App Passwords and create one named `MakiKonek`.
4. Copy the generated 16-character App Password.
5. Open `Web_App/includes/mail_config.local.php`.
6. Paste the App Password into `MAIL_PASSWORD` without spaces.

Do not use the normal Gmail password and do not commit
`mail_config.local.php`.

For a hosted deployment, use these environment variables instead:

- `MAKIKONEK_MAIL_HOST=smtp.gmail.com`
- `MAKIKONEK_MAIL_PORT=587`
- `MAKIKONEK_MAIL_ENCRYPTION=tls`
- `MAKIKONEK_MAIL_USERNAME=makikonek.support@gmail.com`
- `MAKIKONEK_MAIL_PASSWORD=your-google-app-password`
- `MAKIKONEK_MAIL_FROM_ADDRESS=makikonek.support@gmail.com`
- `MAKIKONEK_MAIL_FROM_NAME=MakiKonek Support`

## 3. Customize the email

Edit `Web_App/email_templates/password_reset_otp.php`.

The template receives:

- `$recipientName`
- `$otp`
- `$expiresInMinutes`
- `$logoCid`

## 4. Test

1. Open `Web_App/forgot_password.php`.
2. Enter an existing resident, official, or SK username/email.
3. Check the account email for the six-digit OTP.
4. Verify within five minutes.
5. Enter and confirm the new password.
6. Log in using the new password.
