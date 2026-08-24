<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/User.php';

class OrderController
{
    private Order $model;
    private Cart $cartModel;

    public function __construct()
    {
        $this->model = new Order();
        $this->cartModel = new Cart();
    }

    public function store(): int
    {
        Auth::requirePermission('narucivanje');
        Csrf::verify();

        $stavkeKosarice = $this->cartModel->getForUser(Auth::id());
        if (empty($stavkeKosarice)) {
            throw new RuntimeException('Košarica je prazna.');
        }

        $restoranId = $this->resolveRestaurantId($stavkeKosarice[0]['jelo_id']);
        $stavke = array_map(fn($s) => [
            'jelo_id'  => $s['jelo_id'],
            'kolicina' => $s['kolicina'],
            'cijena'   => $s['cijena'],
        ], $stavkeKosarice);

        $adresa = trim($_POST['adresa_dostave'] ?? '');
        if ($adresa === '') {
            throw new RuntimeException('Upišite adresu dostave.');
        }

        $orderData = [
            'kupac_id'                => Auth::id(),
            'restoran_id'             => $restoranId,
            'status'                  => 'primljena',
            'adresa_dostave'          => $adresa,
            'zeljeno_vrijeme_dostave' => $this->parsirajDatum($_POST['zeljeno_vrijeme_dostave'] ?? ''),
        ];

        $deliveryLat = $this->parsirajKoordinatu($_POST['dostava_lat'] ?? null, -90, 90);
        $deliveryLng = $this->parsirajKoordinatu($_POST['dostava_lng'] ?? null, -180, 180);

        if ($deliveryLat === null || $deliveryLng === null) {
            throw new RuntimeException('Odaberite lokaciju dostave na karti.');
        }

        if ($this->model->supportsDeliveryCoordinates()) {
            $orderData['dostava_lat'] = $deliveryLat;
            $orderData['dostava_lng'] = $deliveryLng;
        }

        $restoran = (new Restaurant())->find($restoranId);

        if (!$restoran) {
            throw new RuntimeException('Restoran nije pronađen.');
        }

        try {
            $quote = (new GeocodingService())->deliveryQuote((string) $restoran['adresa'], $deliveryLat, $deliveryLng);
        } catch (Throwable $e) {
            throw new RuntimeException('Nije moguće izračunati dostavu za odabranu lokaciju. Pokušajte ponovno.');
        }

        $orderData['_delivery_fee'] = $quote['cijena'];

        if ($this->model->supportsDeliveryPricing()) {
            $orderData['cijena_dostave'] = $quote['cijena'];
            $orderData['dostava_km'] = $quote['km'];
        }

        $orderId = $this->model->createWithItems($orderData, $stavke);
        $this->cartModel->clearForUser(Auth::id());
        return $orderId;
    }

    public function myOrders(): array
    {
        Auth::requirePermission('narucivanje');
        return $this->model->findByCustomer(Auth::id());
    }

    public function items(int $orderId): array
    {
        return $this->model->getItems($orderId);
    }

    public function updateStatus(): void
    {
        Auth::requireLogin();
        Csrf::verifyJson();
        header('Content-Type: application/json; charset=utf-8');

        $orderId = (int) ($_POST['narudzba_id'] ?? 0);
        $status  = trim((string) ($_POST['status'] ?? ''));
        $narudzba = $this->model->find($orderId);

        if (!$narudzba) {
            http_response_code(404);
            echo json_encode(['greska' => 'Narudžba nije pronađena.']);
            return;
        }

        if (!$this->smijeUpravljatiNarudzbom($narudzba)) {
            http_response_code(403);
            echo json_encode(['greska' => 'Nemate ovlasti nad ovom narudžbom.']);
            return;
        }

        if (!$this->dozvoljenPrijelazStatusa($narudzba, $status)) {
            http_response_code(422);
            echo json_encode(['greska' => 'Ovaj prijelaz statusa nije dozvoljen.']);
            return;
        }

        $uspjeh = $this->isDispatchAdmin()
            ? $this->model->updateStatus($orderId, $status)
            : $this->model->updateStatusIfCurrent($orderId, (string) $narudzba['status'], $status);

        if (!$uspjeh) {
            http_response_code(409);
            echo json_encode(['greska' => 'Narudžba je u međuvremenu promijenila status. Osvježite stranicu.']);
            return;
        }

        echo json_encode(['uspjeh' => true, 'status' => $status]);
    }

