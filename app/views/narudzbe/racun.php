<?php
$naslovStranice = 'Račun #' . (int) $podaci['narudzba']['id'];
require __DIR__ . '/../layouts/header.php';

$narudzba = $podaci['narudzba'];
$stavke = $podaci['stavke'];
?>

<main class="py-5" style="background-color: #fcfcfc; min-height: 60vh;">
    <div class="container" style="max-width: 640px;">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <a href="<?= BASE_URL ?>/public/index.php?stranica=moje-narudzbe" class="btn btn-outline-primary rounded-pill btn-sm fw-semibold">
                <i class="bi bi-arrow-left"></i> Natrag
            </a>
            <button onclick="window.print()" class="btn btn-primary rounded-pill btn-sm fw-semibold">
                <i class="bi bi-printer"></i> Ispiši
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="d-flex justify-content-center mb-2">
                    <?php $velicinaLoga = 'sm'; require __DIR__ . '/../partials/brand-mark.php'; ?>
                </div>
                <p class="text-muted small mb-0">Račun / narudžbenica</p>
            </div>

            <div class="row small text-muted mb-4">
                <div class="col-6">
                    <strong class="text-dark">Broj narudžbe:</strong> #<?= (int) $narudzba['id'] ?><br>
                    <strong class="text-dark">Datum:</strong> <?= htmlspecialchars(date('d.m.Y. H:i', strtotime($narudzba['vrijeme_narudzbe']))) ?>
                </div>
                <div class="col-6 text-end">
                    <strong class="text-dark">Status:</strong> <?= htmlspecialchars(str_replace('_', ' ', $narudzba['status'])) ?><br>
                    <strong class="text-dark">Adresa:</strong> <?= htmlspecialchars($narudzba['adresa_dostave']) ?>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr class="border-bottom">
                        <th>Jelo</th>
                        <th class="text-center">Kol.</th>
                        <th class="text-end">Cijena</th>
                        <th class="text-end">Ukupno</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stavke as $stavka): ?>
                        <tr>
                            <td><?= htmlspecialchars($stavka['jelo_naziv']) ?></td>
                            <td class="text-center"><?= (int) $stavka['kolicina'] ?></td>
                            <td class="text-end"><?= number_format((float) $stavka['cijena_u_trenutku_narudzbe'], 2) ?> KM</td>
                            <td class="text-end"><?= number_format((float) $stavka['cijena_u_trenutku_narudzbe'] * $stavka['kolicina'], 2) ?> KM</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (array_key_exists('cijena_dostave', $narudzba)): ?>
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <span class="text-muted">Dostava<?= !empty($narudzba['dostava_km']) ? ' (' . number_format((float) $narudzba['dostava_km'], 1, ',', '.') . ' km)' : '' ?></span>
                    <strong><?= number_format((float) $narudzba['cijena_dostave'], 2, ',', '.') ?> KM</strong>
                </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-2">
                <span class="fw-semibold text-dark">Ukupno za platiti</span>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-bold fs-6"><?= number_format((float) $narudzba['ukupna_cijena'], 2) ?> KM</span>
            </div>

            <?php if (!empty($narudzba['dostava_lat']) && !empty($narudzba['dostava_lng'])): ?>
                <div class="mt-4 pt-4 border-top no-print">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <strong class="text-dark d-block"><i class="bi bi-geo-alt-fill text-primary me-1"></i> Lokacija dostave</strong>
                            <small class="text-muted">Pin koji ste označili prilikom naručivanja</small>
                        </div>
                    </div>
                    <div id="receipt-delivery-map" class="receipt-delivery-map"
                         data-lat="<?= htmlspecialchars((string) $narudzba['dostava_lat']) ?>"
                         data-lng="<?= htmlspecialchars((string) $narudzba['dostava_lng']) ?>"></div>
                </div>
            <?php endif; ?>

            <p class="text-center text-muted small mt-4 mb-0">Hvala na narudžbi!</p>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
