<?php
$pageTitle = 'Add Resident';
ob_start();
?>
<p><a href="/members">&laquo; Back to Residents</a></p>
<div class="card border-0 shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="post" action="/members">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Flat *</label>
                    <select name="flat_id" class="form-select" required>
                        <option value="">Select flat</option>
                        <?php foreach ($flats as $flat): ?>
                            <option value="<?= (int) $flat['id'] ?>"><?= htmlspecialchars($flat['wing_name'] . '-' . $flat['flat_number']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Type *</label>
                    <select name="member_type" class="form-select" required>
                        <option value="owner">Owner</option>
                        <option value="tenant">Tenant</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Phone *</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Alternate Phone</label>
                    <input type="text" name="alternate_phone" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Move-in Date</label>
                    <input type="date" name="move_in_date" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Add Resident</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
