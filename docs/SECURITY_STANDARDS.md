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
