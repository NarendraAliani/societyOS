<?php
$pageTitle = 'Add Vehicle';
ob_start();
?>
<p><a href="<?= $returnToMember ? '/members/' . $returnToMember : '/vehicles' ?>">&laquo; Back to <?= $returnToMember ? 'Resident' : 'Vehicles' ?></a></p>
<div class="card border-0 shadow-sm" style="max-width: 560px;">
    <div class="card-body">
        <form method="post" action="/vehicles">
            <?= \App\Helpers\Csrf::field() ?>
            <?php if ($returnToMember): ?>
                <input type="hidden" name="return_to_member" value="<?= (int) $returnToMember ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Resident *</label>
                    <select name="member_id" class="form-select" required <?= $returnToMember ? 'disabled' : '' ?>>
                        <option value="">Select resident</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= (int) $member['id'] ?>" <?= $returnToMember === (int) $member['id'] ? 'selected' : '' ?>><?= htmlspecialchars($member['name'] . ' (' . $member['wing_name'] . '-' . $member['flat_number'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($returnToMember): ?>
                        <input type="hidden" name="member_id" value="<?= (int) $returnToMember ?>">
                        <div class="form-text">Adding a vehicle for this resident.</div>
                    <?php endif; ?>
                </div>
                <div class="col-6">
                    <label class="form-label">Type *</label>
                    <select name="vehicle_type" class="form-select" required>
                        <option value="four_wheeler">4-Wheeler</option>
                        <option value="two_wheeler">2-Wheeler</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Registration Number *</label>
                    <input type="text" name="registration_number" class="form-control" required>
                </div>
                <div class="col-4">
                    <label class="form-label">Make</label>
                    <input type="text" name="make" class="form-control">
                </div>
                <div class="col-4">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control">
                </div>
                <div class="col-4">
                    <label class="form-label">Color</label>
                    <input type="text" name="color" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Add Vehicle</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
