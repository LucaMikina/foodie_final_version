<?php



require_once __DIR__ . '/../app/config/bootstrap.php';

function assertTrue($cond, $label) {
    echo ($cond ? "OK   " : "FAIL ") . $label . "\n";
    if (!$cond) exit(1);
}


$token1 = Csrf::token();
$token2 = Csrf::token();
assertTrue($token1 === $token2, 'Csrf::token() vraća isti token unutar iste sesije');
assertTrue(strlen($token1) === 64, 'Csrf token je 32 bajta (64 hex znaka) - dovoljno siguran');

$field = Csrf::field();
assertTrue(strpos($field, 'name="csrf_token"') !== false, 'Csrf::field() generira ispravno HTML polje');
assertTrue(strpos($field, $token1) !== false, 'Csrf::field() sadrži trenutni token');




$laznaSlika = sys_get_temp_dir() . '/zlonamjerna.jpg';
file_put_contents($laznaSlika, '<?php echo "napadnut"; ?>');

$_FILES['test_slika'] = [
    'name' => 'zlonamjerna.jpg',
    'type' => 'image/jpeg', 
    'tmp_name' => $laznaSlika,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($laznaSlika),
];

try {
    Uploader::handle('test_slika', 'test');
    assertTrue(false, 'Uploader odbija datoteku koja tvrdi da je JPG ali sadrži PHP kod');
} catch (RuntimeException $e) {
    assertTrue(str_contains($e->getMessage(), 'Nedozvoljena vrsta'), 'Uploader ispravno odbija lažnu sliku (finfo MIME provjera)');
}


$pravaSlikaPodaci = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
$pravaSlika = sys_get_temp_dir() . '/prava.png';
file_put_contents($pravaSlika, $pravaSlikaPodaci);

$_FILES['test_slika'] = [
    'name' => 'prava.png',
    'type' => 'image/png',
    'tmp_name' => $pravaSlika,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($pravaSlika),
];




$finfo = new finfo(FILEINFO_MIME_TYPE);
assertTrue($finfo->file($pravaSlika) === 'image/png', 'Prava PNG datoteka prepoznata ispravnim MIME tipom (preduvjet za Uploader::handle)');

unlink($laznaSlika);
unlink($pravaSlika);


$weather = new WeatherService();
$rezultat = $weather->getByCity('Mostar');
assertTrue($rezultat === null, 'WeatherService vraća null (ne ruši aplikaciju) kad API ključ nije postavljen - fail-safe ponašanje');

echo "\nSVI DODATNI TESTOVI PROŠLI USPJEŠNO.\n";
