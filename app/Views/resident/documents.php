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
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal"><i class="fa-solid fa-upload me-1"></i>Add Document</button>
            <span class="resident-home-pill"><i class="fa-solid fa-house me-1"></i><?= htmlspecialchars(($member['wing_name'] ?? '') . '-' . ($member['flat_number'] ?? 'Home')) ?></span>
        </div>
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
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="/documents/<?= (int) $document['id'] ?>/file" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i></a>
                                <form method="post" action="/resident/documents/<?= (int) $document['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Remove this document?');">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
<div class="modal fade app-form-modal" id="uploadDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><div><div class="resident-eyebrow">Documents</div><h5 class="modal-title">Add Document</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="post" action="/resident/documents" enctype="multipart/form-data">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="modal-body"><p class="resident-form-note mb-4">Upload a JPG, PNG, or PDF document for your resident profile.</p>
                <div class="mb-3"><label class="form-label">Document Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" placeholder="e.g. Aadhaar Card, Ownership Proof" required></div>
                <div><label class="form-label">File <span class="text-danger">*</span></label><input type="file" name="document" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit"><i class="fa-solid fa-upload me-1"></i>Upload Document</button></div>
        </form>
    </div></div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
