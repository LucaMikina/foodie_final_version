<?php
$naslovStranice = 'Podrška i česta pitanja';
require __DIR__ . '/../layouts/header.php';

$faq = [
    [
        'q' => 'Kako naručiti hranu preko Foodie aplikacije?',
        'a' => 'Odaberi restoran s popisa, dodaj željena jela u košaricu, unesi adresu dostave i potvrdi narudžbu. Status narudžbe možeš pratiti u odjeljku "Moje narudžbe".',
    ],
    [
        'q' => 'Koliko traje dostava?',
        'a' => 'Prosječno vrijeme dostave je 30 minuta, ovisno o udaljenosti restorana i trenutnoj gužvi. Ako dostava kasni znatno duže, slobodno nas kontaktiraj.',
    ],
    [
        'q' => 'Mogu li otkazati narudžbu nakon što je poslana?',
        'a' => 'Narudžbu je moguće otkazati samo dok je u statusu "primljena" (prije nego je restoran prihvati). Kontaktiraj restoran izravno ili našu podršku što prije.',
    ],
    [
        'q' => 'Kako pratim status svoje narudžbe?',
        'a' => 'U izborniku klikni "Moje narudžbe" - vidjet ćeš trenutni status (primljena, prihvaćena, u pripremi, dostavljač preuzeo, dostavljeno) za svaku narudžbu.',
    ],
    [
        'q' => 'Kako mogu postati partner restoran na Foodieju?',
        'a' => 'Registriraj se i pri registraciji odaberi "Vlasnik restorana". Nakon prijave, u izborniku "Moj restoran" možeš dodati svoj restoran i jelovnik.',
    ],
    [
        'q' => 'Kako mogu postati dostavljač?',
        'a' => 'Registriraj se kao korisnik, a zatim nam se javi putem kontakt podataka ispod - administrator će ti dodijeliti ulogu dostavljača nakon kratke provjere.',
    ],
    [
        'q' => 'Koji su načini plaćanja dostupni?',
        'a' => 'Trenutno je dostupno plaćanje gotovinom pri dostavi. Plaćanje karticom online je u pripremi.',
    ],
    [
        'q' => 'Što ako je narudžba stigla neispravna ili nepotpuna?',
        'a' => 'Javi nam se odmah putem kontakt podataka ispod, uz broj narudžbe - rješavamo reklamacije u najkraćem mogućem roku.',
    ],
];
?>

<header class="hero-v2 py-5 bg-white">
    <div class="container py-3 text-center">
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-medium mb-3 d-inline-flex align-items-center gap-2">
            <i class="bi bi-headset"></i> Podrška
        </span>
        <h1 class="h2 fw-bold text-dark mb-2">Kako ti možemo pomoći?</h1>
        <p class="text-secondary mx-auto" style="max-width: 32rem;">
            Odgovore na najčešća pitanja pronađi ispod, ili nas kontaktiraj izravno.
        </p>
    </div>
</header>

<main class="py-5" style="background-color: #fcfcfc;">
    <div class="container py-4">
        <div class="row g-5">

            <div class="col-lg-8" id="cesta-pitanja">
                <h2 class="h4 fw-bold text-dark mb-4">Česta pitanja</h2>
                <div class="accordion faq-accordion" id="faqAccordion">
                    <?php foreach ($faq as $i => $stavka): ?>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                                    <?= htmlspecialchars($stavka['q']) ?>
                                </button>
                            </h3>
                            <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary">
                                    <?= htmlspecialchars($stavka['a']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-4" id="kontakt">
                <h2 class="h4 fw-bold text-dark mb-4">Kontaktiraj nas</h2>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-3">
                    <div class="d-flex align-items-start gap-3">
                        <span class="support-icon-circle"><i class="bi bi-envelope"></i></span>
                        <div>
                            <p class="fw-semibold text-dark mb-1">Email</p>
                            <a href="mailto:podrska@foodie.local" class="text-decoration-none">podrska@foodie.local</a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-3">
                    <div class="d-flex align-items-start gap-3">
                        <span class="support-icon-circle"><i class="bi bi-telephone"></i></span>
                        <div>
                            <p class="fw-semibold text-dark mb-1">Telefon</p>
                            <a href="tel:+38700000000" class="text-decoration-none">+387 00 000 000</a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="support-icon-circle"><i class="bi bi-clock"></i></span>
                        <div>
                            <p class="fw-semibold text-dark mb-1">Radno vrijeme podrške</p>
                            <p class="text-muted small mb-0">Svaki dan, 08:00 - 23:00</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
