<?php $pageTitle='Platform Admin Login'; ob_start(); ?>
<div class="row justify-content-center py-5">
    <div class="col-md-5 col-lg-4">
        <div class="text-center mb-4">
            <i class="fa-solid fa-building-shield fa-2x mb-2"></i>
            <h4 class="mb-1">SocietyOS Platform</h4>
            <p class="text-muted mb-0">Platform administrator sign in</p>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" action="/platform/login">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required autocomplete="current-password">
                    </div>
                    <button class="btn btn-primary w-100">Sign in</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/platform.php'; ?>
