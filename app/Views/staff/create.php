<?php
$pageTitle = 'Add Staff';
ob_start();
?>
<p><a href="/staff">&laquo; Back to Staff</a></p>
<div class="card border-0 shadow-sm" style="max-width: 560px;">
    <div class="card-body">
        <form method="post" action="/staff" enctype="multipart/form-data">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control" placeholder="e.g. Watchman, Plumber">
                </div>
                <div class="col-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png">
                    <div class="form-text">JPG or PNG, max 5 MB.</div>
                </div>
                <div class="col-6">
                    <label class="form-label">ID Proof</label>
                    <input type="file" name="id_proof" class="form-control" accept="image/jpeg,image/png,application/pdf">
                    <div class="form-text">JPG, PNG, or PDF, max 5 MB.</div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Add Staff</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
