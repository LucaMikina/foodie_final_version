<?php
require_once __DIR__ . '/../models/Cart.php';

class CartController
{
    private Cart $model;

    public function __construct()
    {
        $this->model = new Cart();
    }

    public function index(): void
    {
        Auth::requirePermission('narucivanje');
        $this->json($this->model->getForUser(Auth::id()));
    }

    public function add(): void
    {
        Auth::requirePermission('narucivanje');
        Csrf::verifyJson();

        $jeloId = (int) ($_POST['jelo_id'] ?? 0);
        $kolicina = max(1, min(99, (int) ($_POST['kolicina'] ?? 1)));

        if ($jeloId <= 0) {
            $this->json(['greska' => 'Nedostaje jelo_id.'], 400);
            return;
        }

        if (!$this->model->canAddDish(Auth::id(), $jeloId)) {
            $this->json([
                'greska' => 'U jednoj narudžbi možete imati jela samo iz jednog restorana. Najprije završite ili ispraznite trenutnu košaricu.'
            ], 409);
            return;
        }

        $this->model->addItem(Auth::id(), $jeloId, $kolicina);
        $this->json(['uspjeh' => true, 'kosarica' => $this->model->getForUser(Auth::id())]);
    }

    public function update(): void
    {
        Auth::requirePermission('narucivanje');
        Csrf::verifyJson();

        $cartId = (int) ($_POST['cart_id'] ?? 0);
        $kolicina = max(1, min(99, (int) ($_POST['kolicina'] ?? 1)));
        if ($cartId <= 0) {
            $this->json(['greska' => 'Neispravna stavka košarice.'], 400);
            return;
        }

        $this->model->updateQuantityForUser(Auth::id(), $cartId, $kolicina);
        $this->json(['uspjeh' => true, 'kosarica' => $this->model->getForUser(Auth::id())]);
    }

    public function remove(): void
    {
        Auth::requirePermission('narucivanje');
        Csrf::verifyJson();

        $cartId = (int) ($_POST['cart_id'] ?? 0);
        if ($cartId <= 0) {
            $this->json(['greska' => 'Neispravna stavka košarice.'], 400);
            return;
        }

        $this->model->removeItemForUser(Auth::id(), $cartId);
        $this->json(['uspjeh' => true, 'kosarica' => $this->model->getForUser(Auth::id())]);
    }

    public function clear(): void
    {
        Auth::requirePermission('narucivanje');
        Csrf::verifyJson();
        $this->model->clearForUser(Auth::id());
        $this->json(['uspjeh' => true, 'kosarica' => []]);
    }

    private function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
