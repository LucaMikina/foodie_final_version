<footer class="footer-v2 mt-5 py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <p class="mb-3"><?php $velicinaLoga = 'md'; require __DIR__ . '/../partials/brand-mark.php'; ?></p>
                <p class="small text-muted" style="max-width: 22rem;">Vaša omiljena hrana na klik od vas. Brza dostava, širok izbor restorana i najukusnija jela direktno na vašu adresu.</p>
            </div>
            <div class="col-6 col-lg-2 offset-lg-1">
                <h6>Linkovi</h6>
                <a href="<?= BASE_URL ?>/public/index.php">Početna</a>
                <a href="<?= BASE_URL ?>/public/index.php?stranica=restorani">Restorani</a>
                <a href="<?= BASE_URL ?>/public/index.php?stranica=onama">O nama</a>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Podrška</h6>
                <a href="<?= BASE_URL ?>/public/index.php?stranica=podrska#kontakt">Kontakt</a>
                <a href="<?= BASE_URL ?>/public/index.php?stranica=podrska#cesta-pitanja">Česta pitanja</a>
            </div>
            <div class="col-lg-3">
                <h6>Pratite nas</h6>
                <div class="d-flex">
                    <a href="#" class="social-circle" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-circle" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center flex-wrap gap-2 mt-5 pt-4 divider-dashed small text-muted">
            <span>&copy; <?= date('Y') ?> Foodie</span>
        </div>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<?php if (isset($pageSlug) && in_array(strtolower($pageSlug), ['kosarica', 'racun'], true)): ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<?php endif; ?>

<?php $appJsVersion = @filemtime(APP_ROOT . '/../public/assets/js/app.js') ?: time(); ?>
<script src="<?= BASE_URL ?>/public/assets/js/app.js?v=<?= $appJsVersion ?>"></script>

</body>
</html>
