<?php
$naslovStranice = Auth::hasRole('administrator') ? 'Profili restorana' : 'Moj restoran';
require __DIR__ . '/../layouts/header.php';
$restorani = $podaci['restorani'] ?? [];
?>

<main class="restaurant-hub py-5">
    <div class="container py-3 py-lg-4">
        <div class="restaurant-hub-heading d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-4 mb-lg-5">
            <div>
                <span class="restaurant-eyebrow"><i class="bi bi-shop-window"></i> Restaurant Portal</span>
                <h1 class="restaurant-page-title mt-3 mb-2"><?= Auth::hasRole('administrator') ? 'Profili restorana' : 'Moj restoran' ?></h1>
            </div>
            <?php if (Auth::hasRole('administrator')): ?>
                <a href="<?= BASE_URL ?>/public/index.php?stranica=restoran-forma" class="btn restaurant-primary-btn">
                    <i class="bi bi-plus-lg"></i> Dodaj restoran
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($restorani)): ?>
            <section class="restaurant-empty-state">
                <div class="restaurant-empty-icon"><i class="bi bi-shop"></i></div>
                <?php if (Auth::hasRole('administrator')): ?>
                    <h2>Još nema profila restorana</h2>
                    <a href="<?= BASE_URL ?>/public/index.php?stranica=restoran-forma" class="btn restaurant-primary-btn">
                        <i class="bi bi-plus-lg"></i> Kreiraj profil restorana
                    </a>
                <?php else: ?>
                    <h2>Restoran ti još nije dodijeljen</h2>
                    <p>Profil restorana nije dodijeljen.</p>
                    <a href="<?= BASE_URL ?>/public/index.php?stranica=podrska#kontakt" class="btn restaurant-soft-btn">Kontaktiraj administratora</a>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($restorani as $restoran): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="restaurant-profile-card h-100">
                            <div class="restaurant-profile-cover">
                                <?php if (!empty($restoran['slika'])): ?>
                                    <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($restoran['slika']) ?>" alt="<?= htmlspecialchars($restoran['naziv']) ?>">
                                <?php else: ?>
                                    <div class="restaurant-profile-cover-fallback"><i class="bi bi-shop-window"></i></div>
                                <?php endif; ?>
                                <span class="restaurant-open-state <?= $restoran['status'] === 'aktivan' ? 'is-open' : 'is-closed' ?>">
                                    <span></span><?= $restoran['status'] === 'aktivan' ? 'Aktivan' : 'Neaktivan' ?>
                                </span>
                                <?php if ((int) ($restoran['nove_narudzbe'] ?? 0) > 0): ?>
                                    <span class="restaurant-new-orders-badge">
                                        <?= (int) $restoran['nove_narudzbe'] ?> <?= (int) $restoran['nove_narudzbe'] === 1 ? 'nova' : 'novih' ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="restaurant-profile-card-body">
                                <h2><?= htmlspecialchars($restoran['naziv']) ?></h2>
                                <p class="restaurant-address"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($restoran['adresa']) ?></p>
                                <?php if (Auth::hasRole('administrator')): ?>
                                    <p class="restaurant-owner-line"><i class="bi bi-person-badge"></i> <?= htmlspecialchars(trim(($restoran['vlasnik_ime'] ?? '') . ' ' . ($restoran['vlasnik_prezime'] ?? ''))) ?></p>
                                <?php endif; ?>
                                <div class="restaurant-mini-stats">
                                    <div><strong><?= (int) ($restoran['broj_jela'] ?? 0) ?></strong><span>jela</span></div>
                                    <div><strong><?= (int) ($restoran['aktivne_narudzbe'] ?? 0) ?></strong><span>u tijeku</span></div>
                                    <div><strong><?= (int) ($restoran['broj_narudzbi'] ?? 0) ?></strong><span>ukupno</span></div>
                                </div>
                                <a href="<?= BASE_URL ?>/public/index.php?stranica=restoran-profil&id=<?= (int) $restoran['id'] ?>" class="btn restaurant-profile-open-btn w-100">
                                    Otvori profil <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
