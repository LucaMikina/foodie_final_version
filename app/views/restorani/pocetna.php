<?php
$naslovStranice = 'Foodie - dostava hrane';
$bodyClass = 'homepage-landing';
$restorani = $podaci['restorani'] ?? [];
$istaknutiRestorani = array_slice($restorani, 0, 3);

$telefonRestorani = [];
foreach (array_slice($restorani, 0, 5) as $restoran) {
    $slika = !empty($restoran['slika'])
        ? BASE_URL . '/public/' . ltrim((string) $restoran['slika'], '/')
        : BASE_URL . '/public/assets/img/hero/pizza.jpg';

    $telefonRestorani[] = [
        'id' => (int) $restoran['id'],
        'naziv' => (string) $restoran['naziv'],
        'adresa' => (string) ($restoran['adresa'] ?? ''),
        'slika' => $slika,
        'url' => BASE_URL . '/public/index.php?stranica=restoran&id=' . (int) $restoran['id'],
    ];
}

if (empty($telefonRestorani)) {
    $telefonRestorani[] = [
        'id' => 0,
        'naziv' => 'Foodie Pizza',
        'adresa' => 'Mostar',
        'slika' => BASE_URL . '/public/assets/img/hero/pizza.jpg',
        'url' => BASE_URL . '/public/index.php?stranica=restorani',
    ];
}

