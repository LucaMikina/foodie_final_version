<?php
$naslovStranice = 'O nama';
require __DIR__ . '/../layouts/header.php';
?>

<header class="hero-v2 py-5 bg-white">
    <div class="container py-3 text-center">
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-medium mb-3 d-inline-flex align-items-center gap-2">
            <i class="bi bi-info-circle"></i> O nama
        </span>
        <h1 class="h2 fw-bold text-dark mb-2">Tko smo mi?</h1>
        <p class="text-secondary mx-auto" style="max-width: 34rem;">
            Foodie je nastao s jednostavnom idejom - povezati vas s najboljim lokalnim restoranima
            i donijeti vam omiljenu hranu direktno na vrata, brzo i jednostavno.
        </p>
    </div>
</header>

<main class="py-5" style="background-color: #fcfcfc;">
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center">
                    <span class="support-icon-circle mx-auto mb-3"><i class="bi bi-rocket-takeoff"></i></span>
                    <h2 class="h5 fw-bold text-dark">Naša misija</h2>
                    <p class="text-secondary mb-0">Olakšati naručivanje hrane i povezati kupce s lokalnim restoranima na jednom mjestu.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center">
                    <span class="support-icon-circle mx-auto mb-3"><i class="bi bi-people"></i></span>
                    <h2 class="h5 fw-bold text-dark">Naša zajednica</h2>
                    <p class="text-secondary mb-0">Podržavamo lokalne restorane, dostavljače i tisuće zadovoljnih korisnika svaki dan.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center">
                    <span class="support-icon-circle mx-auto mb-3"><i class="bi bi-lightning-charge"></i></span>
                    <h2 class="h5 fw-bold text-dark">Naša brzina</h2>
                    <p class="text-secondary mb-0">Prosječno vrijeme dostave od samo 30 minuta, uz pouzdano praćenje narudžbe.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
