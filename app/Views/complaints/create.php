<?php
$pageTitle = 'Register Complaint';
ob_start();
?>
<p><a href="/complaints">&laquo; Back to Complaints</a></p>
<div class="card border-0 shadow-sm" style="max-width: 640px;">
    <div class="card-body">
        <form method="post" action="/complaints">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Resident *</label>
                    <select name="member_id" class="form-select" required>
                        <option value="">Select resident</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= (int) $member['id'] ?>"><?= htmlspecialchars($member['name'] . ' (' . $member['wing_name'] . '-' . $member['flat_number'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">The flat is taken from the selected resident automatically.</div>
                </div>
                <div class="col-6">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Subject *</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Register Complaint</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
