<?php

$naslovStranice = $naslovStranice ?? 'Foodie';
$pageSlug = preg_replace('/[^a-z0-9-]+/i', '-', (string) ($_GET['stranica'] ?? 'pocetna'));
$bodyClass = trim(($bodyClass ?? '') . ' foodie-wolt page-' . strtolower($pageSlug));
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#009de0">
    <title><?= htmlspecialchars($naslovStranice) ?> | Foodie</title>
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token()) ?>">
    <script>
window.APP_BASE_URL = <?= json_encode(BASE_URL) ?>;
window.GEOAPIFY_API_KEY = <?= json_encode(defined('GEOAPIFY_API_KEY') ? GEOAPIFY_API_KEY : '') ?>;
</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.css">

    <?php $mainCssVersion = @filemtime(APP_ROOT . '/../public/styles/main.css') ?: time(); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/styles/main.css?v=<?= $mainCssVersion ?>">
    <?php if (str_contains($bodyClass, 'homepage-landing')): ?>
        <?php $homeCssVersion = @filemtime(APP_ROOT . '/../public/styles/home.css') ?: time(); ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/styles/home.css?v=<?= $homeCssVersion ?>">
    <?php endif; ?>
    <?php $woltCssVersion = @filemtime(APP_ROOT . '/../public/styles/wolt.css') ?: time(); ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/styles/wolt.css?v=<?= $woltCssVersion ?>">
    <?php if (in_array(strtolower($pageSlug), ['kosarica', 'racun'], true)): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
        <?php $checkoutCssVersion = @filemtime(APP_ROOT . '/../public/styles/checkout.css') ?: time(); ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/styles/checkout.css?v=<?= $checkoutCssVersion ?>">
    <?php endif; ?>
    <?php if (in_array(strtolower($pageSlug), ['moj-restoran', 'restoran-profil', 'restoran-forma', 'jelo-forma'], true)): ?>
        <?php $restaurantPortalCssVersion = @filemtime(APP_ROOT . '/../public/styles/restaurant-portal.css') ?: time(); ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/styles/restaurant-portal.css?v=<?= $restaurantPortalCssVersion ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/styles/print.css" media="print">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<nav class="navbar navbar-expand-lg navbar-foodie sticky-top py-3 py-lg-4">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/public/index.php"><?php $velicinaLoga = 'sm'; require __DIR__ . '/../partials/brand-mark.php'; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#foodieNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="foodieNav">
            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['stranica'] ?? 'pocetna') === 'pocetna' ? 'active' : '' ?>" href="<?= BASE_URL ?>/public/index.php">Početna</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['stranica'] ?? '') === 'restorani' ? 'active' : '' ?>" href="<?= BASE_URL ?>/public/index.php?stranica=restorani">Restorani</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['stranica'] ?? '') === 'onama' ? 'active' : '' ?>" href="<?= BASE_URL ?>/public/index.php?stranica=onama">O nama</a>
                </li>

                <?php if (Auth::hasPermission('narucivanje')): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" aria-label="Košarica" title="Košarica" href="<?= BASE_URL ?>/public/index.php?stranica=kosarica">
                            <i class="bi bi-cart3 fs-5"></i>
                            <span class="badge bg-primary rounded-pill js-kosarica-brojac d-none" style="font-size: 0.6rem;">0</span>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item dropdown">
                    <button class="btn nav-link dropdown-toggle" id="userMenu" title="Korisnički izbornik" aria-label="Korisnički izbornik" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person fs-5"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <?php if (Auth::check()): ?>
                            <li><span class="dropdown-item-text text-muted small">Bok, <?= htmlspecialchars($_SESSION['ime'] ?? '') ?></span></li>
                            <?php if (Auth::hasPermission('narucivanje')): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/public/index.php?stranica=moje-narudzbe">Moje narudžbe</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/public/index.php?stranica=podrska">Pomoć i podrška</a></li>

                            <?php if (Auth::hasAnyRole(['restoran', 'administrator'])): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/public/index.php?stranica=moj-restoran"><i class="bi bi-shop-window"></i> Restoran portal</a></li>
                            <?php endif; ?>

                            <?php if (Auth::hasRole('dostavljac')): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/public/index.php?stranica=dostupne-dostave">Dostave</a></li>
                            <?php endif; ?>

                            <?php if (Auth::hasAnyRole(['administrator', 'superadministrator'])): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/public/index.php?stranica=admin-korisnici">Upravljanje korisnicima</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/public/index.php?stranica=admin-narudzbe">Sve narudžbe</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/public/index.php?stranica=restoran-forma">Dodaj restoran</a></li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/public/index.php?stranica=odjava">Odjava</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item text-success" href="<?= BASE_URL ?>/public/index.php?stranica=login">Prijava</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/public/index.php?stranica=registracija">Registracija</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="toast-container position-fixed top-0 end-0 p-3" id="foodie-toasts" style="z-index: 1080;"></div>

<?php if (!empty($_SESSION['greske'])): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach ($_SESSION['greske'] as $greska): ?>
                    <li><?= htmlspecialchars($greska) ?></li>
                <?php endforeach; unset($_SESSION['greske']); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($_GET['poruka']) && $_GET['poruka'] === 'registracija_uspjesna'): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Registracija uspješna! Sad se možeš prijaviti.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>
