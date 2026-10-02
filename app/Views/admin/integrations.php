<?php
$pageTitle='Integrations & Payments';
ob_start();
$cfg=$cfg??[];
?>
<p><a href="/admin/settings">&laquo; Back to Settings</a></p>
<div class="row g-3">
<div class="col-xl-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">
<h5>Razorpay Payment Gateway</h5>
<p class="text-muted small">Each society can connect its own Razorpay merchant account. Key secrets remain encrypted in the SocietyOS database and are never rendered back in full.</p>
<form method="post" action="/admin/integrations"><?= \App\Helpers\Csrf::field() ?>
<div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="razorpay_enabled" <?= $cfg['razorpay_enabled']?'checked':'' ?>><label class="form-check-label">Enable Razorpay online payments</label></div>
<label class="form-label">Key ID</label><input class="form-control mb-2" name="razorpay_key_id" placeholder="<?= htmlspecialchars($masked['razorpay_key_id']) ?>">
<label class="form-label">Key Secret</label><input class="form-control mb-2" type="password" name="razorpay_key_secret" autocomplete="new-password" placeholder="<?= htmlspecialchars($masked['razorpay_key_secret']) ?>">
<label class="form-label">Webhook Secret</label><input class="form-control mb-3" type="password" name="razorpay_webhook_secret" autocomplete="new-password" placeholder="<?= htmlspecialchars($masked['razorpay_webhook_secret']) ?>">
<button class="btn btn-primary">Save Razorpay</button>
</form>
<hr><div class="small text-muted">Webhook endpoint: <code><?= htmlspecialchars(rtrim(config()['url'],'/').'/payments/razorpay/webhook?society_id='.(int)\App\Helpers\Auth::societyId()) ?></code></div>
</div></div></div>

<div class="col-xl-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">
<h5>UPI Collection</h5><p class="text-muted small">Configure the society's UPI ID for direct UPI instructions/QR display. Razorpay remains the verified online gateway when enabled.</p>
<form method="post" action="/admin/integrations"><?= \App\Helpers\Csrf::field() ?>
<div class="form-check form-switch mb-3"><input class="form-check-input js-integration-toggle" type="checkbox" name="upi_enabled" data-integration="upi" <?= $cfg['upi_enabled']?'checked':'' ?>><label class="form-check-label">Enable UPI display</label></div>
<label class="form-label">UPI ID / VPA</label><input class="form-control mb-2" name="upi_id" value="<?= htmlspecialchars($cfg['upi_id']) ?>" placeholder="society@bank">
<label class="form-label">UPI Account Name</label><input class="form-control mb-3" name="upi_name" value="<?= htmlspecialchars($cfg['upi_name']) ?>" placeholder="Society Name">
<button class="btn btn-primary">Save UPI</button>
</form>
</div></div></div>

<div class="col-xl-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">
<h5>WhatsApp Cloud API</h5><p class="text-muted small">Use Meta WhatsApp Business Platform credentials. Message templates and recipient opt-in remain governed by WhatsApp's policies.</p>
<form method="post" action="/admin/integrations"><?= \App\Helpers\Csrf::field() ?>
<div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="whatsapp_enabled" <?= $cfg['whatsapp_enabled']?'checked':'' ?>><label class="form-check-label">Enable WhatsApp</label></div>
<label class="form-label">Phone Number ID</label><input class="form-control mb-2" name="whatsapp_phone_number_id" value="<?= htmlspecialchars($cfg['whatsapp_phone_number_id']) ?>">
<label class="form-label">Business Account ID</label><input class="form-control mb-2" name="whatsapp_business_account_id" value="<?= htmlspecialchars($cfg['whatsapp_business_account_id']) ?>">
<label class="form-label">Access Token</label><input class="form-control mb-2" type="password" name="whatsapp_access_token" autocomplete="new-password" placeholder="<?= htmlspecialchars($masked['whatsapp_access_token']) ?>">
<label class="form-label">Webhook Verify Token</label><input class="form-control mb-3" type="password" name="whatsapp_verify_token" autocomplete="new-password" placeholder="<?= htmlspecialchars($masked['whatsapp_verify_token']) ?>">
<button class="btn btn-primary">Save WhatsApp</button>
</form>
<hr>
<form method="post" action="/admin/integrations/whatsapp/test" class="row g-2"><?= \App\Helpers\Csrf::field() ?><div class="col"><input class="form-control" name="recipient" placeholder="9198XXXXXXXX"></div><div class="col-auto"><button class="btn btn-outline-primary">Send Test</button></div></form>
</div></div></div>

<div class="col-xl-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">
<h5>Telegram Bot</h5><p class="text-muted small">Configure one bot per society. The bot can be used for society alerts and operational notifications.</p>
<form method="post" action="/admin/integrations"><?= \App\Helpers\Csrf::field() ?>
<div class="form-check form-switch mb-3"><input class="form-check-input js-integration-toggle" type="checkbox" name="telegram_enabled" data-integration="telegram" <?= $cfg['telegram_enabled']?'checked':'' ?>><label class="form-check-label">Enable Telegram</label></div>
<label class="form-label">Bot Token</label><input class="form-control mb-2" type="password" name="telegram_bot_token" autocomplete="new-password" placeholder="<?= htmlspecialchars($masked['telegram_bot_token']) ?>">
<label class="form-label">Default Chat ID</label><input class="form-control mb-3" name="telegram_default_chat_id" value="<?= htmlspecialchars($cfg['telegram_default_chat_id']) ?>" placeholder="-1001234567890">
<button class="btn btn-primary">Save Telegram</button>
</form>
<hr>
<form method="post" action="/admin/integrations/telegram/test" class="row g-2"><?= \App\Helpers\Csrf::field() ?><div class="col"><input class="form-control" name="chat_id" value="<?= htmlspecialchars($cfg['telegram_default_chat_id']) ?>" placeholder="Chat ID"></div><div class="col-auto"><button class="btn btn-outline-primary">Send Test</button></div></form>
</div></div></div>
</div>
<script>
document.querySelectorAll('.js-integration-toggle').forEach(function(toggle){
    toggle.addEventListener('change', async function(){
        const previous = !toggle.checked;
        const form = toggle.closest('form');
        const csrf = form ? form.querySelector('input[name="_csrf"]') : null;
        toggle.disabled = true;
        try {
            const body = new URLSearchParams();
            body.set('_csrf', csrf ? csrf.value : '');
            body.set('integration', toggle.dataset.integration || '');
            body.set('enabled', toggle.checked ? '1' : '0');
            const response = await fetch('/admin/integrations/toggle', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body:body.toString()});
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.message || 'Could not save integration status.');
        } catch (error) {
            toggle.checked = previous;
            alert(error.message || 'Could not save integration status.');
        } finally {
            toggle.disabled = false;
        }
    });
});
</script>
<div class="alert alert-warning mt-3"><strong>Credential safety:</strong> enter live secrets only in SocietyOS settings. Never commit them to GitHub or put them in frontend JavaScript. Razorpay likewise recommends keeping API secrets out of source control and validating webhook HMAC signatures. </div>
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/app.php';