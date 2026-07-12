<?php
$pageTitle = 'Residents';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Residents</h5>
    <a href="/members/create" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i>Add Resident</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead><tr><th>Name</th><th>Flat</th><th>Type</th><th>Phone</th><th>Status</th><th class="text-center">Members</th><th class="text-center">Vehicles</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($members as $member): ?>
                <tr>
                    <td><a href="/members/<?= (int) $member['id'] ?>"><?= htmlspecialchars($member['name']) ?></a></td>
                    <td><?= htmlspecialchars($member['wing_name'] . '-' . $member['flat_number']) ?></td>
                    <td><span class="badge bg-<?= $member['member_type'] === 'tenant' ? 'info' : 'primary' ?>"><?= htmlspecialchars($member['member_type']) ?></span></td>
                    <td><?= htmlspecialchars($member['phone']) ?></td>
                    <td><span class="badge bg-<?= $member['status'] === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($member['status']) ?></span></td>
                    <td class="text-center"><span class="badge bg-light text-dark border"><?= (int) $member['member_count'] ?></span></td>
                    <td class="text-center"><span class="badge bg-light text-dark border"><?= (int) $member['vehicle_count'] ?></span></td>
                    <td class="text-end"><a href="/members/<?= (int) $member['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($members)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No residents yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
