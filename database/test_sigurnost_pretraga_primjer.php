<?php


require_once __DIR__ . '/../app/config/bootstrap.php';

function assertTrue($cond, $label) {
    echo ($cond ? "OK   " : "FAIL ") . $label . "\n";
    if (!$cond) exit(1);
}



$slucajevi = [
    '<script>alert("xss")</script><p>Opis restorana</p>'
        => fn($out) => !str_contains($out, '<script') && str_contains($out, 'Opis restorana'),

    '<p onclick="alert(1)">Klikni me</p>'
        => fn($out) => !str_contains($out, 'onclick') && str_contains($out, 'Klikni me'),

    '<img src=x onerror="alert(1)">'
        => fn($out) => !str_contains($out, 'onerror') && !str_contains($out, '<img'),

    '<a href="javascript:alert(1)">Link</a>'
        => fn($out) => !str_contains($out, 'javascript:'),

    '<a href="https://example.com">Sigurni link</a>'
        => fn($out) => str_contains($out, 'href="https://example.com"') && str_contains($out, 'rel='),

    '<p>Dobar <strong>opis</strong> s <em>formatiranjem</em>.</p>'
        => fn($out) => str_contains($out, '<strong>opis</strong>') && str_contains($out, '<em>formatiranjem</em>'),

    '<iframe src="https://evil.com"></iframe><p>Tekst</p>'
        => fn($out) => !str_contains($out, '<iframe') && str_contains($out, 'Tekst'),

    '<div style="background:url(javascript:alert(1))">Div sadržaj</div>'
        => fn($out) => !str_contains($out, 'style=') && str_contains($out, 'Div sadržaj'),
];

$i = 1;
foreach ($slucajevi as $input => $provjera) {
    $ocisceno = HtmlSanitizer::clean($input);
    assertTrue($provjera($ocisceno), "XSS test #$i: '" . substr($input, 0, 40) . "...' ispravno očišćen");
    $i++;
}

echo "Primjer čišćenja: " . HtmlSanitizer::clean('<script>alert(1)</script><p>Margherita pizza s <strong>svježim</strong> sastojcima.</p>') . "\n\n";


$userModel = new User();
$email = 'pretraga_' . uniqid() . '@test.hr';
$id = $userModel->register(['ime' => 'Iva', 'prezime' => 'Ivić', 'email' => $email, 'lozinka' => 'lozinka123'], 'kupac');

$rezultatiPoImenu = $userModel->search('Ivić');
assertTrue(count(array_filter($rezultatiPoImenu, fn($r) => (int)$r['id'] === $id)) === 1, 'User::search() pronalazi korisnika po prezimenu');

$rezultatiPoEmailu = $userModel->search(substr($email, 0, 10));
assertTrue(count(array_filter($rezultatiPoEmailu, fn($r) => (int)$r['id'] === $id)) === 1, 'User::search() pronalazi korisnika po dijelu emaila');

$rezultatiPoUlozi = $userModel->search('', 'kupac');
assertTrue(count(array_filter($rezultatiPoUlozi, fn($r) => (int)$r['id'] === $id)) === 1, 'User::search() filtrira po ulozi');

$rezultatiKrivaUloga = $userModel->search('', 'administrator');
assertTrue(count(array_filter($rezultatiKrivaUloga, fn($r) => (int)$r['id'] === $id)) === 0, 'User::search() ispravno isključuje korisnika s drugom ulogom');

echo "\nSVI TESTOVI (XSS + pretraga) PROŠLI USPJEŠNO.\n";
