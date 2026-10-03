<?php
$pageTitle = 'Visitor Report';
ob_start();
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
?>
<p><a href="/reports">&laquo; Back to Reports</a></p>
<form method="get" action="/reports/visitors" class="row g-2 mb-3" style="max-width: 480px;">
    <div class="col-4"><input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>"></div>
    <div class="col-4"><input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>"></div>
    <div class="col-4"><button type="submit" class="btn btn-sm btn-primary w-100">Filter</button></div>
</form>
<div class="btn-group mb-3">
    <a href="/reports/visitors?from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>&format=csv" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-download me-1"></i>CSV</a>
    <a href="/reports/visitors?from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>&format=pdf" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-pdf me-1"></i>PDF</a>
    <a href="/reports/visitors?from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>&format=xlsx" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-file-excel me-1"></i>Excel</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Check In</th><th>Name</th><th>Flat</th><th>Purpose</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['check_in_at']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['wing_name'] . '-' . $row['flat_number']) ?></td>
                    <td><?= htmlspecialchars($row['purpose'] ?? '-') ?></td>
                    <td><?= ucfirst($row['approval_status']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No visitors in this range.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
