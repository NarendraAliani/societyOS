<div class="captcha-box mb-3">
    <label class="form-label" for="captcha_code">Security Check</label>
    <div class="captcha-challenge d-flex align-items-center gap-2">
        <div class="captcha-image flex-grow-1"><?= \App\Helpers\Captcha::svg() ?></div>
        <button type="button" class="btn btn-outline-secondary btn-sm captcha-refresh" onclick="window.location.reload()" aria-label="Refresh CAPTCHA">
            Refresh
        </button>
    </div>
    <input
        type="text"
        id="captcha_code"
        name="captcha_code"
        class="form-control mt-2"
        maxlength="6"
        minlength="6"
        autocomplete="off"
        inputmode="text"
        spellcheck="false"
        required
        aria-describedby="captcha-help"
    >
    <div id="captcha-help" class="form-text">Enter the 6-character code shown above. It is case-insensitive.</div>
</div>
