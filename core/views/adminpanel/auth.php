<div class="position-absolute top-0 end-0">
    <img src="/template/views/dashboard/assets/images/auth-card-bg.svg" class="auth-card-bg-img" alt="auth-card-bg">
</div>
<div class="position-absolute bottom-0 start-0" style="transform: rotate(180deg)">
    <img src="/template/views/dashboard/assets/images/auth-card-bg.svg" class="auth-card-bg-img" alt="auth-card-bg">
</div>

<div class="auth-box overflow-hidden align-items-center d-flex">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-5 col-md-6 col-sm-8">
                <div class="card p-4">
                    <div class="auth-brand text-center mb-2">
                        <a href="/" class="logo-dark">CODERS</a>
                        <a href="/" class="logo-light">CODERS</a>
                        <h4 class="fw-bold text-dark mt-3">Admin Panel Login</h4>
                        <p class="text-muted w-lg-75 mx-auto">Use your admin panel credentials.</p>
                    </div>

                    <?php if(!empty($message)): ?>
                        <div class="alert alert-<?= $message_type === 'ok' ? 'success' : 'danger' ?> text-center">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-semibold py-2">Sign In</button>
                        </div>
                    </form>
                </div>

                <p class="text-center text-muted mt-4 mb-0">
                    <script>document.write(new Date().getFullYear())</script>
                    <span class="fw-semibold">CODERS adminpanel</span>
                </p>
            </div>
        </div>
    </div>
</div>

