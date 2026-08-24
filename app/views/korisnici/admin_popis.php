<?php
$naslovStranice = 'Upravljanje korisnicima';
require __DIR__ . '/../layouts/header.php';

$userModelZaPrikaz = new \User();
$restaurantModelZaPrikaz = new \Restaurant();
$ulogeZapisi = $userModelZaPrikaz->getAllRoles();
$uloge = array_column($ulogeZapisi, 'naziv_uloge');

$roleMeta = [
    'administrator' => ['label' => 'Administrator', 'icon' => 'bi-shield-check'],
    'restoran'      => ['label' => 'Restoran', 'icon' => 'bi-shop'],
    'dostavljac'    => ['label' => 'Dostavljač', 'icon' => 'bi-bicycle'],
    'kupac'         => ['label' => 'Kupac', 'icon' => 'bi-bag-heart'],
];
?>

<main class="py-5 admin-users-page">
    <div class="container py-4">
        <div class="admin-users-hero mb-4">
            <div>
                <span class="admin-kicker"><i class="bi bi-shield-check"></i> Admin centar</span>
                <h1 class="display-6 fw-bold text-dark mb-2">Korisnici i uloge</h1>
            </div>
        </div>

        <?php if (($_GET['poruka'] ?? '') === 'uloge_spremljene'): ?>
            <div class="alert alert-success border-0 rounded-4 shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <span>Uloge su uspješno spremljene.</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['admin_greska'])): ?>
            <div class="alert alert-danger border-0 rounded-4 shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($_SESSION['admin_greska']) ?></span>
            </div>
            <?php unset($_SESSION['admin_greska']); ?>
        <?php endif; ?>

        <form method="get" class="admin-filter-bar mb-4">
            <input type="hidden" name="stranica" value="admin-korisnici">
            <div class="admin-filter-search">
                <label class="form-label small fw-semibold" for="admin-pojam">Pretraga</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="admin-pojam" name="pojam" class="form-control"
                           placeholder="Ime, prezime ili email..." value="<?= htmlspecialchars($_GET['pojam'] ?? '') ?>">
                </div>
            </div>
            <div class="admin-filter-role">
                <label class="form-label small fw-semibold" for="admin-uloga">Uloga</label>
                <select id="admin-uloga" name="uloga" class="form-select">
                    <option value="">Sve uloge</option>
                    <?php foreach ($uloge as $u): ?>
                        <option value="<?= htmlspecialchars($u) ?>" <?= ($_GET['uloga'] ?? '') === $u ? 'selected' : '' ?>>
                            <?= htmlspecialchars($roleMeta[$u]['label'] ?? ucfirst($u)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4">
                <i class="bi bi-sliders2"></i> Filtriraj
            </button>
        </form>

        <div class="admin-users-list">
            <?php foreach ($podaci['korisnici'] as $korisnik): ?>
                <?php
                $trenutneUlogeZapisi = $userModelZaPrikaz->getRoles((int) $korisnik['id']);
                $trenutneUloge = array_column($trenutneUlogeZapisi, 'naziv_uloge');
                $uredujeSebe = (int) $korisnik['id'] === (int) Auth::id();
                $dodijeljeniRestoran = in_array('restoran', $trenutneUloge, true)
                    ? $restaurantModelZaPrikaz->findAssignedToOwner((int) $korisnik['id'])
                    : null;
                ?>
                <article class="admin-user-card">
                    <div class="admin-user-identity">
                        <div class="admin-user-avatar">
                            <?= htmlspecialchars(mb_strtoupper(mb_substr($korisnik['ime'], 0, 1) . mb_substr($korisnik['prezime'], 0, 1))) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <h2 class="h6 fw-bold text-dark mb-0"><?= htmlspecialchars($korisnik['ime'] . ' ' . $korisnik['prezime']) ?></h2>
                                <?php if ($uredujeSebe): ?><span class="admin-you-badge">Ti</span><?php endif; ?>
                                <span class="status-pill <?= $korisnik['status'] === 'aktivan' ? 'status-dostavljena' : 'status-otkazana' ?>">
                                    <?= htmlspecialchars($korisnik['status']) ?>
                                </span>
                            </div>
                            <p class="text-muted small mb-0 text-truncate"><?= htmlspecialchars($korisnik['email']) ?></p>
                        </div>
                    </div>

                    <form method="post" action="<?= BASE_URL ?>/public/index.php?stranica=admin-korisnik-uloga" class="admin-role-form">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int) $korisnik['id'] ?>">

                        <div class="admin-role-toggle-wrap" aria-label="Uloge korisnika">
                            <?php foreach ($uloge as $u): ?>
                                <?php
                                $checked = in_array($u, $trenutneUloge, true);
                                $zakljucanAdmin = $uredujeSebe && $u === 'administrator';
                                $inputId = 'role-' . (int) $korisnik['id'] . '-' . htmlspecialchars($u);
                                $meta = $roleMeta[$u] ?? ['label' => ucfirst($u), 'icon' => 'bi-person-badge'];
                                ?>
                                <?php if ($zakljucanAdmin): ?>
                                    <input type="hidden" name="uloge[]" value="administrator">
                                <?php endif; ?>
                                <input class="btn-check" type="checkbox" name="uloge[]" value="<?= htmlspecialchars($u) ?>"
                                       id="<?= $inputId ?>" <?= $checked ? 'checked' : '' ?> <?= $zakljucanAdmin ? 'disabled' : '' ?>>
                                <label class="admin-role-chip <?= $zakljucanAdmin ? 'role-locked' : '' ?>" for="<?= $inputId ?>"
                                       title="<?= $zakljucanAdmin ? 'Ne možeš sam sebi ukloniti administratorsku ulogu.' : '' ?>">
                                    <i class="bi <?= htmlspecialchars($meta['icon']) ?>"></i>
                                    <span><?= htmlspecialchars($meta['label']) ?></span>
                                    <?php if ($zakljucanAdmin): ?><i class="bi bi-lock-fill role-lock-icon"></i><?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill admin-save-roles">
                            <i class="bi bi-check2"></i> Spremi uloge
                        </button>
                    </form>

                    <?php if (in_array('restoran', $trenutneUloge, true)): ?>
                        <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2 px-1">
                            <div class="small text-muted">
                                <i class="bi bi-shop me-1"></i>
                                <?php if ($dodijeljeniRestoran): ?>
                                    Dodijeljeni restoran: <strong class="text-dark"><?= htmlspecialchars($dodijeljeniRestoran['naziv']) ?></strong>
                                <?php else: ?>
                                    <strong class="text-warning-emphasis">Nema dodijeljen restoran</strong>
                                <?php endif; ?>
                            </div>
                            <?php if ($dodijeljeniRestoran): ?>
                                <a class="btn btn-light rounded-pill btn-sm fw-semibold" href="<?= BASE_URL ?>/public/index.php?stranica=restoran-forma&id=<?= (int) $dodijeljeniRestoran['id'] ?>">
                                    <i class="bi bi-arrow-left-right"></i> Promijeni dodjelu
                                </a>
                            <?php else: ?>
                                <a class="btn btn-outline-primary rounded-pill btn-sm fw-semibold" href="<?= BASE_URL ?>/public/index.php?stranica=restoran-forma&vlasnik_id=<?= (int) $korisnik['id'] ?>">
                                    <i class="bi bi-plus-lg"></i> Dodijeli restoran
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="admin-user-actions">
                        <?php if ($korisnik['status'] === 'aktivan'): ?>
                            <form method="post" action="<?= BASE_URL ?>/public/index.php?stranica=admin-korisnik-deaktiviraj"
                                  onsubmit="return confirm('Deaktivirati ovog korisnika?');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int) $korisnik['id'] ?>">
                                <button type="submit" class="btn btn-light rounded-pill btn-sm fw-semibold" <?= $uredujeSebe ? 'disabled title="Ne možeš deaktivirati samoga sebe."' : '' ?>>
                                    <i class="bi bi-person-x"></i> Deaktiviraj
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (empty($podaci['korisnici'])): ?>
                <div class="admin-empty-state">
                    <i class="bi bi-people"></i>
                    <h2 class="h5 fw-bold">Nema rezultata</h2>
                    <p class="text-muted mb-0">Pokušaj promijeniti pojam ili filter uloge.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
