<?php
$naslovStranice = 'Moje narudžbe';
require __DIR__ . '/../layouts/header.php';

$mozeMijenjatiStatus = Auth::hasRole('dostavljac') || Auth::hasRole('administrator');
$statusiZaOdabir = ['primljena', 'prihvacena', 'priprema', 'na_dostavi', 'dostavljena', 'otkazana'];
?>

<main class="py-5" style="background-color: #fcfcfc; min-height: 60vh;">
    <div class="container py-4">
        <div class="mb-4">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-medium mb-2 d-inline-flex align-items-center gap-2">
                <i class="bi bi-receipt"></i> Praćenje narudžbi
            </span>
            <h1 class="h2 fw-bold text-dark mb-0">Moje narudžbe</h1>
        </div>

        <?php if (empty($podaci['narudzbe'])): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-light-subtle">
                <i class="bi bi-receipt display-4 text-secondary"></i>
                <p class="mt-3 text-muted">Još nemate narudžbi.</p>
                <a href="<?= BASE_URL ?>/public/index.php?stranica=restorani" class="btn btn-primary rounded-pill px-4 fw-semibold">Pregledaj restorane</a>
            </div>
        <?php else: ?>
            <?php foreach ($podaci['narudzbe'] as $narudzba): ?>
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-3 narudzba-red">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3">
                            <span class="text-muted small">Narudžba</span>
                            <p class="fw-bold text-dark mb-0">#<?= (int) $narudzba['id'] ?></p>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">Datum</span>
                            <p class="mb-0 text-dark"><?= htmlspecialchars(date('d.m.Y. H:i', strtotime($narudzba['vrijeme_narudzbe']))) ?></p>
                        </div>
                        <div class="col-md-2">
                            <span class="text-muted small">Iznos</span>
                            <p class="mb-0"><span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-bold"><?= number_format((float) $narudzba['ukupna_cijena'], 2) ?> KM</span></p>
                        </div>
                        <div class="col-md-2">
                            <?php if ($mozeMijenjatiStatus): ?>
                                <select class="form-select form-select-sm rounded-pill js-status-narudzbe" data-narudzba-id="<?= (int) $narudzba['id'] ?>">
                                    <?php foreach ($statusiZaOdabir as $s): ?>
                                        <option value="<?= $s ?>" <?= $s === $narudzba['status'] ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <span class="status-pill js-status-pill status-<?= htmlspecialchars($narudzba['status']) ?>">
                                    <?= htmlspecialchars(str_replace('_', ' ', $narudzba['status'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2 text-md-end">
                            <a href="<?= BASE_URL ?>/public/index.php?stranica=racun&id=<?= (int) $narudzba['id'] ?>" class="btn btn-outline-primary rounded-pill btn-sm fw-semibold">
                                <i class="bi bi-printer"></i> Račun
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
