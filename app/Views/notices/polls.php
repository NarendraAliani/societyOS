<?php
$pageTitle = 'Polls';
ob_start();
?>
<p><a href="/notices">&laquo; Back to Notice Board</a></p>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Question</th><th>Closes</th></tr></thead>
                    <tbody>
                    <?php foreach ($polls as $poll): ?>
                        <tr>
                            <td><a href="/notices/polls/<?= (int) $poll['id'] ?>"><?= htmlspecialchars($poll['question']) ?></a></td>
                            <td><?= htmlspecialchars($poll['closes_at'] ?? 'No deadline') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($polls)): ?>
                        <tr><td colspan="2" class="text-center text-muted py-4">No polls yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Create Poll</h6>
                <form method="post" action="/notices/polls">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Question *</label>
                        <input type="text" name="question" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Options (at least 2) *</label>
                        <input type="text" name="options[]" class="form-control mb-2" placeholder="Option 1" required>
                        <input type="text" name="options[]" class="form-control mb-2" placeholder="Option 2" required>
                        <input type="text" name="options[]" class="form-control mb-2" placeholder="Option 3 (optional)">
                        <input type="text" name="options[]" class="form-control" placeholder="Option 4 (optional)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Closes On</label>
                        <input type="datetime-local" name="closes_at" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Poll</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
