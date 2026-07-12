<?php
$pageTitle = 'Society Profile';
ob_start();
?>
<div class="card border-0 shadow-sm" style="max-width: 720px;">
    <div class="card-body">
        <form method="post" action="/society">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Society Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($society['name'] ?? '') ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Registration No.</label>
                    <input type="text" name="registration_no" class="form-control" value="<?= htmlspecialchars($society['registration_no'] ?? '') ?>">
                </div>
                <div class="col-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($society['phone'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($society['address'] ?? '') ?>">
                </div>
                <div class="col-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($society['city'] ?? '') ?>">
                </div>
                <div class="col-4">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($society['state'] ?? '') ?>">
                </div>
                <div class="col-4">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($society['pincode'] ?? '') ?>">
                </div>
                <div class="col-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($society['email'] ?? '') ?>">
                </div>
                <div class="col-3">
                    <label class="form-label">GSTIN</label>
                    <input type="text" name="gstin" class="form-control" value="<?= htmlspecialchars($society['gstin'] ?? '') ?>">
                </div>
                <div class="col-3">
                    <label class="form-label">PAN</label>
                    <input type="text" name="pan" class="form-control" value="<?= htmlspecialchars($society['pan'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Save Changes</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