$telefonRestoran = $telefonRestorani[0];
$telefonJson = htmlspecialchars(
    json_encode($telefonRestorani, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
    ENT_QUOTES,
    'UTF-8'
);

require __DIR__ . '/../layouts/header.php';
?>

<main class="fh-main">
    <section class="fh-hero" aria-labelledby="fh-title">
        <span class="fh-dot fh-dot-1" aria-hidden="true"></span>
        <span class="fh-dot fh-dot-2" aria-hidden="true"></span>
        <span class="fh-dot fh-dot-3" aria-hidden="true"></span>
        <span class="fh-dot fh-dot-4" aria-hidden="true"></span>

        <div class="fh-hero-inner">
            <div class="fh-copy">
                <p class="fh-kicker"><i class="bi bi-geo-alt-fill"></i> Dostava u Mostaru</p>
                <h1 class="fh-title" id="fh-title">
                    <span class="fh-title-dark">Sve što želiš.</span>
                    <span class="fh-title-accent">Dostavljeno.</span>
                </h1>
                <p class="fh-lead">
                    Otkrij lokalne restorane, pronađi svoje omiljeno jelo i naruči u nekoliko klikova.
                    Foodie ti sve donosi na jedno jednostavno mjesto.
                </p>

                <form method="get" action="<?= BASE_URL ?>/public/index.php" class="fh-address-form" aria-label="Pretraži restorane">
                    <input type="hidden" name="stranica" value="restorani">
                    <div class="fh-address-search">
                        <span class="fh-address-icon"><i class="bi bi-search"></i></span>
                        <input type="search" name="pojam" placeholder="Pretraži restorane..." autocomplete="off">
                        <button type="submit">Pronađi hranu</button>
                    </div>
                </form>

                <div class="fh-quick-links" aria-label="Brze poveznice">
                    <a href="<?= BASE_URL ?>/public/index.php?stranica=restorani"><i class="bi bi-shop"></i> Svi restorani</a>
                    <a href="#kako-radi"><i class="bi bi-lightning-charge-fill"></i> Kako radi</a>
                </div>

            </div>

                <div class="fh-trust" aria-label="Prednosti Foodie aplikacije">
                    <div class="fh-trust-item">
                        <span class="fh-trust-icon"><i class="bi bi-bicycle"></i></span>
                        <div><strong>Brza dostava</strong><span>Bez suvišnih koraka</span></div>
                    </div>
                    <div class="fh-trust-item">
                        <span class="fh-trust-icon"><i class="bi bi-shield-check"></i></span>
                        <div><strong>Sigurno</strong><span>Jasna i laka narudžba</span></div>
                    </div>
                    <div class="fh-trust-item">
                        <span class="fh-trust-icon"><i class="bi bi-stars"></i></span>
                        <div><strong>Lokalno</strong><span>Restorani iz tvoje blizine</span></div>
                    </div>
                </div>

            <div class="fh-visual" aria-label="Prikaz Foodie mobilne aplikacije">
                <div class="fh-orbit-card fh-orbit-card-top" aria-hidden="true">
                    <span><i class="bi bi-lightning-charge-fill"></i></span>
                    <div><strong>Brzo naručivanje</strong><small>Sve na par dodira</small></div>
                </div>

                <div class="fh-phone fh-iphone17-pro" data-phone-restaurants="<?= $telefonJson ?>">
                    <div class="fh-dynamic-island" aria-hidden="true"><span></span></div>
                    <div class="fh-screen">
                        <div class="fh-status">
                            <strong>9:41</strong>
                            <span class="fh-status-icons">
                                <i class="bi bi-reception-4"></i>
                                <i class="bi bi-wifi"></i>
                                <i class="bi bi-battery-full"></i>
                            </span>
                        </div>

                        <div class="fh-location">
                            <div>
                                <small>Dostava na</small>
                                <strong><i class="bi bi-geo-alt-fill"></i> Mostar <i class="bi bi-chevron-down"></i></strong>
                            </div>
                            <span class="fh-avatar"><i class="bi bi-person-fill"></i></span>
                        </div>

                        <div class="fh-search">
                            <i class="bi bi-search"></i>
                            <span>Traži na Foodieju</span>
                            <i class="bi bi-sliders2"></i>
                        </div>

                        <div class="fh-promo js-phone-restaurant-surface">
                            <img class="js-phone-promo-image" src="<?= htmlspecialchars($telefonRestoran['slika']) ?>" alt="<?= htmlspecialchars($telefonRestoran['naziv']) ?>">
                            <div class="fh-promo-overlay"></div>
                            <div class="fh-promo-copy">
                                <span>Popularno sada</span>
                                <strong class="js-phone-promo-name"><?= htmlspecialchars($telefonRestoran['naziv']) ?></strong>
                                <small><i class="bi bi-arrow-right"></i> Pogledaj ponudu</small>
                            </div>
                        </div>

                        <div class="fh-phone-heading"><strong>Što ti se jede?</strong><span>Vidi sve</span></div>
                        <div class="fh-categories">
                            <div class="fh-category"><span class="fh-category-icon"><img src="<?= foodieKategorijaIkona('pizza') ?>" alt=""></span>Pizza</div>
                            <div class="fh-category"><span class="fh-category-icon"><img src="<?= foodieKategorijaIkona('burger') ?>" alt=""></span>Burger</div>
                            <div class="fh-category"><span class="fh-category-icon"><img src="<?= foodieKategorijaIkona('sushi') ?>" alt=""></span>Sushi</div>
                            <div class="fh-category"><span class="fh-category-icon"><img src="<?= foodieKategorijaIkona('zdravo') ?>" alt=""></span>Zdravo</div>
                        </div>

                        <div class="fh-phone-heading fh-near"><strong>Popularno u blizini</strong><span>Za tebe</span></div>
                        <a class="fh-mini-restaurant js-phone-restaurant-link" href="<?= htmlspecialchars($telefonRestoran['url']) ?>">
                            <img class="js-phone-restaurant-image" src="<?= htmlspecialchars($telefonRestoran['slika']) ?>" alt="<?= htmlspecialchars($telefonRestoran['naziv']) ?>">
                            <div class="fh-mini-copy">
                                <strong class="js-phone-restaurant-name"><?= htmlspecialchars($telefonRestoran['naziv']) ?></strong>
                                <span><i class="bi bi-clock-fill"></i> Brza dostava</span>
                                <small class="js-phone-restaurant-address"><?= htmlspecialchars($telefonRestoran['adresa']) ?></small>
                            </div>
                            <span class="fh-mini-arrow"><i class="bi bi-chevron-right"></i></span>
                        </a>

                        <?php if (count($telefonRestorani) > 1): ?>
                            <div class="fh-phone-carousel-dots" aria-label="Odaberi restoran u prikazu">
                                <?php foreach ($telefonRestorani as $index => $item): ?>
                                    <button type="button" class="js-phone-dot <?= $index === 0 ? 'active' : '' ?>" data-phone-index="<?= $index ?>" aria-label="Prikaži <?= htmlspecialchars($item['naziv']) ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="fh-phone-nav">
                            <div class="active"><i class="bi bi-house-door-fill"></i><small>Početna</small></div>
                            <div><i class="bi bi-grid-fill"></i><small>Otkrij</small></div>
                            <div><i class="bi bi-receipt"></i><small>Narudžbe</small></div>
                            <div><i class="bi bi-person"></i><small>Profil</small></div>
                        </div>
                    </div>
                </div>

                <div class="fh-orbit-card fh-orbit-card-bottom" aria-hidden="true">
                    <span><i class="bi bi-bag-check-fill"></i></span>
                    <div><strong>Spremno za narudžbu</strong><small>Odaberi restoran i kreni</small></div>
                </div>
            </div>
        </div>
    </section>

    <section class="fh-section fh-section-soft" aria-labelledby="popularni-restorani">
        <div class="container">
            <div class="fh-restaurants-head">
                <div>
                    <p class="fh-eyebrow">U tvojoj blizini</p>
                    <h2 id="popularni-restorani">Popularni <span>restorani</span></h2>
                    <p>Otvori restoran, pregledaj jelovnik i naruči ono što ti se jede.</p>
                </div>
                <a class="fh-all-link" href="<?= BASE_URL ?>/public/index.php?stranica=restorani">Prikaži sve <i class="bi bi-arrow-right"></i></a>
            </div>

            <?php if (empty($istaknutiRestorani)): ?>
                <div class="fh-empty">
                    <i class="bi bi-shop"></i>
                    <h3>Restorani uskoro stižu</h3>
                    <p>Čim se dodaju restorani, ovdje će se pojaviti preporuke.</p>
                </div>
            <?php else: ?>
                <div class="fh-restaurant-grid">
                    <?php foreach ($istaknutiRestorani as $restoran): ?>
                        <a class="fh-restaurant-link" href="<?= BASE_URL ?>/public/index.php?stranica=restoran&id=<?= (int) $restoran['id'] ?>">
                            <article class="fh-restaurant-card">
                                <div class="fh-restaurant-image">
                                    <img src="<?= !empty($restoran['slika']) ? BASE_URL . '/public/' . htmlspecialchars($restoran['slika']) : BASE_URL . '/public/assets/img/hero/pizza.jpg' ?>"
                                         alt="<?= htmlspecialchars($restoran['naziv']) ?>">
                                    <span class="fh-delivery-pill"><i class="bi bi-bicycle"></i> Dostava</span>
                                </div>
                                <div class="fh-restaurant-body">
                                    <div class="fh-card-title-row">
                                        <h3><?= htmlspecialchars($restoran['naziv']) ?></h3>
                                        <span class="fh-card-arrow"><i class="bi bi-arrow-up-right"></i></span>
                                    </div>
                                    <p><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($restoran['adresa']) ?></p>
                                </div>
                            </article>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="kako-radi" class="fh-section">
        <div class="container">
            <div class="fh-section-heading">
                <p class="fh-eyebrow">Jednostavno od početka do kraja</p>
                <h2>Tri koraka do <span>omiljenog jela</span></h2>
                <p>Foodie je napravljen da naručivanje bude brzo, jasno i bez nepotrebnih koraka.</p>
            </div>

            <div class="fh-steps">
                <article class="fh-step">
                    <span class="fh-step-number">01</span>
                    <div class="fh-step-icon"><i class="bi bi-search"></i></div>
                    <h3>Pronađi restoran</h3>
                    <p>Pretraži ponudu i filtriraj restorane prema vrsti hrane koju želiš.</p>
                </article>
                <article class="fh-step">
                    <span class="fh-step-number">02</span>
                    <div class="fh-step-icon"><i class="bi bi-bag-plus"></i></div>
                    <h3>Dodaj omiljena jela</h3>
                    <p>Otvori jelovnik, odaberi količinu i dodaj jela u svoju košaricu.</p>
                </article>
                <article class="fh-step">
                    <span class="fh-step-number">03</span>
                    <div class="fh-step-icon"><i class="bi bi-bicycle"></i></div>
                    <h3>Prati narudžbu</h3>
                    <p>Potvrdi narudžbu i prati njezin status sve dok ne stigne do tebe.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
