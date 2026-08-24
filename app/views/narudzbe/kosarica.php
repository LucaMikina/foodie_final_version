<?php
$naslovStranice = 'Košarica i dostava';
require __DIR__ . '/../layouts/header.php';

$stavke = $podaci['stavke'] ?? [];
$ukupno = array_reduce($stavke, fn($zbroj, $s) => $zbroj + ((float) $s['cijena'] * (int) $s['kolicina']), 0.0);
$restoranNaziv = !empty($stavke) ? ($stavke[0]['restoran_naziv'] ?? 'Restoran') : '';
?>

<main class="checkout-page py-4 py-lg-5">
    <div class="container checkout-shell py-2 py-lg-3">
        <div class="checkout-topbar mb-4 mb-lg-5">
            <div>
                <a class="checkout-back-link" href="<?= BASE_URL ?>/public/index.php?stranica=restorani">
                    <i class="bi bi-arrow-left"></i>
                    <span>Nastavi pregledavati</span>
                </a>
                <div class="checkout-eyebrow mt-3"><i class="bi bi-bag-check"></i> Završetak narudžbe</div>
                <h1 class="checkout-title mb-2">Košarica i dostava</h1>
                <p class="checkout-subtitle mb-0">Provjerite jela, označite lokaciju i pošaljite narudžbu.</p>
            </div>
            <?php if (!empty($stavke)): ?>
                <div class="checkout-restaurant-chip d-none d-md-flex">
                    <span class="checkout-restaurant-icon"><i class="bi bi-shop"></i></span>
                    <span>
                        <small>Naručujete iz</small>
                        <strong><?= htmlspecialchars($restoranNaziv) ?></strong>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($stavke)): ?>
            <section class="checkout-empty text-center">
                <div class="checkout-empty-icon"><i class="bi bi-bag"></i></div>
                <h2>Košarica je prazna</h2>
                <p>Odaberite restoran i dodajte nešto ukusno prije nastavka.</p>
                <a href="<?= BASE_URL ?>/public/index.php?stranica=restorani" class="btn btn-primary rounded-pill px-4 py-3 fw-bold">
                    Pronađi hranu
                </a>
            </section>
        <?php else: ?>
            <div class="row g-4 g-xl-5 align-items-start">
                <div class="col-lg-7 col-xl-8">
                    <section class="checkout-card checkout-items-card">
                        <div class="checkout-card-heading">
                            <div>
                                <span class="checkout-step">1</span>
                                <div>
                                    <h2>Vaša narudžba</h2>
                                    <p><?= htmlspecialchars($restoranNaziv) ?></p>
                                </div>
                            </div>
                            <span class="checkout-item-count js-checkout-item-count">
                                <?= array_sum(array_map(fn($s) => (int) $s['kolicina'], $stavke)) ?> stavki
                            </span>
                        </div>

                        <div class="checkout-items-list">
                            <?php foreach ($stavke as $stavka): ?>
                                <?php
                                $cijena = (float) $stavka['cijena'];
                                $kolicina = (int) $stavka['kolicina'];
                                $slika = !empty($stavka['slika'])
                                    ? BASE_URL . '/public/' . htmlspecialchars($stavka['slika'])
                                    : null;
                                ?>
                                <article class="checkout-item js-cart-row"
                                         data-cart-id="<?= (int) $stavka['id'] ?>"
                                         data-unit-price="<?= htmlspecialchars(number_format($cijena, 2, '.', '')) ?>">
                                    <div class="checkout-item-image-wrap">
                                        <?php if ($slika): ?>
                                            <img src="<?= $slika ?>" class="checkout-item-image" alt="<?= htmlspecialchars($stavka['naziv']) ?>">
                                        <?php else: ?>
                                            <div class="checkout-item-image checkout-item-placeholder"><i class="bi bi-egg-fried"></i></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="checkout-item-info">
                                        <h3><?= htmlspecialchars($stavka['naziv']) ?></h3>
                                        <p><?= htmlspecialchars($stavka['restoran_naziv']) ?></p>
                                        <strong><?= number_format($cijena, 2) ?> KM</strong>
                                    </div>

                                    <div class="checkout-item-actions">
                                        <div class="checkout-stepper" aria-label="Količina za <?= htmlspecialchars($stavka['naziv']) ?>">
                                            <button type="button" class="checkout-stepper-btn js-cart-qty" data-direction="minus" aria-label="Smanji količinu">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <span class="checkout-stepper-value js-cart-qty-value"><?= $kolicina ?></span>
                                            <button type="button" class="checkout-stepper-btn js-cart-qty" data-direction="plus" aria-label="Povećaj količinu">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        <div class="checkout-line-price js-cart-line-total"><?= number_format($cijena * $kolicina, 2) ?> KM</div>
                                        <button type="button" class="checkout-remove-btn js-cart-remove" aria-label="Ukloni <?= htmlspecialchars($stavka['naziv']) ?>">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="checkout-card mt-4">
                        <div class="checkout-card-heading mb-3">
                            <div>
                                <span class="checkout-step">2</span>
                                <div>
                                    <h2>Lokacija dostave</h2>
                                    <p>Upišite adresu i postavite pin što preciznije.</p>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-location-grid">
                            <div class="checkout-address-column">
                                <label class="checkout-label" for="adresa_dostave">Adresa dostave</label>
                                <div class="checkout-address-search">
                                    <div class="checkout-input-wrap">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <input type="text" class="form-control checkout-input" id="adresa_dostave" name="adresa_dostave_preview"
                                               value="<?= htmlspecialchars($_SESSION['adresa'] ?? '') ?>"
                                               placeholder="npr. Kralja Tomislava 12, Mostar"
                                               autocomplete="street-address">
                                    </div>
                                    <button type="button" class="checkout-address-search-btn" id="search-delivery-address">
                                        <i class="bi bi-search"></i>
                                        <span>Pronađi</span>
                                    </button>
                                </div>
                                <div class="checkout-geocode-results d-none" id="delivery-search-results" aria-live="polite"></div>
                                <small class="checkout-search-hint" id="delivery-search-hint">Upišite ulicu, broj i grad pa kliknite <strong>Pronađi</strong>.</small>

                                <button type="button" class="checkout-location-btn" id="use-current-location">
                                    <span><i class="bi bi-crosshair"></i></span>
                                    <span>
                                        <strong>Koristi moju lokaciju</strong>
                                        <small id="location-button-status">Preglednik će zatražiti dopuštenje</small>
                                    </span>
                                    <i class="bi bi-chevron-right ms-auto"></i>
                                </button>

                            </div>

                            <div class="checkout-map-column">
                                <div id="delivery-map" class="checkout-map" aria-label="Odabir lokacije dostave"></div>
                                <div class="checkout-map-caption">
                                    <span id="delivery-map-status"><i class="bi bi-geo-alt"></i> Kliknite na kartu za pin dostave</span>
                                    <span class="checkout-map-coordinates" id="delivery-map-coordinates"></span>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-lg-5 col-xl-4">
                    <aside class="checkout-card checkout-summary-card sticky-lg-top">
                        <form method="post" action="<?= BASE_URL ?>/public/index.php?stranica=narudzba-kreiraj" id="checkout-order-form">
                            <?= Csrf::field() ?>
                            <input type="hidden" id="delivery_lat" name="dostava_lat" value="">
                            <input type="hidden" id="delivery_lng" name="dostava_lng" value="">
                            <input type="hidden" id="checkout_address_value" name="adresa_dostave" value="<?= htmlspecialchars($_SESSION['adresa'] ?? '') ?>">
                            <input type="hidden" id="delivery_fee_preview" value="0">
                            <input type="hidden" id="delivery_km_preview" value="0">

                            <div class="checkout-card-heading compact mb-4">
                                <div>
                                    <span class="checkout-step">3</span>
                                    <div>
                                        <h2>Dostava</h2>
                                        <p>Vrijeme i pregled cijene</p>
                                    </div>
                                </div>
                            </div>

                            <div class="checkout-delivery-option active">
                                <span class="checkout-option-icon"><i class="bi bi-lightning-charge-fill"></i></span>
                                <span>
                                    <strong>Što prije</strong>
                                    <small>Počet ćemo pripremu odmah</small>
                                </span>
                                <input class="form-check-input ms-auto js-delivery-time-mode" type="radio" name="vrijeme_tip" value="asap" checked aria-label="Što prije">
                            </div>

                            <div class="checkout-delivery-option mt-2">
                                <span class="checkout-option-icon"><i class="bi bi-calendar3"></i></span>
                                <span>
                                    <strong>Zakaži dostavu</strong>
                                    <small>Odaberi željeni datum</small>
                                </span>
                                <input class="form-check-input ms-auto js-delivery-time-mode" type="radio" name="vrijeme_tip" value="scheduled" aria-label="Zakaži dostavu">
                            </div>

                            <div class="checkout-scheduled-wrap mt-3 d-none" id="scheduled-delivery-wrap">
                                <label class="checkout-label" for="zeljeno_vrijeme_dostave">Željeni datum</label>
                                <div class="checkout-input-wrap">
                                    <i class="bi bi-calendar-event"></i>
                                    <input type="text" class="form-control checkout-input" id="zeljeno_vrijeme_dostave" name="zeljeno_vrijeme_dostave"
                                           placeholder="Odaberi datum..." readonly>
                                </div>
                            </div>

                            <div id="weather-widget" class="weather-widget checkout-weather mt-3" data-grad="Mostar"></div>

                            <div class="checkout-divider"></div>

                            <div class="checkout-price-row">
                                <span>Međuzbroj</span>
                                <strong class="js-checkout-subtotal"><?= number_format($ukupno, 2) ?> KM</strong>
                            </div>
                            <div class="checkout-price-row">
                                <span>Dostava <small class="text-muted js-delivery-distance"></small></span>
                                <strong class="js-delivery-fee">Odaberite lokaciju</strong>
                            </div>
                            <div class="checkout-price-row checkout-total-row">
                                <span>Ukupno</span>
                                <strong class="js-checkout-total" data-subtotal="<?= number_format($ukupno, 2, '.', '') ?>"><?= number_format($ukupno, 2) ?> KM</strong>
                            </div>

                            <button type="submit" class="checkout-submit-btn mt-4" id="checkout-submit">
                                <span>Potvrdi narudžbu</span>
                                <span class="js-checkout-total"><?= number_format($ukupno, 2) ?> KM</span>
                            </button>

                            <p class="checkout-terms mb-0 mt-3">
                                <i class="bi bi-shield-check"></i>
                                Potvrdom narudžbe šaljete zahtjev odabranom restoranu.
                            </p>
                        </form>
                    </aside>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
