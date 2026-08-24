<?php
$naslovStranice = 'Dostave';
require __DIR__ . '/../layouts/header.php';
$trenutniDostavljacId = (int) Auth::id();
?>

<style>
.delivery-board-card{border:1px solid rgba(0,157,224,.12);border-radius:1.35rem;background:#fff;box-shadow:0 .55rem 1.8rem rgba(15,23,42,.06);height:100%;transition:.2s ease}.delivery-board-card:hover{transform:translateY(-2px);box-shadow:0 .9rem 2rem rgba(15,23,42,.09)}
.delivery-state{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .75rem;border-radius:999px;font-weight:800;font-size:.78rem}.delivery-state-free{background:#eafaf1;color:#137a48}.delivery-state-busy{background:#fff3e6;color:#9a5300}.delivery-state-mine{background:#e8f7fd;color:#0078ad}.delivery-state-wait{background:#f1f5f9;color:#64748b}.delivery-price{font-weight:900;color:#009de0}.delivery-meta{display:flex;align-items:flex-start;gap:.55rem;color:#64748b;font-size:.9rem}.delivery-meta i{color:#009de0;margin-top:.05rem}.driver-kpi{border-radius:1.25rem;background:linear-gradient(135deg,#009de0,#00b5eb);color:#fff;padding:1.2rem 1.3rem}.driver-kpi strong{font-size:1.7rem;display:block}.driver-action{min-height:46px}.delivery-progress{display:flex;gap:.35rem;align-items:center}.delivery-progress span{height:6px;flex:1;border-radius:999px;background:#e2e8f0}.delivery-progress span.active{background:#009de0}
</style>

<main class="py-5" style="background:#f7fbfd;min-height:70vh;">
    <div class="container py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <span class="badge rounded-pill px-3 py-2 mb-2" style="background:#e8f7fd;color:#0078ad"><i class="bi bi-bicycle me-1"></i> Dostavljački centar</span>
                <h1 class="display-6 fw-black mb-1" style="font-weight:900">Trenutne narudžbe</h1>
            </div>
            <button type="button" class="btn btn-outline-primary rounded-pill px-4" onclick="window.location.reload()"><i class="bi bi-arrow-clockwise me-1"></i> Osvježi</button>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-sm-6 col-lg-3"><div class="driver-kpi"><span class="small opacity-75">Aktualne</span><strong><?= count($podaci['trenutne']) ?></strong><span class="small">narudžbe u sustavu</span></div></div>
            <div class="col-sm-6 col-lg-3"><div class="driver-kpi"><span class="small opacity-75">Moje</span><strong><?= count($podaci['moje']) ?></strong><span class="small">aktivne dostave</span></div></div>
        </div>

        <?php if (empty($podaci['trenutne'])): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-check2-circle display-4 text-primary"></i>
                <h2 class="h5 mt-3">Sve je mirno</h2>
                <p class="text-muted mb-0">Trenutno nema aktivnih narudžbi.</p>
            </div>
        <?php else: ?>
            <div class="row g-4 mb-5" id="delivery-board">
                <?php foreach ($podaci['trenutne'] as $n): ?>
                    <?php
                    $dostavljacId = (int) ($n['dostavljac_id'] ?? 0);
                    $moja = $dostavljacId > 0 && $dostavljacId === $trenutniDostavljacId;
                    $zauzeta = $dostavljacId > 0;
                    $spremnaZaPrihvat = !$zauzeta && $n['status'] === 'priprema';
                    ?>
                    <div class="col-md-6 col-xl-4 dostupna-narudzba" data-narudzba-id="<?= (int) $n['id'] ?>">
                        <article class="delivery-board-card p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
                                <div>
                                    <div class="small text-muted fw-semibold mb-1">NARUDŽBA #<?= (int) $n['id'] ?></div>
                                    <h2 class="h5 fw-bold mb-0"><?= htmlspecialchars($n['restoran_naziv']) ?></h2>
                                </div>
                                <span class="delivery-price"><?= number_format((float) $n['ukupna_cijena'], 2) ?> KM</span>
                            </div>

                            <div class="d-grid gap-2 mb-3">
                                <div class="delivery-meta"><i class="bi bi-shop"></i><span><?= htmlspecialchars($n['restoran_adresa']) ?></span></div>
                                <div class="delivery-meta"><i class="bi bi-geo-alt-fill"></i><span><?= htmlspecialchars($n['adresa_dostave']) ?></span></div>
                                <div class="delivery-meta"><i class="bi bi-person"></i><span><?= htmlspecialchars($n['kupac_ime'] . ' ' . $n['kupac_prezime']) ?></span></div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="status-pill status-<?= htmlspecialchars($n['status']) ?>"><?= htmlspecialchars(str_replace('_',' ', $n['status'])) ?></span>
                                <?php if ($moja): ?>
                                    <span class="delivery-state delivery-state-mine"><i class="bi bi-person-check-fill"></i> Tvoja dostava</span>
                                <?php elseif ($zauzeta): ?>
                                    <span class="delivery-state delivery-state-busy"><i class="bi bi-lock-fill"></i> Zauzeto</span>
                                <?php elseif ($spremnaZaPrihvat): ?>
                                    <span class="delivery-state delivery-state-free"><i class="bi bi-unlock-fill"></i> Slobodno</span>
                                <?php else: ?>
                                    <span class="delivery-state delivery-state-wait"><i class="bi bi-hourglass-split"></i> Čeka pripremu</span>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto">
                                <?php if ($spremnaZaPrihvat): ?>
                                    <button class="btn btn-primary rounded-pill w-100 fw-bold driver-action js-preuzmi-dostavu" data-narudzba-id="<?= (int) $n['id'] ?>">
                                        <i class="bi bi-hand-index-thumb me-1"></i> Prihvati dostavu
                                    </button>
                                <?php elseif ($zauzeta && !$moja): ?>
                                    <button class="btn btn-light rounded-pill w-100 fw-bold driver-action" disabled><i class="bi bi-lock-fill me-1"></i> Zauzeo drugi dostavljač</button>
                                <?php elseif ($moja): ?>
                                    <a href="#moje-dostave" class="btn btn-outline-primary rounded-pill w-100 fw-bold driver-action d-flex align-items-center justify-content-center"><i class="bi bi-arrow-down-circle me-1"></i> Upravljaj dostavom</a>
                                <?php else: ?>
                                    <button class="btn btn-light rounded-pill w-100 fw-bold driver-action" disabled><i class="bi bi-clock me-1"></i> Još nije spremno</button>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section id="moje-dostave" class="pt-2">
            <div class="mb-3">
                <span class="small text-primary fw-bold text-uppercase">Moja ruta</span>
                <h2 class="h3 fw-bold mb-1">Moje aktivne dostave</h2>
            </div>

            <?php if (empty($podaci['moje'])): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-bicycle display-5 text-secondary"></i>
                    <p class="text-muted mt-3 mb-0">Još nemaš aktivnu dostavu.</p>
                </div>
            <?php else: ?>
                <div class="d-grid gap-3">
                    <?php foreach ($podaci['moje'] as $n): ?>
                        <div class="card border-0 shadow-sm rounded-4 p-4 narudzba-red">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-4">
                                    <div class="small text-muted fw-semibold">#<?= (int) $n['id'] ?></div>
                                    <div class="fw-bold fs-5"><?= htmlspecialchars($n['restoran_naziv']) ?></div>
                                    <div class="small text-muted mt-1"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($n['adresa_dostave']) ?></div>
                                </div>
                                <div class="col-lg-3">
                                    <span class="delivery-state delivery-state-busy mb-2"><i class="bi bi-lock-fill"></i> Zauzeto — tvoje</span>
                                    <div><span class="status-pill js-status-pill status-<?= htmlspecialchars($n['status']) ?>"><?= htmlspecialchars(str_replace('_',' ', $n['status'])) ?></span></div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="delivery-progress" title="Tijek dostave">
                                        <span class="active"></span><span class="<?= in_array($n['status'], ['na_dostavi','dostavljena'], true) ? 'active' : '' ?>"></span><span class="<?= $n['status'] === 'dostavljena' ? 'active' : '' ?>"></span>
                                    </div>
                                </div>
                                <div class="col-lg-3 text-lg-end">
                                    <?php if ($n['status'] === 'priprema'): ?>
                                        <button type="button" class="btn btn-primary rounded-pill fw-bold px-4 js-delivery-status-button" data-narudzba-id="<?= (int) $n['id'] ?>" data-status="na_dostavi"><i class="bi bi-bicycle me-1"></i> Kreni na dostavu</button>
                                    <?php elseif ($n['status'] === 'na_dostavi'): ?>
                                        <button type="button" class="btn btn-success rounded-pill fw-bold px-4 js-delivery-status-button" data-narudzba-id="<?= (int) $n['id'] ?>" data-status="dostavljena"><i class="bi bi-check2-circle me-1"></i> Dostavljeno</button>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Čeka da restoran završi pripremu</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
