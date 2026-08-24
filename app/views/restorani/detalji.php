<?php
$restoran = $podaci['restoran'] ?? null;
$naslovStranice = $restoran['naziv'] ?? 'Restoran';
$aktivnaKategorija = (int) ($_GET['kategorija_id'] ?? 0);
$mozeNaruciti = Auth::hasPermission('narucivanje');
$prijavljen = Auth::check();
require __DIR__ . '/../layouts/header.php';
?>

<?php if (!$restoran): ?>
    <main class="wolt-page-center">
        <div class="wolt-empty-state">
            <span><i class="bi bi-shop"></i></span>
            <h1>Restoran nije pronađen</h1>
            <p>Ovaj restoran više nije dostupan ili je poveznica neispravna.</p>
            <a href="<?= BASE_URL ?>/public/index.php?stranica=restorani" class="btn btn-primary rounded-pill">Natrag na restorane</a>
        </div>
    </main>
<?php else: ?>

<header class="wolt-venue-header">
    <div class="container">
        <a href="<?= BASE_URL ?>/public/index.php?stranica=restorani" class="wolt-back-link"><i class="bi bi-arrow-left"></i> Restorani</a>

        <div class="wolt-venue-cover">
            <img src="<?= !empty($restoran['slika']) ? BASE_URL . '/public/' . htmlspecialchars($restoran['slika']) : BASE_URL . '/public/assets/img/hero/pizza.jpg' ?>"
                 alt="<?= htmlspecialchars($restoran['naziv']) ?>">
            <div class="wolt-cover-shade"></div>
            <span class="wolt-cover-badge"><i class="bi bi-bicycle"></i> Dostava</span>
        </div>

        <div class="wolt-venue-info-card">
            <div class="wolt-venue-title-block">
                <span class="wolt-eyebrow"><i class="bi bi-shop"></i> Restoran</span>
                <h1><?= htmlspecialchars($restoran['naziv']) ?></h1>
                <p class="wolt-venue-address"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($restoran['adresa']) ?></p>
                <?php if (!empty($restoran['opis'])): ?>
                    <div class="wolt-venue-description"><?= $restoran['opis'] ?></div>
                <?php endif; ?>
            </div>
            <div class="wolt-venue-facts" aria-label="Informacije o restoranu">
                <div><span><i class="bi bi-bag-check-fill"></i></span><div><strong>Online narudžba</strong><small>Naruči kroz Foodie</small></div></div>
                <div><span><i class="bi bi-geo-alt-fill"></i></span><div><strong>Mostar</strong><small>Lokalna dostava</small></div></div>
            </div>
        </div>
    </div>
</header>

