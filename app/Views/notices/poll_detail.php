<?php
$pageTitle = 'Poll: ' . $poll['question'];
ob_start();
$totalVotes = array_sum(array_column($options, 'vote_count'));
?>
<p><a href="/notices/polls">&laquo; Back to Polls</a></p>
<div class="row g-3">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6><?= htmlspecialchars($poll['question']) ?></h6>
                <?php foreach ($options as $option): ?>
                    <?php $pct = $totalVotes > 0 ? round($option['vote_count'] / $totalVotes * 100) : 0; ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span><?= htmlspecialchars($option['option_text']) ?></span>
                            <span class="text-muted small"><?= (int) $option['vote_count'] ?> vote(s) &middot; <?= $pct ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <p class="text-muted small mt-3">Total votes: <?= $totalVotes ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Cast a Vote</h6>
                <p class="text-muted small">Voting again replaces a resident's earlier choice on this poll.</p>
                <form method="post" action="/notices/polls/<?= (int) $poll['id'] ?>/vote">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Resident *</label>
                        <select name="member_id" class="form-select" required>
                            <option value="">Select resident</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= (int) $member['id'] ?>"><?= htmlspecialchars($member['name'] . ' (' . $member['wing_name'] . '-' . $member['flat_number'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Option *</label>
                        <select name="poll_option_id" class="form-select" required>
                            <?php foreach ($options as $option): ?>
                                <option value="<?= (int) $option['id'] ?>"><?= htmlspecialchars($option['option_text']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Vote</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
