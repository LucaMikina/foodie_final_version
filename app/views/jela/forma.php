<?php
$jelo = $podaci['jelo'] ?? null;
$naslovStranice = $jelo ? 'Uredi jelo' : 'Dodaj jelo';
require __DIR__ . '/../layouts/header.php';
?>

<main class="py-5" style="background-color: #fcfcfc;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-medium mb-3 d-inline-flex align-items-center gap-2" style="width: fit-content;">
                        <i class="bi bi-egg-fried"></i> <?= $jelo ? 'Uređivanje' : 'Novo jelo' ?>
                    </span>
                    <h1 class="h3 fw-bold text-dark mb-4"><?= $jelo ? 'Uredi jelo' : 'Dodaj jelo' ?></h1>

                    <form method="post" action="<?= BASE_URL ?>/public/index.php?stranica=jelo-forma" enctype="multipart/form-data">
                        <?= Csrf::field() ?>
                        <?php if ($jelo): ?>
                            <input type="hidden" name="id" value="<?= (int) $jelo['id'] ?>">
                        <?php else: ?>
                            <input type="hidden" name="restoran_id" value="<?= (int) $podaci['restoran_id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="naziv">Naziv jela</label>
                            <input type="text" class="form-control" id="naziv" name="naziv" required
                                   value="<?= htmlspecialchars($jelo['naziv'] ?? '') ?>">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="kategorija_id">Kategorija</label>
                                <select class="form-select" id="kategorija_id" name="kategorija_id" required>
                                    <?php foreach ($podaci['kategorije'] as $kat): ?>
                                        <option value="<?= (int) $kat['id'] ?>" <?= (($jelo['kategorija_id'] ?? null) == $kat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kat['naziv']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="cijena">Cijena (KM)</label>
                                <input type="number" step="0.10" min="0" class="form-control" id="cijena" name="cijena" required
                                       value="<?= htmlspecialchars($jelo['cijena'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="opis">Opis</label>
                            <textarea class="form-control js-wysiwyg" id="opis" name="opis" rows="5"><?= $jelo['opis'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="slika">Fotografija</label>
                            <input type="file" class="form-control" id="slika" name="slika" accept="image/png, image/jpeg, image/webp">
                            <?php if (!empty($jelo['slika'])): ?>
                                <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($jelo['slika']) ?>" class="mt-2 rounded-3" style="max-height:120px;">
                            <?php endif; ?>
                        </div>

                        <?php if ($jelo): ?>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="dostupno" name="dostupno" value="1" <?= !empty($jelo['dostupno']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="dostupno">Dostupno za narudžbu</label>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                            <i class="bi bi-check-lg"></i> Spremi
                        </button>
                        <?php $povratakRestoranId = (int) ($jelo['restoran_id'] ?? $podaci['restoran_id'] ?? 0); ?>
                        <a href="<?= BASE_URL ?>/public/index.php?stranica=restoran-profil&id=<?= $povratakRestoranId ?>#jelovnik" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">Odustani</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
