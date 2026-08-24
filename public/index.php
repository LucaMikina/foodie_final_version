<?php

require_once __DIR__ . '/../app/config/bootstrap.php';

$akcija = $_GET['akcija'] ?? null;

if ($akcija) {
    switch ($akcija) {
        case 'kosarica_dodaj':
            (new CartController())->add();
            break;
        case 'kosarica_prikazi':
            (new CartController())->index();
            break;
        case 'kosarica_azuriraj':
            (new CartController())->update();
            break;
        case 'kosarica_ukloni':
            (new CartController())->remove();
            break;
        case 'kosarica_isprazni':
            (new CartController())->clear();
            break;
        case 'narudzba_status':
            (new OrderController())->updateStatus();
            break;
        case 'vrijeme':
            (new WeatherController())->show();
            break;
        case 'lokacija_pretrazi':
            (new GeocodingController())->search();
            break;
        case 'dostava_izracunaj':
            (new GeocodingController())->deliveryQuote();
            break;
        case 'dostava_prihvati':
            (new OrderController())->acceptDelivery();
            break;
        case 'dostava_dodijeli':
            (new OrderController())->assignDelivery();
            break;
        default:
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['greska' => 'Nepoznata akcija']);
    }
    exit;
}

$stranica = $_GET['stranica'] ?? 'pocetna';

