<?php

class Uploader
{
    private const DOZVOLJENI_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_VELICINA_BYTES = 5 * 1024 * 1024;

    public static function handle(string $polje, string $poddirektorij): ?string
    {
        if (empty($_FILES[$polje]['name']) || $_FILES[$polje]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES[$polje]['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Greška prilikom prijenosa datoteke.');
        }

        if ($_FILES[$polje]['size'] > self::MAX_VELICINA_BYTES) {
            throw new RuntimeException('Datoteka je prevelika (maksimalno 5 MB).');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $stvarniMime = $finfo->file($_FILES[$polje]['tmp_name']);

        if (!isset(self::DOZVOLJENI_MIME[$stvarniMime])) {
            throw new RuntimeException('Nedozvoljena vrsta datoteke. Dozvoljeno: JPG, PNG, WEBP.');
        }

        $ekstenzija = self::DOZVOLJENI_MIME[$stvarniMime];
        $naziv = bin2hex(random_bytes(16)) . '.' . $ekstenzija;

        $odredisniDir = APP_ROOT . '/../public/assets/img/uploads/' . trim($poddirektorij, '/');
        if (!is_dir($odredisniDir)) {
            mkdir($odredisniDir, 0755, true);
        }

        $punaPutanja = $odredisniDir . '/' . $naziv;

        if (!move_uploaded_file($_FILES[$polje]['tmp_name'], $punaPutanja)) {
            throw new RuntimeException('Spremanje datoteke nije uspjelo.');
        }

        return 'assets/img/uploads/' . trim($poddirektorij, '/') . '/' . $naziv;
    }
}
