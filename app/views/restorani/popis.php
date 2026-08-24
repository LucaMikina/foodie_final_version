<?php
$naslovStranice = 'Restorani';
require __DIR__ . '/../layouts/header.php';
?>

<style>
    
    .text-gradient {
        background: var(--foodie-gradient);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    
    .hero-collage-wrap {
        position: relative;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hero-blob {
        position: absolute;
        width: 420px; height: 420px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(230,45,21,0.10) 0%, rgba(230,183,26,0.06) 60%, transparent 80%);
        z-index: 0;
    }
    .photo-primary img {
        width: 85%;
        border-radius: 2rem;
        box-shadow: 0 20px 40px rgba(23,19,15,0.10);
        position: relative;
        z-index: 2;
        transition: transform 0.3s ease;
        background: #fff;
        padding: 1.5rem;
    }
    .photo-secondary img {
        position: absolute;
        width: 45%;
        bottom: -20px;
        right: 5%;
        border-radius: 2rem;
        box-shadow: 0 25px 50px rgba(23,19,15,0.16);
        border: 6px solid #fff;
        z-index: 3;
        background: #fff;
        padding: 0.75rem;
    }
    .stat-float-modern {
        position: absolute;
        top: 10%;
        right: -5%;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        padding: 1rem 1.5rem;
        border-radius: 1.5rem;
        box-shadow: 0 15px 35px rgba(23,19,15,0.12);
        z-index: 4;
        animation: float 4s ease-in-out infinite;
        border: 1px solid rgba(255,255,255,0.4);
    }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-12px); }
        100% { transform: translateY(0px); }
    }

    
    .trust-item i {
        font-size: 1.4rem;
        color: var(--foodie-red);
    }

    
    .step-card:hover .step-icon-circle {
        transform: scale(1.1) rotate(5deg);
        background: var(--foodie-gradient);
        color: #fff !important;
    }
    .step-icon-circle {
        width: 75px;
        height: 75px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: rgba(230, 45, 21, 0.08);
        color: var(--foodie-red);
        transition: all 0.3s ease;
    }

    
    .restaurant-card-hover {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(23,19,15,0.05);
    }
    .restaurant-card-hover:hover {
        transform: translateY(-6px);
        box-shadow: 0 1.5rem 3rem rgba(23,19,15,0.10) !important;
    }
    .restaurant-img-wrap {
        height: 220px;
        overflow: hidden;
        background: linear-gradient(135deg, #FFF7E8, #FCEFD2);
    }
    .restaurant-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .restaurant-card-hover:hover .restaurant-img-wrap img {
        transform: scale(1.05);
    }
</style>


<section class="hero-v2 py-5 bg-white overflow-hidden">
    <div class="container py-4 py-lg-5">
        <div class="row align-items-center g-5">

            <div class="col-lg-6 text-center text-lg-start">
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-medium mb-4 d-inline-flex align-items-center gap-2 border border-primary-subtle">
                    <i class="bi bi-lightning-charge-fill" style="color: var(--foodie-gold);"></i> Dostava za 30 minuta ili besplatno!
                </span>

                <h1 class="display-4 fw-bold mb-1 text-dark">Dobrodošli u</h1>
                <div class="mb-4 d-flex justify-content-center justify-content-lg-start">
                    <?php $velicinaLoga = 'lg'; require __DIR__ . '/../partials/brand-mark.php'; ?>
                </div>

                <p class="text-secondary fs-5 mb-5 mx-auto mx-lg-0" style="max-width: 34rem; line-height: 1.6;">
                    Vaša omiljena hrana na klik od vas. Brza dostava, širok izbor restorana
                    i najukusnija jela direktno na vašu adresu.
                </p>

                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start mb-4">
                    <a href="#restorani" class="btn btn-primary btn-lg rounded-pill px-5 fw-semibold shadow-sm">
                        Naruči sada
                    </a>
                    <a href="#kako-naruciti" class="btn btn-light btn-lg rounded-pill px-5 fw-semibold shadow-sm text-secondary">
                        Saznaj više
                    </a>
                </div>

                <div id="weather-widget" class="badge bg-light text-muted rounded-pill px-3 py-2 border shadow-sm fw-normal d-inline-flex align-items-center gap-2 mt-2" data-grad="Mostar">
                    <i class="bi bi-cloud-sun text-secondary"></i>
                    <span>Učitavanje vremena...</span>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-collage-wrap">
                    <div class="hero-blob"></div>
                    <div class="photo-primary text-center">
                        <img src="<?= BASE_URL ?>/public/assets/img/illustrations/pizza.svg" alt="Pizza ilustracija">
                    </div>
                    <div class="photo-secondary">
                        <img src="<?= BASE_URL ?>/public/assets/img/illustrations/burger.svg" alt="Burger ilustracija">
                    </div>
                    <div class="stat-float-modern text-center">
                        <h4 class="fw-bold mb-0 fs-3" style="color: var(--foodie-red);">50k+</h4>
                        <span class="text-muted small fw-medium">Zadovoljnih korisnika</span>
                    </div>
                </div>
            </div>

        </div>

        
        <div class="row g-4 mt-4 pt-4 border-top">
            <div class="col-6 col-md-3 text-center trust-item">
                <i class="bi bi-shield-check d-block mb-2"></i>
                <span class="small fw-semibold text-dark">Sigurno plaćanje</span>
            </div>
            <div class="col-6 col-md-3 text-center trust-item">
                <i class="bi bi-clock-history d-block mb-2"></i>
                <span class="small fw-semibold text-dark">Dostava do 30 min</span>
            </div>
            <div class="col-6 col-md-3 text-center trust-item">
                <i class="bi bi-star d-block mb-2"></i>
                <span class="small fw-semibold text-dark">Provjereni restorani</span>
            </div>
            <div class="col-6 col-md-3 text-center trust-item">
                <i class="bi bi-headset d-block mb-2"></i>
                <span class="small fw-semibold text-dark">Podrška 24/7</span>
            </div>
        </div>
    </div>
</section>


<section id="kako-naruciti" class="py-5 bg-light">
    <div class="container py-5 text-center">
        <h2 class="h1 fw-bold mb-5 text-dark">
            <span class="text-gradient">Kako</span> naručiti?
        </h2>

        <div class="row g-4 g-lg-5">
            <div class="col-md-4 step-card">
                <div class="step-icon-circle mx-auto mb-4 shadow-sm">
                    <i class="bi bi-geo-alt-fill fs-2"></i>
                </div>
                <h3 class="h5 fw-bold text-dark">Recite nam gdje ste</h3>
                <p class="text-secondary small mx-auto" style="max-width: 16rem;">Pokazat ćemo vam najbolje restorane u vašoj neposrednoj blizini.</p>
            </div>

            <div class="col-md-4 step-card">
                <div class="step-icon-circle mx-auto mb-4 shadow-sm">
                    <i class="bi bi-search fs-2"></i>
                </div>
                <h3 class="h5 fw-bold text-dark">Pronađite što želite</h3>
                <p class="text-secondary small mx-auto" style="max-width: 16rem;">Pretražite omiljena jela, specifične restorane ili nove kuhinje.</p>
            </div>

            <div class="col-md-4 step-card">
                <div class="step-icon-circle mx-auto mb-4 shadow-sm">
                    <i class="bi bi-bag-check-fill fs-2"></i>
                </div>
                <h3 class="h5 fw-bold text-dark">Brza dostava</h3>
                <p class="text-secondary small mx-auto" style="max-width: 16rem;">Naručite dostavu ili preuzimanje i pratite status u stvarnom vremenu.</p>
            </div>
        </div>
    </div>
</section>


<main id="restorani" class="py-5" style="background-color: #fcfcfc;">
    <div class="container py-4">

        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5 gap-3">
            <div>
                <h2 class="h2 fw-bold text-dark mb-2">
                    <?= !empty($_GET['pojam']) ? 'Rezultati za "' . htmlspecialchars($_GET['pojam']) . '"' : 'Preporučeni <span class="text-gradient">restorani</span>' ?>
                </h2>
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-medium shadow-sm">
                    Dostupno: <span class="text-dark fw-bold"><?= count($podaci['restorani']) ?></span> restorana
                </span>
            </div>

            <form method="get" class="d-flex">
                <div class="input-group shadow-sm rounded-pill overflow-hidden border border-light-subtle bg-white">
                    <input type="text" name="pojam" class="form-control border-0 px-4 bg-transparent" style="max-width: 300px;"
                           placeholder="Pretraži restorane..." value="<?= htmlspecialchars($_GET['pojam'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary px-4 border-0">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>

        
        <?php if (!empty($podaci['kategorije'])): ?>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="?stranica=pocetna" class="btn btn-sm rounded-pill foodie-category-filter <?= empty($_GET['kategorija_id']) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <span class="foodie-category-icon-badge" aria-hidden="true">
                        <img src="<?= foodieKategorijaIkona('sve') ?>" alt="">
                    </span>
                    <span>Sve</span>
                </a>
                <?php foreach ($podaci['kategorije'] as $kat): ?>
                    <a href="?stranica=pocetna&kategorija_id=<?= (int) $kat['id'] ?>"
                       class="btn btn-sm rounded-pill foodie-category-filter <?= (($_GET['kategorija_id'] ?? '') == $kat['id']) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        <span class="foodie-category-icon-badge" aria-hidden="true">
                            <img src="<?= foodieKategorijaIkona($kat['naziv']) ?>" alt="">
                        </span>
                        <span><?= htmlspecialchars($kat['naziv']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        
        <?php if (empty($podaci['restorani'])): ?>
            <div class="text-center py-5 my-5 bg-white rounded-5 shadow-sm border border-light-subtle">
                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-emoji-frown display-5 text-secondary"></i>
                </div>
                <h4 class="fw-bold text-dark mt-2">Nema rezultata</h4>
                <p class="text-muted">Pokušajte s drugačijim pojmom pretrage.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($podaci['restorani'] as $restoran): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <a href="<?= BASE_URL ?>/public/index.php?stranica=restoran&id=<?= (int) $restoran['id'] ?>" class="text-decoration-none">
                            <div class="card restaurant-card-hover rounded-4 bg-white h-100 p-2">

                                <div class="restaurant-img-wrap rounded-4 position-relative">
                                    <img src="<?= !empty($restoran['slika']) ? BASE_URL . '/public/' . htmlspecialchars($restoran['slika']) : 'https://placehold.co/600x400/FCEFD2/E62D15?text=Foodie' ?>"
                                         alt="<?= htmlspecialchars($restoran['naziv']) ?>">
                                </div>

                                <div class="card-body p-3 mt-1">
                                    <h3 class="h5 fw-bold text-dark mb-1 text-truncate">
                                        <?= htmlspecialchars($restoran['naziv']) ?>
                                    </h3>
                                    <p class="text-muted small mb-0 d-flex align-items-center gap-1">
                                        <i class="bi bi-geo-alt" style="color: var(--foodie-red);"></i>
                                        <span class="text-truncate"><?= htmlspecialchars($restoran['adresa']) ?></span>
                                    </p>
                                </div>

                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