switch ($stranica) {
    case 'pocetna':
        $podaci = ['restorani' => (new RestaurantController())->index()];
        require __DIR__ . '/../app/views/restorani/pocetna.php';
        break;

    case 'restorani':
        $podaci = ['restorani' => (new RestaurantController())->index()];
        $podaci['kategorije'] = Database::getConnection()->query('SELECT * FROM kategorije ORDER BY naziv')->fetchAll();
        require __DIR__ . '/../app/views/restorani/lista.php';
        break;

    case 'podrska':
        require __DIR__ . '/../app/views/info/podrska.php';
        break;

    case 'onama':
        require __DIR__ . '/../app/views/info/onama.php';
        break;

    case 'restoran':
        $restoranId = (int) ($_GET['id'] ?? 0);
        $podaci = ['restoran' => (new RestaurantController())->show($restoranId)];

        $imaFiltere = isset($_GET['kategorija_id'], $_GET['pojam']) ||
            !empty($_GET['kategorija_id']) || !empty($_GET['cijena_min']) ||
            !empty($_GET['cijena_max']) || !empty($_GET['pojam']);

        $podaci['jela'] = $imaFiltere
            ? (new \Dish())->filter([
                'restoran_id'   => $restoranId,
                'kategorija_id' => $_GET['kategorija_id'] ?? null,
                'cijena_min'    => $_GET['cijena_min'] ?? null,
                'cijena_max'    => $_GET['cijena_max'] ?? null,
                'pojam'         => $_GET['pojam'] ?? null,
            ])
            : (new \Dish())->findByRestaurant($restoranId);

        $podaci['kategorije'] = Database::getConnection()->query('SELECT * FROM kategorije ORDER BY naziv')->fetchAll();
        require __DIR__ . '/../app/views/restorani/detalji.php';
        break;

    case 'registracija':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->register();
        }
        require __DIR__ . '/../app/views/auth/registracija.php';
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->login();
        }
        require __DIR__ . '/../app/views/auth/login.php';
        break;

    case 'odjava':
        (new AuthController())->logout();
        break;

    case 'kosarica':
        Auth::requirePermission('narucivanje');
        $podaci = ['stavke' => (new \Cart())->getForUser(Auth::id())];
        require __DIR__ . '/../app/views/narudzbe/kosarica.php';
        break;

    case 'moje-narudzbe':
        $podaci = ['narudzbe' => (new OrderController())->myOrders()];
        require __DIR__ . '/../app/views/narudzbe/popis.php';
        break;

    case 'narudzba-kreiraj':
        Auth::requirePermission('narucivanje');
        try {
            $orderId = (new OrderController())->store();
            header('Location: ' . BASE_URL . '/public/index.php?stranica=racun&id=' . $orderId);
        } catch (RuntimeException $e) {
            $_SESSION['greske'] = [$e->getMessage()];
            header('Location: ' . BASE_URL . '/public/index.php?stranica=kosarica');
        }
        exit;

    case 'racun':
        Auth::requireLogin();
        $orderController = new OrderController();
        $svojeNarudzbe = $orderController->myOrders();
        $trazenaNarudzba = null;
        foreach ($svojeNarudzbe as $n) {
            if ((int) $n['id'] === (int) ($_GET['id'] ?? 0)) {
                $trazenaNarudzba = $n;
                break;
            }
        }
        if (!$trazenaNarudzba) {
            http_response_code(404);
            echo 'Narudžba nije pronađena.';
            break;
        }
        $podaci = ['narudzba' => $trazenaNarudzba, 'stavke' => $orderController->items((int) $trazenaNarudzba['id'])];
        require __DIR__ . '/../app/views/narudzbe/racun.php';
        break;

    case 'admin-korisnici':
        $podaci = ['korisnici' => (new UserController())->index()];
        require __DIR__ . '/../app/views/korisnici/admin_popis.php';
        break;

    case 'admin-korisnik-uloga':
        try {
            (new UserController())->updateRoles((int) ($_POST['id'] ?? 0));
            header('Location: ' . BASE_URL . '/public/index.php?stranica=admin-korisnici&poruka=uloge_spremljene');
        } catch (InvalidArgumentException $e) {
            $_SESSION['admin_greska'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/public/index.php?stranica=admin-korisnici');
        }
        exit;

    case 'admin-korisnik-deaktiviraj':
        try {
            (new UserController())->destroy((int) ($_POST['id'] ?? 0));
        } catch (InvalidArgumentException $e) {
            $_SESSION['admin_greska'] = $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/public/index.php?stranica=admin-korisnici');
        exit;

    case 'moj-restoran':
        $restaurantController = new RestaurantController();
        $podaci = ['restorani' => $restaurantController->myProfiles()];

        if (Auth::hasRole('restoran') && !Auth::hasRole('administrator') && count($podaci['restorani']) === 1) {
            header('Location: ' . BASE_URL . '/public/index.php?stranica=restoran-profil&id=' . (int) $podaci['restorani'][0]['id']);
            exit;
        }

        require __DIR__ . '/../app/views/restorani/moj_restoran.php';
        break;

    case 'restoran-profil':
        Auth::requireAnyRole(['restoran', 'administrator']);
        $restoranId = (int) ($_GET['id'] ?? 0);
        if ($restoranId <= 0) {
            if (Auth::hasRole('restoran') && !Auth::hasRole('administrator')) {
                $dodijeljeni = (new RestaurantController())->myAssignedRestaurant();
                if ($dodijeljeni) {
                    header('Location: ' . BASE_URL . '/public/index.php?stranica=restoran-profil&id=' . (int) $dodijeljeni['id']);
                } else {
                    header('Location: ' . BASE_URL . '/public/index.php?stranica=moj-restoran');
                }
            } else {
                header('Location: ' . BASE_URL . '/public/index.php?stranica=moj-restoran');
            }
            exit;
        }
        $orderController = new OrderController();
        $dashboard = $orderController->restaurantDashboard($restoranId);
        $podaci = $dashboard;
        $podaci['jela'] = (new \Dish())->findByRestaurant($restoranId);
        $podaci['kategorije'] = Database::getConnection()->query('SELECT * FROM kategorije ORDER BY naziv')->fetchAll();
        require __DIR__ . '/../app/views/restorani/profil.php';
        break;

    case 'restoran-forma':
        Auth::requireRole('administrator');
        $restaurantController = new RestaurantController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!empty($_POST['id'])) {
                    $spremljeniRestoranId = (int) $_POST['id'];
                    $restaurantController->update($spremljeniRestoranId);
                } else {
                    $spremljeniRestoranId = $restaurantController->store();
                }
                header('Location: ' . BASE_URL . '/public/index.php?stranica=restoran-profil&id=' . $spremljeniRestoranId);
            } catch (InvalidArgumentException $e) {
                $_SESSION['greske'] = [$e->getMessage()];
                $suffix = !empty($_POST['id']) ? '&id=' . (int) $_POST['id'] : '';
                header('Location: ' . BASE_URL . '/public/index.php?stranica=restoran-forma' . $suffix);
            }
            exit;
        }

        $restoranZaUredivanje = !empty($_GET['id']) ? $restaurantController->show((int) $_GET['id']) : null;
        $currentRestaurantId = $restoranZaUredivanje ? (int) $restoranZaUredivanje['id'] : null;
        $podaci = [
            'restoran' => $restoranZaUredivanje,
            'vlasnici' => (new \User())->findAvailableRestaurantOwners($currentRestaurantId),
            'predlozeni_vlasnik_id' => (int) ($_GET['vlasnik_id'] ?? 0),
        ];
        require __DIR__ . '/../app/views/restorani/forma.php';
        break;

    case 'jelo-forma':
        Auth::requireAnyRole(['restoran', 'administrator']);
        $dishController = new DishController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ciljniRestoranId = (int) ($_POST['restoran_id'] ?? 0);
            if (!empty($_POST['id'])) {
                $postojeceJelo = $dishController->requireDishManagement((int) $_POST['id']);
                $ciljniRestoranId = (int) $postojeceJelo['restoran_id'];
                $dishController->update((int) $_POST['id']);
            } else {
                $dishController->store();
            }
            header('Location: ' . BASE_URL . '/public/index.php?stranica=restoran-profil&id=' . $ciljniRestoranId . '#jelovnik');
            exit;
        }

        $jelo = null;
        $restoranIdZaFormu = (int) ($_GET['restoran_id'] ?? 0);
        if (!empty($_GET['id'])) {
            $jelo = $dishController->requireDishManagement((int) $_GET['id']);
            $restoranIdZaFormu = (int) $jelo['restoran_id'];
        } else {
            $dishController->requireRestaurantManagement($restoranIdZaFormu);
        }

        $podaci = [
            'jelo' => $jelo,
            'restoran_id' => $restoranIdZaFormu,
            'kategorije' => Database::getConnection()->query('SELECT * FROM kategorije ORDER BY naziv')->fetchAll(),
        ];
        require __DIR__ . '/../app/views/jela/forma.php';
        break;

    case 'jelo-obrisi':
        Auth::requireLogin();
        $dishController = new DishController();
        $jeloZaBrisanje = $dishController->show((int) ($_POST['id'] ?? 0));
        $ciljniRestoranId = (int) ($jeloZaBrisanje['restoran_id'] ?? 0);
        $rezultatBrisanja = $dishController->destroy((int) ($_POST['id'] ?? 0));
        $_SESSION['jelo_poruka'] = $rezultatBrisanja === 'archived'
            ? 'Jelo je uklonjeno iz jelovnika. Povijest postojećih narudžbi je sačuvana.'
            : 'Jelo je uspješno obrisano.';
        header('Location: ' . BASE_URL . '/public/index.php?stranica=restoran-profil&id=' . $ciljniRestoranId . '#jelovnik');
        exit;

    case 'dostupne-dostave':
        $orderController = new OrderController();
        $podaci = [
            'trenutne' => $orderController->deliveryBoard(),
            'moje'     => $orderController->myDeliveries(),
        ];
        require __DIR__ . '/../app/views/narudzbe/dostavljac.php';
        break;

    case 'admin-narudzbe':
        $orderController = new OrderController();
        $podaci = [
            'narudzbe' => $orderController->allOrders(),
            'dostavljaci' => $orderController->deliveryPeople(),
        ];
        require __DIR__ . '/../app/views/narudzbe/admin_popis.php';
        break;

    case 'restoran-narudzbe':
        Auth::requireAnyRole(['restoran', 'administrator']);
        $restoranId = (int) ($_GET['id'] ?? 0);
        if ($restoranId > 0) {
            header('Location: ' . BASE_URL . '/public/index.php?stranica=restoran-profil&id=' . $restoranId . '#narudzbe');
        } else {
            header('Location: ' . BASE_URL . '/public/index.php?stranica=restoran-profil');
        }
        exit;

    default:
        http_response_code(404);
        echo '404 - Stranica nije pronađena.';
}
