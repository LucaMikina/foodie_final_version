<?php
$naslovStranice = 'Restorani';
$aktivnaKategorija = (int) ($_GET['kategorija_id'] ?? 0);
$pojam = trim((string) ($_GET['pojam'] ?? ''));
require __DIR__ . '/../layouts/header.php';
?>

<main class="wolt-restaurants-page">
    <section class="wolt-list-hero">
        <div class="container">
            <div class="wolt-list-heading">
                <div>
                    <span class="wolt-eyebrow"><i class="bi bi-geo-alt-fill"></i> Mostar</span>
                    <h1><?= $pojam !== '' ? 'Rezultati za „' . htmlspecialchars($pojam) . '“' : 'Restorani i hrana za tebe' ?></h1>
                    <p>Otkrij ponudu lokalnih restorana i pronađi baš ono što ti se danas jede.</p>
                </div>
                <span class="wolt-result-count"><strong><?= count($podaci['restorani']) ?></strong> restorana</span>
            </div>

            <form method="get" class="wolt-main-search" role="search">
                <input type="hidden" name="stranica" value="restorani">
                <?php if ($aktivnaKategorija > 0): ?>
                    <input type="hidden" name="kategorija_id" value="<?= $aktivnaKategorija ?>">
                <?php endif; ?>
                <i class="bi bi-search"></i>
                <input type="search" name="pojam" placeholder="Pretraži restorane..." value="<?= htmlspecialchars($pojam) ?>" autocomplete="off">
                <?php if ($pojam !== ''): ?>
                    <a class="wolt-search-clear" href="?stranica=restorani<?= $aktivnaKategorija ? '&kategorija_id=' . $aktivnaKategorija : '' ?>" aria-label="Očisti pretragu"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
                <button type="submit">Pretraži</button>
            </form>
        </div>
    </section>

    <section class="wolt-category-strip" aria-label="Kategorije hrane">
        <div class="container">
            <div class="wolt-category-scroller">
                <?php
                $allQuery = ['stranica' => 'restorani'];
                if ($pojam !== '') {
                    $allQuery['pojam'] = $pojam;
                }
                ?>
                <a href="?<?= htmlspecialchars(http_build_query($allQuery)) ?>" class="wolt-category-tile <?= $aktivnaKategorija === 0 ? 'active' : '' ?>">
                    <span class="wolt-category-art"><img src="<?= foodieKategorijaIkona('sve') ?>" alt=""></span>
                    <span>Sve</span>
                </a>

                <?php foreach (($podaci['kategorije'] ?? []) as $kat): ?>
                    <?php
                    $katId = (int) $kat['id'];
                    $query = ['stranica' => 'restorani', 'kategorija_id' => $katId];
                    if ($pojam !== '') {
                        $query['pojam'] = $pojam;
                    }
                    ?>
                    <a href="?<?= htmlspecialchars(http_build_query($query)) ?>" class="wolt-category-tile <?= $aktivnaKategorija === $katId ? 'active' : '' ?>">
                        <span class="wolt-category-art"><img src="<?= foodieKategorijaIkona($kat['naziv']) ?>" alt=""></span>
                        <span><?= htmlspecialchars($kat['naziv']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="wolt-list-content">
        <div class="container">
            <div class="wolt-section-title-row">
                <div>
                    <h2><?= $aktivnaKategorija > 0 ? 'Odabrana kategorija' : 'Preporučeno za tebe' ?></h2>
                    <p><?= $pojam !== '' ? 'Restorani koji odgovaraju tvojoj pretrazi.' : 'Pregledaj restorane i otvori jelovnik jednim klikom.' ?></p>
                </div>
            </div>

            <?php if (empty($podaci['restorani'])): ?>
                <div class="wolt-empty-state">
                    <span><i class="bi bi-search"></i></span>
                    <h3>Nismo pronašli restoran</h3>
                    <p>Pokušaj s drugim pojmom ili odaberi drugu kategoriju hrane.</p>
                    <a href="?stranica=restorani" class="btn btn-primary rounded-pill">Prikaži sve restorane</a>
                </div>
            <?php else: ?>
                <div class="wolt-restaurant-grid">
                    <?php foreach ($podaci['restorani'] as $restoran): ?>
                        <a href="<?= BASE_URL ?>/public/index.php?stranica=restoran&id=<?= (int) $restoran['id'] ?>" class="wolt-restaurant-card">
                            <div class="wolt-restaurant-media">
                                <img src="<?= !empty($restoran['slika']) ? BASE_URL . '/public/' . htmlspecialchars($restoran['slika']) : BASE_URL . '/public/assets/img/hero/pizza.jpg' ?>"
                                     alt="<?= htmlspecialchars($restoran['naziv']) ?>">
                                <span class="wolt-delivery-badge"><i class="bi bi-bicycle"></i> Dostava</span>
                            </div>
                            <div class="wolt-restaurant-info">
                                <div class="wolt-restaurant-title-row">
                                    <h3><?= htmlspecialchars($restoran['naziv']) ?></h3>
                                    <span class="wolt-open-arrow"><i class="bi bi-arrow-up-right"></i></span>
                                </div>
                                <p><i class="bi bi-geo-alt"></i> <span><?= htmlspecialchars($restoran['adresa']) ?></span></p>
                                <div class="wolt-restaurant-bottom">
                                    <span><i class="bi bi-bag-check"></i> Pogledaj jelovnik</span>
                                    <span class="wolt-dot-separator"></span>
                                    <span>Foodie</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
