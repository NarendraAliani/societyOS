<?php
ob_start();
?>
<p><a href="/admin/users">&laquo; Back to Users</a></p>

<div class="card border-0 shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <h6 class="mb-3">System Settings</h6>
        <form method="post" action="/admin/settings">
            <?= \App\Helpers\Csrf::field() ?>

            <h6 class="text-muted small text-uppercase mt-2">Appearance (site default)</h6>
            <p class="form-text mt-0">Individual users can still pick their own Theme/Font Size from the topbar — this sets what new visitors see before they've made their own choice.</p>
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label">Default Theme</label>
                    <select name="theme_default" class="form-select">
                        <option value="light" <?= $settings['theme_default'] === 'light' ? 'selected' : '' ?>>Light</option>
                        <option value="dark" <?= $settings['theme_default'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                        <option value="mid" <?= $settings['theme_default'] === 'mid' ? 'selected' : '' ?>>Mid</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Default Font Size</label>
                    <select name="font_size_default" class="form-select">
                        <option value="small" <?= $settings['font_size_default'] === 'small' ? 'selected' : '' ?>>Small</option>
                        <option value="medium" <?= $settings['font_size_default'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="large" <?= $settings['font_size_default'] === 'large' ? 'selected' : '' ?>>Large</option>
                    </select>
                </div>
            </div>

            <h6 class="text-muted small text-uppercase mt-3">Billing</h6>
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">Late Payment Annual Interest Rate (%)</label>
                    <input type="number" step="0.01" min="0" name="penalty_interest_rate_percent" class="form-control" value="<?= htmlspecialchars((string) $settings['penalty_interest_rate_percent']) ?>" required>
                    <div class="form-text">Applied per day (rate &divide; 365) against a bill's outstanding balance once it's overdue. Takes effect immediately — penalties are recalculated fresh each time a bill or the defaulter report is viewed.</div>
                </div>
            </div>

            <h6 class="text-muted small text-uppercase mt-3">Uploads</h6>
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label">Maximum Upload File Size (MB)</label>
                    <input type="number" min="1" name="upload_max_size_mb" class="form-control" value="<?= htmlspecialchars((string) $settings['upload_max_size_mb']) ?>" required>
                    <div class="form-text">Applies to staff photos/ID proofs, resident documents, and lease agreements.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
