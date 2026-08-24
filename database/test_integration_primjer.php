<?php



require_once __DIR__ . '/../app/config/bootstrap.php';

function assertTrue($cond, $label) {
    echo ($cond ? "OK   " : "FAIL ") . $label . "\n";
    if (!$cond) exit(1);
}


$pdo = Database::getConnection();
assertTrue($pdo instanceof PDO, 'PDO konekcija uspostavljena (OOP)');


$userModel = new User();
$vlasnikEmail = 'vlasnik_' . uniqid() . '@test.hr';
$vlasnikId = $userModel->register([
    'ime' => 'Ana', 'prezime' => 'Anić', 'email' => $vlasnikEmail, 'lozinka' => 'lozinka123'
], 'restoran');
assertTrue($vlasnikId > 0, 'Registracija vlasnika restorana (User::register)');

$kupacEmail = 'kupac_' . uniqid() . '@test.hr';
$kupacId = $userModel->register([
    'ime' => 'Marko', 'prezime' => 'Marić', 'email' => $kupacEmail, 'lozinka' => 'lozinka123'
], 'kupac');
assertTrue($kupacId > 0, 'Registracija kupca (User::register)');


$roles = $userModel->getRoles($vlasnikId);
assertTrue(count($roles) === 1 && $roles[0]['naziv_uloge'] === 'restoran', 'Dodijeljena ispravna uloga (korisnik_uloga)');
assertTrue($userModel->hasPermission($vlasnikId, 'upravljanje_vlastitim_restoranom'), 'hasPermission() ispravno vraća true preko uloga_dozvola JOIN-a');
assertTrue(!$userModel->hasPermission($kupacId, 'upravljanje_korisnicima'), 'hasPermission() ispravno vraća false za neovlaštenu dozvolu');


$found = $userModel->findByEmail($vlasnikEmail);
assertTrue($userModel->verifyPassword('lozinka123', $found['lozinka']), 'Prijava - password_verify ispravno provjerava hashiranu lozinku');
assertTrue(!$userModel->verifyPassword('kriva-lozinka', $found['lozinka']), 'Prijava - pogrešna lozinka je ispravno odbijena');


$restaurantModel = new Restaurant();
$restoranId = $restaurantModel->create([
    'vlasnik_id' => $vlasnikId, 'naziv' => 'Pizzeria Test', 'adresa' => 'Test adresa 1', 'opis' => 'Testni opis'
]);
assertTrue($restoranId > 0, 'CREATE restorana');

$restoran = $restaurantModel->find($restoranId);
assertTrue($restoran['naziv'] === 'Pizzeria Test', 'READ (find) restorana');

$restaurantModel->update($restoranId, ['naziv' => 'Pizzeria Test - Izmijenjeno']);
$restoran = $restaurantModel->find($restoranId);
assertTrue($restoran['naziv'] === 'Pizzeria Test - Izmijenjeno', 'UPDATE restorana');


$dishModel = new Dish();
$kategorije = $pdo->query("SELECT id FROM kategorije WHERE naziv = 'Pizze'")->fetch();
$jeloId = $dishModel->create([
    'restoran_id' => $restoranId, 'kategorija_id' => $kategorije['id'],
    'naziv' => 'Margherita', 'cijena' => 6.50, 'dostupno' => 1
]);
assertTrue($jeloId > 0, 'CREATE jela');

$filtrirano = $dishModel->filter(['kategorija_id' => $kategorije['id'], 'cijena_max' => 10, 'pojam' => 'Marg']);
assertTrue(count($filtrirano) === 1, 'Višestruko filtriranje (kategorija + cijena + tekst) radi ispravno');


$cartModel = new Cart();
$cartModel->addItem($kupacId, $jeloId, 2);
$kosarica = $cartModel->getForUser($kupacId);
assertTrue(count($kosarica) === 1 && (int)$kosarica[0]['kolicina'] === 2, 'Dodavanje u košaricu i dohvat (JOIN s jela/restorani)');

$cartModel->addItem($kupacId, $jeloId, 1); 
$kosarica = $cartModel->getForUser($kupacId);
assertTrue(count($kosarica) === 1 && (int)$kosarica[0]['kolicina'] === 3, 'Ponovljeno dodavanje povećava količinu umjesto duplog reda');


$orderModel = new Order();
$orderId = $orderModel->createWithItems(
    ['kupac_id' => $kupacId, 'restoran_id' => $restoranId, 'status' => 'primljena', 'adresa_dostave' => 'Dostavna 5'],
    [['jelo_id' => $jeloId, 'kolicina' => 3, 'cijena' => 6.50]]
);
assertTrue($orderId > 0, 'Kreiranje narudžbe s transakcijom (createWithItems)');

$narudzba = $orderModel->find($orderId);
assertTrue(abs((float)$narudzba['ukupna_cijena'] - 19.50) < 0.001, 'Ukupna cijena narudžbe ispravno izračunata (3 x 6.50 = 19.50)');

$stavke = $orderModel->getItems($orderId);
assertTrue(count($stavke) === 1 && $stavke[0]['jelo_naziv'] === 'Margherita', 'Stavke narudžbe ispravno povezane s jelima (JOIN)');


$orderModel->updateStatus($orderId, 'priprema');
$narudzba = $orderModel->find($orderId);
assertTrue($narudzba['status'] === 'priprema', 'Ažuriranje statusa narudžbe');


$restaurantModel->deactivate($restoranId);
$restoran = $restaurantModel->find($restoranId);
assertTrue($restoran['status'] === 'neaktivan', 'Meko brisanje (deactivate) postavlja status umjesto brisanja retka');

echo "\nSVI TESTOVI PROŠLI USPJEŠNO.\n";
