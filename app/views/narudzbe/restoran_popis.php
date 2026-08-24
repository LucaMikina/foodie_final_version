<?php
$naslovStranice = 'Narudžbe mog restorana';
require __DIR__ . '/../layouts/header.php';

$statusiZaOdabir = ['primljena', 'prihvacena', 'priprema', 'na_dostavi', 'dostavljena', 'otkazana'];
?>

<main class="py-5" style="background-color: #fcfcfc;">
    <div class="container py-4">
        <div class="mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-medium mb-2 d-inline-flex align-items-center gap-2">
                <i class="bi bi-shop"></i> Vlasnik restorana
            </span>
            <h1 class="h2 fw-bold text-dark mb-0">Narudžbe mog restorana</h1>
        </div>

        <?php if (empty($podaci['narudzbe'])): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-light-subtle">
                <i class="bi bi-receipt display-5 text-secondary"></i>
                <p class="text-muted mt-3 mb-0">Još nema narudžbi za vaš restoran.</p>
            </div>
        <?php else: ?>
            <?php foreach ($podaci['narudzbe'] as $narudzba): ?>
                <div class="card shadow-sm border-0 rounded-4 p-3 p-md-4 mb-3">
                    <div class="row align-items-center g-3">
                        <div class="col-md-2">
                            <span class="text-muted small">Narudžba</span>
                            <p class="fw-bold text-dark mb-0">#<?= (int) $narudzba['id'] ?></p>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small">Adresa dostave</span>
                            <p class="mb-0 small text-dark"><i class="bi bi-geo-alt text-primary"></i> <?= htmlspecialchars($narudzba['adresa_dostave']) ?></p>
                        </div>
                        <div class="col-md-2">
                            <span class="text-muted small">Datum</span>
                            <p class="mb-0 small text-dark"><?= htmlspecialchars(date('d.m.Y. H:i', strtotime($narudzba['vrijeme_narudzbe']))) ?></p>
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-bold"><?= number_format((float) $narudzba['ukupna_cijena'], 2) ?> KM</span>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm rounded-pill js-status-narudzbe" data-narudzba-id="<?= (int) $narudzba['id'] ?>">
                                <?php foreach ($statusiZaOdabir as $s): ?>
                                    <option value="<?= $s ?>" <?= $s === $narudzba['status'] ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
