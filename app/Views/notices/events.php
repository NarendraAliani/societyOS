<?php
$pageTitle = 'Events';
ob_start();
?>
<p><a href="/notices">&laquo; Back to Notice Board</a></p>
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Title</th><th>Venue</th><th>Starts</th><th>Ends</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= htmlspecialchars($event['venue'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($event['starts_at']) ?></td>
                            <td><?= htmlspecialchars($event['ends_at'] ?? '-') ?></td>
                            <td class="text-end">
                                <form method="post" action="/notices/events/<?= (int) $event['id'] ?>/delete" onsubmit="return confirm('Remove this event?');">
                                    <?= \App\Helpers\Csrf::field() ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($events)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No events scheduled.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Create Event</h6>
                <form method="post" action="/notices/events">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Venue</label>
                        <input type="text" name="venue" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Starts *</label>
                        <input type="datetime-local" name="starts_at" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ends</label>
                        <input type="datetime-local" name="ends_at" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Event</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
