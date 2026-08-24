<?php
$naslovStranice = 'Registracija';
require __DIR__ . '/../layouts/header.php';
?>

<main class="py-5" style="background-color: #fcfcfc; min-height: 70vh;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card auth-card shadow-sm p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="auth-icon-circle mb-3"><i class="bi bi-person-plus"></i></span>
                        <p class="eyebrow mb-1 text-uppercase small fw-semibold" style="color: var(--foodie-olive); letter-spacing: 0.08em;">Pridruži se</p>
                        <h1 class="h3 fw-bold text-dark mb-0">Registracija</h1>
                    </div>

                    <form method="post" action="<?= BASE_URL ?>/public/index.php?stranica=registracija">
                        <?= Csrf::field() ?>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="ime">Ime</label>
                                <input type="text" class="form-control" id="ime" name="ime" required autofocus>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="prezime">Prezime</label>
                                <input type="text" class="form-control" id="prezime" name="prezime" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="lozinka">Lozinka</label>
                            <input type="password" class="form-control" id="lozinka" name="lozinka" minlength="6" required>
                            <div class="form-text">Najmanje 6 znakova.</div>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 fw-semibold shadow-sm">Registriraj se</button>
                    </form>

                    <p class="text-center text-muted small mt-4 mb-0">
                        Već imaš račun? <a href="<?= BASE_URL ?>/public/index.php?stranica=login" class="fw-semibold text-decoration-none">Prijavi se</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
