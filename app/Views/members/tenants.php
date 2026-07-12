<?php
$pageTitle = 'Tenants';
ob_start();
?>
<p><a href="/members">&laquo; Back to Residents</a></p>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Flat</th><th>Tenant</th><th>Owner</th><th>Lease Start</th><th>Lease End</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($tenants as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['wing_name'] . '-' . $row['flat_number']) ?></td>
                    <td><a href="/members/<?= (int) $row['member_id'] ?>"><?= htmlspecialchars($row['tenant_name']) ?></a> <small class="text-muted"><?= htmlspecialchars($row['tenant_phone'] ?? '') ?></small></td>
                    <td><a href="/members/<?= (int) $row['owner_member_id'] ?>"><?= htmlspecialchars($row['owner_name']) ?></a> <small class="text-muted"><?= htmlspecialchars($row['owner_phone'] ?? '') ?></small></td>
                    <td><?= htmlspecialchars($row['lease_start'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['lease_end'] ?? '-') ?></td>
                    <td>
                        <?php if (!$row['lease_end']): ?>
                            <span class="badge bg-secondary">No end date set</span>
                        <?php else: ?>
                            <?php $daysLeft = (int) ((strtotime($row['lease_end']) - strtotime('today')) / 86400); ?>
                            <?php if ($daysLeft < 0): ?>
                                <span class="badge bg-danger">Expired <?= abs($daysLeft) ?>d ago</span>
                            <?php elseif ($daysLeft <= 30): ?>
                                <span class="badge bg-warning text-dark"><?= $daysLeft ?>d left</span>
                            <?php else: ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($tenants)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No lease details recorded yet. Set them up from a tenant resident's detail page.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