<main class="wolt-menu-page">
    <div class="container">
        <form method="get" class="wolt-menu-filter" id="wolt-menu-filter">
            <input type="hidden" name="stranica" value="restoran">
            <input type="hidden" name="id" value="<?= (int) $restoran['id'] ?>">

            <div class="wolt-menu-filter-top">
                <div>
                    <span class="wolt-eyebrow">Jelovnik</span>
                    <h2>Što ti se jede?</h2>
                </div>
                <button type="submit" class="wolt-filter-submit"><i class="bi bi-sliders2"></i> Primijeni filtere</button>
            </div>

            <div class="wolt-menu-category-scroller" aria-label="Filtriraj jela po kategoriji">
                <label class="wolt-menu-category <?= $aktivnaKategorija === 0 ? 'active' : '' ?>">
                    <input class="js-auto-submit-category" type="radio" name="kategorija_id" value="" <?= $aktivnaKategorija === 0 ? 'checked' : '' ?>>
                    <span class="wolt-menu-category-art"><img src="<?= foodieKategorijaIkona('sve') ?>" alt=""></span>
                    <span>Sve</span>
                </label>
                <?php foreach (($podaci['kategorije'] ?? []) as $kat): ?>
                    <?php $katId = (int) $kat['id']; ?>
                    <label class="wolt-menu-category <?= $aktivnaKategorija === $katId ? 'active' : '' ?>">
                        <input class="js-auto-submit-category" type="radio" name="kategorija_id" value="<?= $katId ?>" <?= $aktivnaKategorija === $katId ? 'checked' : '' ?>>
                        <span class="wolt-menu-category-art"><img src="<?= foodieKategorijaIkona($kat['naziv']) ?>" alt=""></span>
                        <span><?= htmlspecialchars($kat['naziv']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="wolt-advanced-filters">
                <label class="wolt-filter-field wolt-filter-search">
                    <span>Pretraži jelo</span>
                    <div><i class="bi bi-search"></i><input type="search" name="pojam" placeholder="Npr. burger, pizza..." value="<?= htmlspecialchars($_GET['pojam'] ?? '') ?>"></div>
                </label>
                <label class="wolt-filter-field">
                    <span>Cijena od</span>
                    <div><i class="bi bi-cash"></i><input type="number" step="0.5" min="0" name="cijena_min" placeholder="0" value="<?= htmlspecialchars($_GET['cijena_min'] ?? '') ?>"></div>
                </label>
                <label class="wolt-filter-field">
                    <span>Cijena do</span>
                    <div><i class="bi bi-cash-stack"></i><input type="number" step="0.5" min="0" name="cijena_max" placeholder="50" value="<?= htmlspecialchars($_GET['cijena_max'] ?? '') ?>"></div>
                </label>
                <button type="submit" class="wolt-filter-search-button"><i class="bi bi-search"></i><span>Pretraži</span></button>
            </div>
        </form>

        <div class="wolt-menu-heading-row">
            <div>
                <h2>Jela</h2>
                <p><?= count($podaci['jela'] ?? []) ?> <?= count($podaci['jela'] ?? []) === 1 ? 'jelo' : 'jela' ?> u trenutnom prikazu</p>
            </div>
        </div>

        <?php if (empty($podaci['jela'])): ?>
            <div class="wolt-empty-state">
                <span><i class="bi bi-search"></i></span>
                <h3>Nema jela za ove filtere</h3>
                <p>Promijeni kategoriju, cijenu ili pojam pretrage pa pokušaj ponovno.</p>
                <a href="<?= BASE_URL ?>/public/index.php?stranica=restoran&id=<?= (int) $restoran['id'] ?>" class="btn btn-primary rounded-pill">Očisti filtere</a>
            </div>
        <?php else: ?>
            <div class="wolt-dish-grid">
                <?php foreach ($podaci['jela'] as $jelo): ?>
                    <?php
                    $jeloSlika = !empty($jelo['slika'])
                        ? BASE_URL . '/public/' . $jelo['slika']
                        : BASE_URL . '/public/assets/img/hero/pizza.jpg';
                    $jeloOpis = trim(strip_tags((string) ($jelo['opis'] ?? '')));
                    ?>
                    <article class="wolt-dish-card jelo-kartica js-dish-open"
                             role="button" tabindex="0"
                             aria-label="Otvori <?= htmlspecialchars($jelo['naziv'], ENT_QUOTES) ?>"
                             data-jelo-id="<?= (int) $jelo['id'] ?>"
                             data-jelo-naziv="<?= htmlspecialchars($jelo['naziv'], ENT_QUOTES) ?>"
                             data-jelo-opis="<?= htmlspecialchars($jeloOpis, ENT_QUOTES) ?>"
                             data-jelo-cijena="<?= htmlspecialchars(number_format((float) $jelo['cijena'], 2, '.', ''), ENT_QUOTES) ?>"
                             data-jelo-slika="<?= htmlspecialchars($jeloSlika, ENT_QUOTES) ?>"
                             data-can-order="<?= $mozeNaruciti ? '1' : '0' ?>"
                             data-is-authenticated="<?= $prijavljen ? '1' : '0' ?>">
                        <div class="wolt-dish-copy">
                            <div>
                                <h3><?= htmlspecialchars($jelo['naziv']) ?></h3>
                                <?php if ($jeloOpis !== ''): ?>
                                    <p><?= htmlspecialchars(skratiTekst($jeloOpis, 110)) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="wolt-dish-actions">
                                <strong class="wolt-dish-price"><?= number_format((float) $jelo['cijena'], 2) ?> KM</strong>
                                <?php if ($mozeNaruciti): ?>
                                    <div class="wolt-add-controls">
                                        <label class="visually-hidden" for="qty-<?= (int) $jelo['id'] ?>">Količina</label>
                                        <input id="qty-<?= (int) $jelo['id'] ?>" type="number" class="js-kolicina" value="1" min="1" max="99">
                                        <button class="js-dodaj-u-kosaricu" data-jelo-id="<?= (int) $jelo['id'] ?>" type="button">
                                            <i class="bi bi-plus-lg"></i><span>Dodaj</span>
                                        </button>
                                    </div>
                                <?php elseif (!$prijavljen): ?>
                                    <a class="wolt-login-to-order" href="<?= BASE_URL ?>/public/index.php?stranica=login">
                                        <i class="bi bi-person"></i><span>Prijavi se za naručivanje</span>
                                    </a>
                                <?php else: ?>
                                    <span class="wolt-role-note"><i class="bi bi-info-circle"></i> Uloga nema dozvolu za naručivanje</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="wolt-dish-media">
                            <img src="<?= htmlspecialchars($jeloSlika) ?>" alt="<?= htmlspecialchars($jelo['naziv']) ?>">
                            <span class="wolt-dish-open-badge"><i class="bi bi-arrows-angle-expand"></i></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<div class="modal fade" id="dishDetailModal" tabindex="-1" aria-labelledby="dishDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered dish-modal-dialog">
        <div class="modal-content dish-modal-content jelo-kartica">
            <button type="button" class="btn-close dish-modal-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
            <div class="dish-modal-image-wrap">
                <img id="dishDetailImage" src="" alt="">
            </div>
            <div class="dish-modal-body">
                <h2 id="dishDetailTitle"></h2>
                <p id="dishDetailDescription" class="dish-modal-description"></p>
                <div class="dish-modal-bottom">
                    <strong id="dishDetailPrice" class="dish-modal-price"></strong>
                    <?php if ($mozeNaruciti): ?>
                        <div class="dish-modal-order-controls">
                            <div class="dish-modal-stepper">
                                <button type="button" class="js-modal-qty-minus" aria-label="Smanji količinu"><i class="bi bi-dash-lg"></i></button>
                                <input id="dishDetailQty" class="js-kolicina" type="number" min="1" max="99" value="1" aria-label="Količina">
                                <button type="button" class="js-modal-qty-plus" aria-label="Povećaj količinu"><i class="bi bi-plus-lg"></i></button>
                            </div>
                            <button type="button" id="dishDetailAdd" class="js-dodaj-u-kosaricu dish-modal-add" data-jelo-id="">
                                <i class="bi bi-bag-plus"></i><span>Dodaj u košaricu</span>
                            </button>
                        </div>
                    <?php elseif (!$prijavljen): ?>
                        <a class="dish-modal-add" href="<?= BASE_URL ?>/public/index.php?stranica=login"><i class="bi bi-person"></i><span>Prijavi se za naručivanje</span></a>
                    <?php else: ?>
                        <div class="dish-modal-permission-note"><i class="bi bi-info-circle"></i> Ovaj račun trenutačno nema dozvolu za naručivanje.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
