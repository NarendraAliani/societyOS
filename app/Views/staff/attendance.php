<?php
$pageTitle = 'Attendance';
ob_start();
$date = $_GET['date'] ?? date('Y-m-d');
?>
<p><a href="/staff">&laquo; Back to Staff</a></p>

<form method="get" action="/staff/attendance" class="mb-3" style="max-width: 220px;">
    <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="/staff/attendance">
            <?= \App\Helpers\Csrf::field() ?>
            <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($date) ?>">
            <table class="table align-middle">
                <thead><tr><th>Staff</th><th>Designation</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['designation'] ?? '-') ?></td>
                        <td>
                            <select name="status[<?= (int) $row['staff_id'] ?>]" class="form-select form-select-sm" style="max-width: 160px;">
                                <?php foreach (['present', 'absent', 'half_day', 'leave'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">No active staff.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <?php if (!empty($rows)): ?>
                <button type="submit" class="btn btn-primary">Save Attendance</button>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
