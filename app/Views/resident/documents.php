<?php
$pageTitle = 'My Documents';
ob_start();
?>
<div class="resident-page">
    <div class="resident-page-header">
        <div>
            <div class="resident-eyebrow">My Home</div>
            <h1 class="h3 mb-1">My Documents</h1>
            <p class="resident-subtitle mb-0">Documents maintained for your resident profile.</p>
        </div>
        <span class="resident-home-pill"><i class="fa-solid fa-house me-1"></i><?= htmlspecialchars($member['flat_number'] ?? 'Home') ?></span>
    </div>
    <section class="resident-section-card">
        <div class="resident-section-title"><span><i class="fa-solid fa-folder-open resident-section-icon"></i>Documents</span><span class="badge text-bg-secondary"><?= count($documents) ?></span></div>
        <?php if (!$documents): ?>
            <div class="resident-empty"><i class="fa-regular fa-folder-open"></i><div>No documents are available for your profile yet.</div></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table resident-table align-middle mb-0">
                    <thead><tr><th>Document</th><th>Type</th><th>Uploaded</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td><div class="fw-semibold"><?= htmlspecialchars($document['title']) ?></div></td>
                            <td><?= htmlspecialchars(strtoupper((string) ($document['file_type'] ?: 'file'))) ?></td>
                            <td><?= htmlspecialchars(date('d M Y', strtotime($document['created_at']))) ?></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="/documents/<?= (int) $document['id'] ?>/file" target="_blank" rel="noopener"><i class="fa-solid fa-eye me-1"></i>View</a></td>
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