    private function smijeUpravljatiNarudzbom(array $narudzba): bool
    {
        if ($this->isDispatchAdmin()) {
            return true;
        }
        if (Auth::hasRole('restoran')) {
            $restaurantModel = new Restaurant();
            $restoran = $restaurantModel->findAssignedToOwner((int) Auth::id());
            return $restoran && (int) $restoran['id'] === (int) $narudzba['restoran_id'];
        }
        if (Auth::hasRole('dostavljac')) {
            return (int) ($narudzba['dostavljac_id'] ?? 0) === Auth::id();
        }
        return false;
    }

    private function dozvoljenPrijelazStatusa(array $narudzba, string $noviStatus): bool
    {
        if ($this->isDispatchAdmin()) {
            return in_array($noviStatus, ['primljena', 'prihvacena', 'priprema', 'na_dostavi', 'dostavljena', 'otkazana'], true);
        }

        $trenutni = (string) ($narudzba['status'] ?? '');

        if (Auth::hasRole('dostavljac')) {
            if ((int) ($narudzba['dostavljac_id'] ?? 0) !== Auth::id()) {
                return false;
            }
            return ($trenutni === 'priprema' && $noviStatus === 'na_dostavi')
                || ($trenutni === 'na_dostavi' && $noviStatus === 'dostavljena');
        }

        if (Auth::hasRole('restoran')) {

            if ($trenutni === 'primljena') {
                return in_array($noviStatus, ['prihvacena', 'otkazana'], true);
            }
            if ($trenutni === 'prihvacena') {
                return in_array($noviStatus, ['priprema', 'otkazana'], true);
            }
            return false;
        }

        return false;
    }

    public function deliveryBoard(): array
    {
        Auth::requireRole('dostavljac');
        return $this->model->findDeliveryBoard();
    }

    public function availableForPickup(): array
    {
        Auth::requireRole('dostavljac');
        return $this->model->findAvailableForPickup();
    }

    public function acceptDelivery(): void
    {
        Auth::requireLogin();
        Csrf::verifyJson();
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::hasRole('dostavljac')) {
            http_response_code(403);
            echo json_encode(['greska' => 'Samo dostavljači mogu prihvatiti dostavu.']);
            return;
        }

        $orderId = (int) ($_POST['narudzba_id'] ?? 0);
        $uspjeh = $this->model->acceptDelivery($orderId, (int) Auth::id());

        if (!$uspjeh) {
            http_response_code(409);
            echo json_encode([
                'uspjeh' => false,
                'greska' => 'Narudžba više nije slobodna ili još nije spremna za preuzimanje.'
            ]);
            return;
        }

