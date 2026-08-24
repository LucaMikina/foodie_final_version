<?php
$restoran = $podaci['restoran'] ?? null;
$naslovStranice = $restoran ? 'Uredi restoran' : 'Dodaj restoran';
require __DIR__ . '/../layouts/header.php';
?>

<main class="py-5" style="background-color: #fcfcfc;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-medium mb-3 d-inline-flex align-items-center gap-2" style="width: fit-content;">
                        <i class="bi bi-shop"></i> <?= $restoran ? 'Uređivanje' : 'Novi restoran' ?>
                    </span>
                    <h1 class="h3 fw-bold text-dark mb-4"><?= $restoran ? 'Uredi restoran' : 'Dodaj restoran' ?></h1>

                    <form method="post" action="<?= BASE_URL ?>/public/index.php?stranica=restoran-forma" enctype="multipart/form-data">
                        <?= Csrf::field() ?>
                        <?php if ($restoran): ?>
                            <input type="hidden" name="id" value="<?= (int) $restoran['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="vlasnik_id">
                                <i class="bi bi-shield-check text-primary"></i> Račun restorana
                            </label>
                            <?php if (!empty($podaci['vlasnici'])): ?>
                                <select class="form-select" id="vlasnik_id" name="vlasnik_id" required>
                                    <?php foreach ($podaci['vlasnici'] as $v): ?>
                                        <option value="<?= (int) $v['id'] ?>" <?= ((!empty($restoran['vlasnik_id']) && (int) $restoran['vlasnik_id'] === (int) $v['id']) || (empty($restoran) && (int) ($podaci['predlozeni_vlasnik_id'] ?? 0) === (int) $v['id'])) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['ime'] . ' ' . $v['prezime'] . ' (' . $v['email'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <select class="form-select" id="vlasnik_id" disabled>
                                    <option>Nema dostupnih računa restorana</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="naziv">Naziv restorana</label>
                            <input type="text" class="form-control" id="naziv" name="naziv" required
                                   value="<?= htmlspecialchars($restoran['naziv'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="adresa">Adresa</label>
                            <input type="text" class="form-control" id="adresa" name="adresa" required
                                   placeholder="npr. Kralja Tomislava 12, Mostar"
                                   value="<?= htmlspecialchars($restoran['adresa'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="opis">Opis</label>
                            <textarea class="form-control js-wysiwyg" id="opis" name="opis" rows="6"><?= $restoran['opis'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="slika">Fotografija</label>
                            <input type="file" class="form-control" id="slika" name="slika" accept="image/png, image/jpeg, image/webp">
                            <?php if (!empty($restoran['slika'])): ?>
                                <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($restoran['slika']) ?>" class="mt-2 rounded-3" style="max-height:120px;">
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" <?= empty($podaci['vlasnici']) ? 'disabled' : '' ?>>
                            <i class="bi bi-check-lg"></i> Spremi
                        </button>
                        <a href="<?= $restoran ? BASE_URL . '/public/index.php?stranica=restoran-profil&id=' . (int) $restoran['id'] : BASE_URL . '/public/index.php?stranica=moj-restoran' ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">Odustani</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
