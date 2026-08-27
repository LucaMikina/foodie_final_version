<?php
$naslovStranice = 'Restorani';

// Pokupi SVE odabrane kategorije kao niz brojeva (radi i za niz i za pojedinacnu vrijednost)
$sirovKategorije = $_GET['kategorija_id'] ?? [];
if (!is_array($sirovKategorije)) {
    $sirovKategorije = [$sirovKategorije];
}
$kategorijaIds = array_values(array_filter(array_map('intval', $sirovKategorije), fn($id) => $id > 0));

$pojam = trim((string) ($_GET['pojam'] ?? ''));
require __DIR__ . '/../layouts/header.php';

// Pomocna funkcija - napravi URL koji DODA ili UKLONI jednu kategoriju iz trenutno odabranih
function foodieKategorijaToggleUrl(array $trenutneKategorije, int $katId, string $pojam): string
{
    if (in_array($katId, $trenutneKategorije, true)) {
        // vec je odabrana -> ukloni je (klik ponovo = deselektuj)
        $nove = array_values(array_diff($trenutneKategorije, [$katId]));
    } else {
        // nije odabrana -> dodaj je
        $nove = array_merge($trenutneKategorije, [$katId]);
    }

    $query = ['stranica' => 'restorani'];
    if ($pojam !== '') {
        $query['pojam'] = $pojam;
    }
    if (!empty($nove)) {
        $query['kategorija_id'] = $nove;
    }
    return '?' . http_build_query($query);
}
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
                <a href="?<?= htmlspecialchars(http_build_query($allQuery)) ?>" class="wolt-category-tile <?= empty($kategorijaIds) ? 'active' : '' ?>">
                    <span class="wolt-category-art"><img src="<?= foodieKategorijaIkona('sve') ?>" alt=""></span>
                    <span>Sve</span>
                </a>

                <?php foreach (($podaci['kategorije'] ?? []) as $kat): ?>
                    <?php
                    $katId = (int) $kat['id'];
                    $jeAktivna = in_array($katId, $kategorijaIds, true);
                    ?>
                    <a href="<?= htmlspecialchars(foodieKategorijaToggleUrl($kategorijaIds, $katId, $pojam)) ?>"
                       class="wolt-category-tile <?= $jeAktivna ? 'active' : '' ?>">
                        <span class="wolt-category-art"><img src="<?= foodieKategorijaIkona($kat['naziv']) ?>" alt=""></span>
                        <span><?= htmlspecialchars($kat['naziv']) ?></span>
                        <?php if ($jeAktivna): ?><i class="bi bi-check-circle-fill wolt-category-check"></i><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="wolt-list-content">
        <div class="container">
            <div class="wolt-section-title-row">
                <div>
                    <h2><?= !empty($kategorijaIds) ? 'Odabrane kategorije' : 'Preporučeno za tebe' ?></h2>
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