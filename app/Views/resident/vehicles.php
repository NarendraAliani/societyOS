<?php
$pageTitle = 'My Vehicles';
ob_start();
?>
<div class="resident-page">
    <div class="resident-page-header">
        <div>
            <div class="resident-eyebrow">My Home</div>
            <h1 class="h3 mb-1">My Vehicles</h1>
            <p class="resident-subtitle mb-0">Vehicles registered against your resident profile.</p>
        </div>
        <span class="resident-home-pill"><i class="fa-solid fa-house me-1"></i><?= htmlspecialchars($member['flat_number'] ?? 'Home') ?></span>
    </div>
    <section class="resident-section-card">
        <div class="resident-section-title"><span><i class="fa-solid fa-car resident-section-icon"></i>Vehicles</span><span class="badge text-bg-secondary"><?= count($vehicles) ?></span></div>
        <?php if (!$vehicles): ?>
            <div class="resident-empty"><i class="fa-solid fa-car"></i><div>No vehicles are registered for your profile yet.</div></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table resident-table align-middle mb-0">
                    <thead><tr><th>Registration</th><th>Type</th><th>Make / Model</th><th>Color</th></tr></thead>
                    <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($vehicle['registration_number']) ?></td>
                            <td><?= htmlspecialchars(ucfirst((string) $vehicle['vehicle_type'])) ?></td>
                            <td><?= htmlspecialchars(trim(($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '')) ?: '-') ?></td>
                            <td><?= htmlspecialchars($vehicle['color'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
