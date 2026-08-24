<?php
$naslovStranice = 'Sve narudžbe';
require __DIR__ . '/../layouts/header.php';
$dostavljaci = $podaci['dostavljaci'] ?? [];
?>

<style>
.dispatch-state{display:inline-flex;align-items:center;gap:.4rem;border-radius:999px;padding:.42rem .7rem;font-weight:800;font-size:.76rem;white-space:nowrap}.dispatch-free{background:#eafaf1;color:#147a49}.dispatch-busy{background:#fff3e6;color:#a15b00}.dispatch-closed{background:#f1f5f9;color:#64748b}.admin-dispatch-select{min-width:170px}.dispatch-assignment{min-width:275px}.admin-order-table td,.admin-order-table th{vertical-align:middle}
</style>

<main class="py-5" style="background:#f7fbfd;min-height:70vh;">
    <div class="container py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <span class="badge rounded-pill px-3 py-2 mb-2" style="background:#e8f7fd;color:#0078ad"><i class="bi bi-shield-check me-1"></i> Dispatch admin</span>
                <h1 class="display-6 fw-bold mb-1">Narudžbe i dodjela dostavljača</h1>
            </div>
            <div class="text-end">
                <div class="fw-bold fs-4"><?= count($podaci['narudzbe']) ?></div>
                <div class="small text-muted">ukupno narudžbi</div>
            </div>
        </div>

        <?php if (empty($podaci['narudzbe'])): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm"><i class="bi bi-inbox display-5 text-secondary"></i><p class="text-muted mt-3 mb-0">Još nema narudžbi.</p></div>
        <?php else: ?>
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 admin-order-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">#</th>
                                <th>Restoran / kupac</th>
                                <th>Status</th>
                                <th>Stanje dostave</th>
                                <th class="dispatch-assignment">Dostavljač / dodjela</th>
                                <th>Iznos</th>
                                <th class="pe-4">Datum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($podaci['narudzbe'] as $n): ?>
                                <?php
                                $zatvorena = in_array($n['status'], ['dostavljena','otkazana'], true);
                                $zauzeta = !empty($n['dostavljac_id']) && !$zatvorena;
                                $slobodna = empty($n['dostavljac_id']) && !$zatvorena;
                                $mozeDodjela = $slobodna && in_array($n['status'], ['primljena','prihvacena','priprema'], true);
                                ?>
                                <tr class="js-admin-order-row" data-narudzba-id="<?= (int) $n['id'] ?>">
                                    <td class="ps-4 fw-bold">#<?= (int) $n['id'] ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($n['restoran_naziv']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($n['kupac_ime'].' '.$n['kupac_prezime']) ?></div>
                                    </td>
                                    <td><span class="status-pill status-<?= htmlspecialchars($n['status']) ?>"><?= htmlspecialchars(str_replace('_',' ', $n['status'])) ?></span></td>
                                    <td class="js-dispatch-state-cell">
                                        <?php if ($zatvorena): ?>
                                            <span class="dispatch-state dispatch-closed"><i class="bi bi-check2-circle"></i> Zatvoreno</span>
                                        <?php elseif ($zauzeta): ?>
                                            <span class="dispatch-state dispatch-busy"><i class="bi bi-lock-fill"></i> Zauzeto</span>
                                        <?php else: ?>
                                            <span class="dispatch-state dispatch-free"><i class="bi bi-unlock-fill"></i> Slobodno</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="js-dispatch-assignment-cell">
                                        <?php if (!empty($n['dostavljac_id'])): ?>
                                            <div class="fw-semibold"><i class="bi bi-bicycle text-primary me-1"></i><?= htmlspecialchars(trim(($n['dostavljac_ime'] ?? '').' '.($n['dostavljac_prezime'] ?? ''))) ?></div>
                                            <?php if (!$zatvorena): ?><div class="small text-muted">Narudžba je zaključana za ostale.</div><?php endif; ?>
                                        <?php elseif ($mozeDodjela && !empty($dostavljaci)): ?>
                                            <div class="d-flex gap-2 align-items-center js-admin-dispatch-controls">
                                                <select class="form-select form-select-sm rounded-pill admin-dispatch-select js-admin-driver-select" aria-label="Odaberi dostavljača">
                                                    <option value="">Odaberi dostavljača</option>
                                                    <?php foreach ($dostavljaci as $d): ?>
                                                        <option value="<?= (int) $d['id'] ?>"><?= htmlspecialchars($d['ime'].' '.$d['prezime']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold js-admin-assign-delivery" data-narudzba-id="<?= (int) $n['id'] ?>">Dodijeli</button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">Nije dostupno za dodjelu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-primary"><?= number_format((float) $n['ukupna_cijena'], 2) ?> KM</td>
                                    <td class="pe-4 text-muted small"><?= htmlspecialchars(date('d.m.Y. H:i', strtotime($n['vrijeme_narudzbe']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
