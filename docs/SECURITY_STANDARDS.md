# Authentication CAPTCHA Standard

## Global project rule

Every user-facing authentication login must require a server-validated CAPTCHA challenge before credentials are accepted.

This applies to:
- normal application/user login;
- platform/tenant administration login;
- every future authentication surface added to this project;
- future projects using this project standard unless a stronger centrally managed CAPTCHA service replaces it.

## Implementation standard

1. Generate a random 6-character alphanumeric challenge on the server.
2. Exclude visually ambiguous characters where practical.
3. Keep the challenge in the server-side session; never trust a client-provided expected answer.
4. Expire challenges after 5 minutes.
5. Treat each challenge as one-time and regenerate it after every verification attempt.
6. Compare the normalized submitted value server-side using a constant-time comparison.
7. Run CAPTCHA verification before credential validation/rate-limit checks so automated password attacks cannot bypass the challenge.
8. Do not use a third-party CAPTCHA provider unless explicitly required; the default implementation must work without external services.
9. Keep existing CSRF protection and login rate limiting; CAPTCHA is an additional control, not a replacement.
10. Add a regression contract whenever a new login surface is introduced.

The reusable implementation lives in app/Helpers/Captcha.php, with the shared form fragment in app/Views/components/captcha.php.


## Email password-reset standard

Every authenticated product must provide a Forgot Password flow that verifies ownership through the user's registered email address before allowing a password change.

Required controls:
1. Reset requests must not reveal whether an email/account exists (anti-enumeration response).
2. Reset requests must use CSRF protection and CAPTCHA.
3. Generate a cryptographically random, single-use reset token; store only its hash in the database.
4. Reset links expire after 60 minutes.
5. Reset links are delivered only to the registered email address through configured SMTP.
6. Use PHPMailer/SMTP (or an equivalent maintained SMTP library); do not build raw email headers or expose SMTP credentials in source code.
7. Rate-limit reset requests (default: 5 per account/email per hour).
8. Mark a token used atomically and invalidate other outstanding reset tokens after successful reset.
9. Invalidate existing application sessions after a successful user password reset when session tracking is available.
10. Use generic error/success messaging so account existence and mail delivery state are not disclosed to the requester.
11. The same standard applies to platform administrators and every future login/authentication surface.
12. Production deployments must document any required password-reset database migration; live database migrations require backup and explicit operational approval.

The reusable implementation is app/Services/PasswordResetService.php + app/Helpers/Mailer.php, with user and platform reset screens under app/Views/auth/ and app/Views/platform/.
