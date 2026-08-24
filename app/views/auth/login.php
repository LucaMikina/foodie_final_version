<?php
$naslovStranice = 'Prijava';
require __DIR__ . '/../layouts/header.php';
?>

<main class="py-5" style="background-color: #fcfcfc; min-height: 70vh;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card shadow-sm p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="auth-icon-circle mb-3"><i class="bi bi-person-check"></i></span>
                        <p class="eyebrow mb-1 text-uppercase small fw-semibold" style="color: var(--foodie-olive); letter-spacing: 0.08em;">Dobrodošli natrag</p>
                        <h1 class="h3 fw-bold text-dark mb-0">Prijava</h1>
                    </div>

                    <form method="post" action="<?= BASE_URL ?>/public/index.php?stranica=login">
                        <?= Csrf::field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="lozinka">Lozinka</label>
                            <input type="password" class="form-control" id="lozinka" name="lozinka" required>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-semibold shadow-sm">Prijavi se</button>
                    </form>

                    <p class="text-center text-muted small mt-4 mb-0">
                        Nemaš račun? <a href="<?= BASE_URL ?>/public/index.php?stranica=registracija" class="fw-semibold text-decoration-none">Registriraj se</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
