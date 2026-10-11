# SocietyOS 2.0 — Integrations & Payments

## Society-level configuration

Each society has independent settings under **Administration → Integrations**.

### Razorpay
- Enable/disable online payments.
- Key ID.
- Key Secret.
- Webhook Secret.
- Standard Checkout order creation is server-side.
- Checkout success signatures are verified server-side.
- Payment status is fetched from Razorpay and only captured payments are posted to the society bill.
- `payment.captured` webhooks are HMAC-verified and reconciled idempotently.
- Secrets are encrypted at rest with `APP_KEY`.

Webhook URL: `https://<societyos-domain>/payments/razorpay/webhook?society_id=<ID>`

### UPI
- Enable/disable direct UPI display.
- UPI ID/VPA.
- Account name.
- Society residents receive a bill-specific UPI QR generated from the configured VPA and outstanding amount.

### WhatsApp Cloud API
- Enable/disable.
- Phone Number ID.
- Business Account ID.
- Access Token.
- Webhook Verify Token.
- Test message action.
- Verification endpoint: `https://<societyos-domain>/integrations/whatsapp/webhook?society_id=<ID>`

### Telegram
- Enable/disable.
- Bot Token.
- Default Chat ID.
- Test message action.

## Credential rules
Never commit Razorpay, WhatsApp, or Telegram secrets to Git. Only the public Razorpay Key ID may be exposed to checkout JavaScript. API secrets/tokens remain server-side.

## Activation checklist
1. Create/activate the society's Razorpay account.
2. Generate Test Mode API keys.
3. Configure them in SocietyOS.
4. Configure the Razorpay webhook URL and secret.
5. Test a payment using Razorpay Test Mode.
6. Confirm the bill changes only after captured verification.
7. Switch to Live Mode only after the end-to-end test passes.
8. Configure WhatsApp Business Platform credentials and webhook verification.
9. Configure Telegram BotFather token and default chat ID.

## Important deployment note
No production database migration is required for this integration layer because society-level configuration uses the existing tenant-scoped `settings` table. No credentials are stored in source control.