        echo json_encode(['uspjeh' => true, 'dostava_stanje' => 'zauzeto']);
    }

    public function assignDelivery(): void
    {
        Auth::requireLogin();
        Csrf::verifyJson();
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isDispatchAdmin()) {
            http_response_code(403);
            echo json_encode(['greska' => 'Samo administrator može dodijeliti dostavljača.']);
            return;
        }

        $orderId = (int) ($_POST['narudzba_id'] ?? 0);
        $dostavljacId = (int) ($_POST['dostavljac_id'] ?? 0);
        if ($orderId <= 0 || $dostavljacId <= 0) {
            http_response_code(422);
            echo json_encode(['greska' => 'Odaberite valjanu narudžbu i dostavljača.']);
            return;
        }

        $userModel = new User();
        $dostavljaci = $userModel->findByRole('dostavljac');
        $valjanDostavljac = false;
        foreach ($dostavljaci as $d) {
            if ((int) $d['id'] === $dostavljacId) {
                $valjanDostavljac = true;
                break;
            }
        }

        if (!$valjanDostavljac) {
            http_response_code(422);
            echo json_encode(['greska' => 'Odabrani korisnik nije aktivan dostavljač.']);
            return;
        }

        $uspjeh = $this->model->assignDelivery($orderId, $dostavljacId);
        if (!$uspjeh) {
            http_response_code(409);
            echo json_encode([
                'uspjeh' => false,
                'greska' => 'Narudžba je već zauzeta, završena ili je u međuvremenu promijenila status.'
            ]);
            return;
        }

        echo json_encode(['uspjeh' => true, 'dostava_stanje' => 'zauzeto']);
    }

    public function myDeliveries(): array
    {
        Auth::requireRole('dostavljac');
        return $this->model->findActiveByDeliveryPerson((int) Auth::id());
    }

    public function myRestaurantOrders(int $restoranId = 0): array
    {
        Auth::requireAnyRole(['restoran', 'administrator']);
        $restaurantModel = new Restaurant();

        if (!Auth::hasRole('administrator')) {
            $dodijeljeni = $restaurantModel->findAssignedToOwner((int) Auth::id());
            if (!$dodijeljeni) {
                return [];
            }
            if ($restoranId > 0 && $restoranId !== (int) $dodijeljeni['id']) {
                http_response_code(403);
                require __DIR__ . '/../views/greske/403.php';
                exit;
            }
            $restoranId = (int) $dodijeljeni['id'];
        } elseif ($restoranId <= 0) {
            return [];
        }

        $restoran = $restaurantModel->find($restoranId);
        if (!$restoran) {
            return [];
        }

        return $this->model->findByRestaurantWithDetails($restoranId);
    }

    public function restaurantDashboard(int $restoranId): array
    {
        Auth::requireAnyRole(['restoran', 'administrator']);
        $restaurantModel = new Restaurant();
        $restoran = $restaurantModel->find($restoranId);

        if (!$restoran) {
            http_response_code(404);
            exit('Restoran nije pronađen.');
        }
        if (!Auth::hasRole('administrator')) {
            $dodijeljeni = $restaurantModel->findAssignedToOwner((int) Auth::id());
            if (!$dodijeljeni || (int) $dodijeljeni['id'] !== $restoranId) {
                http_response_code(403);
                require __DIR__ . '/../views/greske/403.php';
                exit;
            }
        }

        $narudzbe = $this->model->findByRestaurantWithDetails($restoranId);
        $stavke = $this->model->getItemsForOrders(array_column($narudzbe, 'id'));

        $statistika = [
            'nove' => 0,
            'aktivne' => 0,
            'dostavljene' => 0,
            'otkazane' => 0,
            'prihod' => 0.0,
        ];
        foreach ($narudzbe as $narudzba) {
            $status = (string) $narudzba['status'];
            if ($status === 'primljena') $statistika['nove']++;
            if (in_array($status, ['prihvacena', 'priprema', 'na_dostavi'], true)) $statistika['aktivne']++;
            if ($status === 'dostavljena') {
                $statistika['dostavljene']++;
                $statistika['prihod'] += (float) $narudzba['ukupna_cijena'];
            }
            if ($status === 'otkazana') $statistika['otkazane']++;
        }

        return [
            'restoran' => $restoran,
            'narudzbe' => $narudzbe,
            'stavke_po_narudzbi' => $stavke,
            'statistika' => $statistika,
        ];
    }

    public function allOrders(): array
    {
        Auth::requireLogin();
        if (!$this->isDispatchAdmin()) {
            http_response_code(403);
            require __DIR__ . '/../views/greske/403.php';
            exit;
        }
        return $this->model->allWithDetails();
    }

    public function deliveryPeople(): array
    {
        Auth::requireLogin();
        if (!$this->isDispatchAdmin()) {
            return [];
        }
        return (new User())->findByRole('dostavljac');
    }

    private function isDispatchAdmin(): bool
    {
        return Auth::hasAnyRole(['administrator', 'superadministrator']);
    }

    private function resolveRestaurantId(int $jeloId): int
    {
        $dish = new Dish();
        $jelo = $dish->find($jeloId);
        return (int) $jelo['restoran_id'];
    }

    private function parsirajKoordinatu($vrijednost, float $min, float $max): ?float
    {
        if ($vrijednost === null || trim((string) $vrijednost) === '') {
            return null;
        }
        if (!is_numeric($vrijednost)) {
            return null;
        }
        $broj = (float) $vrijednost;
        return ($broj >= $min && $broj <= $max) ? $broj : null;
    }

    private function parsirajDatum(string $vrijednost): ?string
    {
        $vrijednost = trim($vrijednost);
        if ($vrijednost === '') {
            return null;
        }

        $datum = DateTime::createFromFormat('d.m.y', $vrijednost)
            ?: DateTime::createFromFormat('d.m.Y', $vrijednost);

        return $datum ? $datum->format('Y-m-d H:i:s') : null;
    }
}
