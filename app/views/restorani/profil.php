<?php
$restoran = $podaci['restoran'];
$narudzbe = $podaci['narudzbe'] ?? [];
$stavkePoNarudzbi = $podaci['stavke_po_narudzbi'] ?? [];
$statistika = $podaci['statistika'] ?? [];
$jela = $podaci['jela'] ?? [];
$naslovStranice = 'Profil - ' . $restoran['naziv'];

$statusMeta = [
    'primljena'   => ['Nova narudžba', 'new', 'bi-bell-fill'],
    'prihvacena'  => ['Prihvaćena', 'accepted', 'bi-check-circle-fill'],
    'priprema'    => ['U pripremi', 'preparing', 'bi-fire'],
    'na_dostavi'  => ['Na dostavi', 'delivery', 'bi-bicycle'],
    'dostavljena' => ['Dostavljena', 'delivered', 'bi-house-check-fill'],
    'otkazana'    => ['Otkazana', 'cancelled', 'bi-x-circle-fill'],
];

require __DIR__ . '/../layouts/header.php';
?>

<main class="restaurant-dashboard pb-5">
    <section class="restaurant-dashboard-hero">
        <div class="container py-4 py-lg-5">
            <a href="<?= BASE_URL ?>/public/index.php?stranica=moj-restoran" class="restaurant-back-link mb-3 d-inline-flex">
                <i class="bi bi-arrow-left"></i> <?= Auth::hasRole('administrator') ? 'Svi profili' : 'Moj restoran' ?>
            </a>

            <div class="restaurant-dashboard-cover">
                <div class="restaurant-dashboard-cover-media">
                    <?php if (!empty($restoran['slika'])): ?>
                        <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($restoran['slika']) ?>" alt="<?= htmlspecialchars($restoran['naziv']) ?>">
                    <?php else: ?>
                        <div class="restaurant-dashboard-cover-fallback"><i class="bi bi-shop-window"></i></div>
                    <?php endif; ?>
                    <div class="restaurant-dashboard-cover-shade"></div>
                    <div class="restaurant-dashboard-identity">
                        <span class="restaurant-eyebrow light"><i class="bi bi-stars"></i> Profil restorana</span>
                        <h1><?= htmlspecialchars($restoran['naziv']) ?></h1>
                        <p><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($restoran['adresa']) ?></p>
                    </div>
                </div>
                <div class="restaurant-dashboard-actions">
                    <a href="<?= BASE_URL ?>/public/index.php?stranica=restoran&id=<?= (int) $restoran['id'] ?>" class="btn restaurant-soft-btn">
                        <i class="bi bi-box-arrow-up-right"></i> Javni profil
                    </a>
                    <?php if (Auth::hasRole('administrator')): ?>
                        <a href="<?= BASE_URL ?>/public/index.php?stranica=restoran-forma&id=<?= (int) $restoran['id'] ?>" class="btn restaurant-primary-btn">
                            <i class="bi bi-pencil-square"></i> Uredi restoran
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <?php if (!empty($_SESSION['jelo_poruka'])): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['jelo_poruka']) ?>
                <?php unset($_SESSION['jelo_poruka']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zatvori"></button>
            </div>
        <?php endif; ?>

        <section class="restaurant-kpi-grid" aria-label="Statistika restorana">
            <article class="restaurant-kpi-card is-new">
                <span class="restaurant-kpi-icon"><i class="bi bi-bell"></i></span>
                <div><strong><?= (int) ($statistika['nove'] ?? 0) ?></strong><span>Nove narudžbe</span></div>
            </article>
            <article class="restaurant-kpi-card">
                <span class="restaurant-kpi-icon"><i class="bi bi-arrow-repeat"></i></span>
                <div><strong><?= (int) ($statistika['aktivne'] ?? 0) ?></strong><span>U tijeku</span></div>
            </article>
            <article class="restaurant-kpi-card">
                <span class="restaurant-kpi-icon"><i class="bi bi-check2-circle"></i></span>
                <div><strong><?= (int) ($statistika['dostavljene'] ?? 0) ?></strong><span>Dostavljeno</span></div>
            </article>
            <article class="restaurant-kpi-card">
                <span class="restaurant-kpi-icon"><i class="bi bi-graph-up-arrow"></i></span>
                <div><strong><?= number_format((float) ($statistika['prihod'] ?? 0), 2) ?> KM</strong><span>Promet dostavljenih</span></div>
            </article>
        </section>

        <nav class="restaurant-dashboard-nav" aria-label="Sekcije profila">
            <a href="#narudzbe" class="active"><i class="bi bi-receipt"></i> Narudžbe <?php if (($statistika['nove'] ?? 0) > 0): ?><span><?= (int) $statistika['nove'] ?></span><?php endif; ?></a>
            <a href="#jelovnik"><i class="bi bi-grid"></i> Jelovnik <span><?= count($jela) ?></span></a>
        </nav>

        <section id="narudzbe" class="restaurant-dashboard-section scroll-margin-top">
            <div class="restaurant-section-heading">
                <div>
                    <span class="restaurant-section-kicker">Live orders</span>
                    <h2>Narudžbe restorana</h2>
                    <p>Prihvati novu narudžbu, pošalji je u pripremu i prati je sve do dostave.</p>
                </div>
                <div class="restaurant-order-filters" role="group" aria-label="Filtriraj narudžbe">
                    <button type="button" class="active" data-restaurant-order-filter="all">Sve</button>
                    <button type="button" data-restaurant-order-filter="primljena">Nove</button>
                    <button type="button" data-restaurant-order-filter="active">U tijeku</button>
                    <button type="button" data-restaurant-order-filter="done">Završene</button>
                </div>
            </div>

            <?php if (empty($narudzbe)): ?>
                <div class="restaurant-empty-state compact">
                    <div class="restaurant-empty-icon"><i class="bi bi-receipt"></i></div>
                    <h3>Još nema narudžbi</h3>
                    <p>Kada kupac naruči iz ovog restorana, narudžba će se odmah pojaviti ovdje.</p>
                </div>
            <?php else: ?>
                <div class="restaurant-orders-list" id="restaurant-orders-list">
                    <?php foreach ($narudzbe as $narudzba): ?>
                        <?php
                        $status = (string) $narudzba['status'];
                        $meta = $statusMeta[$status] ?? [ucfirst($status), 'default', 'bi-circle-fill'];
                        $stavke = $stavkePoNarudzbi[(int) $narudzba['id']] ?? [];
                        $filterGroup = in_array($status, ['prihvacena', 'priprema', 'na_dostavi'], true)
                            ? 'active'
                            : (in_array($status, ['dostavljena', 'otkazana'], true) ? 'done' : $status);
                        ?>
                        <article class="restaurant-order-card narudzba-red <?= $status === 'primljena' ? 'is-new-order' : '' ?>"
                                 data-order-status="<?= htmlspecialchars($status) ?>"
                                 data-order-group="<?= htmlspecialchars($filterGroup) ?>">
                            <div class="restaurant-order-topbar">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="restaurant-order-number">#<?= (int) $narudzba['id'] ?></div>
                                    <span class="restaurant-status-pill restaurant-status-<?= htmlspecialchars($meta[1]) ?> js-status-pill">
                                        <i class="bi <?= htmlspecialchars($meta[2]) ?>"></i> <?= htmlspecialchars($meta[0]) ?>
                                    </span>
                                    <?php if ($status === 'primljena'): ?>
                                        <span class="restaurant-live-indicator"><span></span> Čeka odgovor</span>
                                    <?php endif; ?>
                                </div>
                                <div class="restaurant-order-time">
                                    <?= htmlspecialchars(date('d.m.Y. H:i', strtotime($narudzba['vrijeme_narudzbe']))) ?>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <div class="restaurant-order-items">
                                        <?php foreach ($stavke as $stavka): ?>
                                            <div class="restaurant-order-item">
                                                <?php if (!empty($stavka['jelo_slika'])): ?>
                                                    <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($stavka['jelo_slika']) ?>" alt="">
                                                <?php else: ?>
                                                    <span class="restaurant-order-item-fallback"><i class="bi bi-egg-fried"></i></span>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= (int) $stavka['kolicina'] ?>× <?= htmlspecialchars($stavka['jelo_naziv']) ?></strong>
                                                    <span><?= number_format((float) $stavka['cijena_u_trenutku_narudzbe'], 2) ?> KM / kom</span>
                                                </div>
                                                <b><?= number_format((float) $stavka['cijena_u_trenutku_narudzbe'] * (int) $stavka['kolicina'], 2) ?> KM</b>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="restaurant-order-customer-box">
                                        <div class="restaurant-order-customer-row">
                                            <i class="bi bi-person"></i>
                                            <div><span>Kupac</span><strong><?= htmlspecialchars(trim($narudzba['kupac_ime'] . ' ' . $narudzba['kupac_prezime'])) ?></strong></div>
                                        </div>
                                        <div class="restaurant-order-customer-row">
                                            <i class="bi bi-geo-alt"></i>
                                            <div><span>Dostava</span><strong><?= htmlspecialchars($narudzba['adresa_dostave']) ?></strong></div>
                                        </div>
                                        <?php if (!empty($narudzba['kupac_telefon'])): ?>
                                            <div class="restaurant-order-customer-row">
                                                <i class="bi bi-telephone"></i>
                                                <div><span>Telefon</span><strong><?= htmlspecialchars($narudzba['kupac_telefon']) ?></strong></div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="restaurant-order-customer-row">
                                            <i class="bi bi-bicycle"></i>
                                            <div><span>Dostavljač</span><strong><?= !empty($narudzba['dostavljac_ime']) ? htmlspecialchars(trim($narudzba['dostavljac_ime'] . ' ' . $narudzba['dostavljac_prezime'])) : 'Još nije dodijeljen' ?></strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="restaurant-order-footer">
                                <div class="restaurant-order-total">
                                    <span>Ukupno</span><strong><?= number_format((float) $narudzba['ukupna_cijena'], 2) ?> KM</strong>
                                </div>
                                <div class="restaurant-order-actions">
                                    <?php if ($status === 'primljena'): ?>
                                        <button type="button" class="btn restaurant-reject-btn js-restaurant-status-button" data-narudzba-id="<?= (int) $narudzba['id'] ?>" data-status="otkazana" data-confirm="Odbiti ovu narudžbu?">
                                            <i class="bi bi-x-lg"></i> Odbij
                                        </button>
                                        <button type="button" class="btn restaurant-accept-btn js-restaurant-status-button" data-narudzba-id="<?= (int) $narudzba['id'] ?>" data-status="prihvacena">
                                            <i class="bi bi-check-lg"></i> Prihvati narudžbu
                                        </button>
                                    <?php elseif ($status === 'prihvacena'): ?>
                                        <button type="button" class="btn restaurant-reject-btn js-restaurant-status-button" data-narudzba-id="<?= (int) $narudzba['id'] ?>" data-status="otkazana" data-confirm="Otkazati već prihvaćenu narudžbu?">
                                            Otkaži
                                        </button>
                                        <button type="button" class="btn restaurant-accept-btn js-restaurant-status-button" data-narudzba-id="<?= (int) $narudzba['id'] ?>" data-status="priprema">
                                            <i class="bi bi-fire"></i> Započni pripremu
                                        </button>
                                    <?php elseif ($status === 'priprema'): ?>
                                        <span class="restaurant-order-readonly"><i class="bi bi-hourglass-split"></i> Hrana se priprema — čeka preuzimanje dostavljača</span>
                                    <?php elseif ($status === 'na_dostavi'): ?>
                                        <span class="restaurant-order-readonly is-blue"><i class="bi bi-bicycle"></i> Dostavljač je preuzeo narudžbu</span>
                                    <?php elseif ($status === 'dostavljena'): ?>
                                        <span class="restaurant-order-readonly is-green"><i class="bi bi-check2-circle"></i> Narudžba je uspješno dostavljena</span>
                                    <?php else: ?>
                                        <span class="restaurant-order-readonly is-red"><i class="bi bi-x-circle"></i> Narudžba je otkazana</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="restaurant-filter-empty d-none" id="restaurant-filter-empty">
                    <i class="bi bi-inbox"></i>
                    <p>Nema narudžbi u ovoj kategoriji.</p>
                </div>
            <?php endif; ?>
        </section>

        <section id="jelovnik" class="restaurant-dashboard-section scroll-margin-top">
            <div class="restaurant-section-heading align-items-end">
                <div>
                    <span class="restaurant-section-kicker">Menu management</span>
                    <h2>Jelovnik</h2>
                    <p>Dodaj nova jela, mijenjaj cijene, fotografije i dostupnost postojeće ponude.</p>
                </div>
                <a href="<?= BASE_URL ?>/public/index.php?stranica=jelo-forma&restoran_id=<?= (int) $restoran['id'] ?>" class="btn restaurant-primary-btn">
                    <i class="bi bi-plus-lg"></i> Dodaj jelo
                </a>
            </div>

            <?php if (empty($jela)): ?>
                <div class="restaurant-empty-state compact">
                    <div class="restaurant-empty-icon"><i class="bi bi-egg-fried"></i></div>
                    <h3>Jelovnik je prazan</h3>
                    <p>Dodaj prvo jelo kako bi ga kupci mogli naručiti.</p>
                    <a href="<?= BASE_URL ?>/public/index.php?stranica=jelo-forma&restoran_id=<?= (int) $restoran['id'] ?>" class="btn restaurant-primary-btn">Dodaj prvo jelo</a>
                </div>
            <?php else: ?>
                <div class="restaurant-menu-grid">
                    <?php foreach ($jela as $jelo): ?>
                        <article class="restaurant-menu-card">
                            <div class="restaurant-menu-media">
                                <?php if (!empty($jelo['slika'])): ?>
                                    <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($jelo['slika']) ?>" alt="<?= htmlspecialchars($jelo['naziv']) ?>">
                                <?php else: ?>
                                    <div class="restaurant-menu-media-fallback"><i class="bi bi-egg-fried"></i></div>
                                <?php endif; ?>
                                <span class="restaurant-menu-availability <?= !empty($jelo['dostupno']) ? 'available' : 'unavailable' ?>">
                                    <?= !empty($jelo['dostupno']) ? 'Dostupno' : 'Nedostupno' ?>
                                </span>
                            </div>
                            <div class="restaurant-menu-body">
                                <div class="d-flex justify-content-between gap-3 align-items-start">
                                    <div>
                                        <h3><?= htmlspecialchars($jelo['naziv']) ?></h3>
                                        <p><?= trim(strip_tags((string) ($jelo['opis'] ?? ''))) !== '' ? htmlspecialchars(mb_strimwidth(trim(strip_tags($jelo['opis'])), 0, 95, '…')) : 'Bez opisa.' ?></p>
                                    </div>
                                    <strong class="restaurant-menu-price"><?= number_format((float) $jelo['cijena'], 2) ?> KM</strong>
                                </div>
                                <div class="restaurant-menu-actions">
                                    <a href="<?= BASE_URL ?>/public/index.php?stranica=jelo-forma&id=<?= (int) $jelo['id'] ?>" class="btn restaurant-soft-btn flex-grow-1">
                                        <i class="bi bi-pencil"></i> Uredi
                                    </a>
                                    <form method="post" action="<?= BASE_URL ?>/public/index.php?stranica=jelo-obrisi" onsubmit="return confirm('Ukloniti jelo <?= htmlspecialchars(addslashes($jelo['naziv'])) ?> iz jelovnika? Povijest postojećih narudžbi ostat će sačuvana.');">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $jelo['id'] ?>">
                                        <button type="submit" class="btn restaurant-icon-danger" aria-label="Obriši <?= htmlspecialchars($jelo['naziv']) ?>"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
