<?php
require_once __DIR__ . '/../models/Dish.php';
require_once __DIR__ . '/../models/Restaurant.php';

class DishController
{
    private Dish $model;
    private Restaurant $restaurantModel;

    public function __construct()
    {
        $this->model = new Dish();
        $this->restaurantModel = new Restaurant();
    }

    public function index(): array
    {
        return $this->model->filter([
            'kategorija_id' => $_GET['kategorija_id'] ?? null,
            'restoran_id'   => $_GET['restoran_id'] ?? null,
            'cijena_min'    => $_GET['cijena_min'] ?? null,
            'cijena_max'    => $_GET['cijena_max'] ?? null,
            'pojam'         => $_GET['pojam'] ?? null,
        ]);
    }

    public function show(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function requireRestaurantManagement(int $restoranId): array
    {
        Auth::requireLogin();
        $restoran = $this->restaurantModel->find($restoranId);
        if (!$restoran) {
            http_response_code(404);
            exit('Restoran nije pronađen.');
        }

        if (!Auth::hasRole('administrator')) {
            if (!Auth::hasRole('restoran')) {
                http_response_code(403);
                require __DIR__ . '/../views/greske/403.php';
                exit;
            }
            $dodijeljeni = $this->restaurantModel->findAssignedToOwner((int) Auth::id());
            if (!$dodijeljeni || (int) $dodijeljeni['id'] !== $restoranId) {
                http_response_code(403);
                require __DIR__ . '/../views/greske/403.php';
                exit;
            }
        }

        return $restoran;
    }

    public function requireDishManagement(int $id): array
    {
        $jelo = $this->model->find($id);
        if (!$jelo) {
            http_response_code(404);
            exit('Jelo nije pronađeno.');
        }
        $this->requireRestaurantManagement((int) $jelo['restoran_id']);
        return $jelo;
    }

    public function store(): int
    {
        Csrf::verify();
        $restoran = $this->requireRestaurantManagement((int) ($_POST['restoran_id'] ?? 0));
        $slika = Uploader::handle('slika', 'jela');

        return $this->model->create([
            'restoran_id'   => (int) $restoran['id'],
            'kategorija_id' => (int) $_POST['kategorija_id'],
            'naziv'         => trim($_POST['naziv'] ?? ''),
            'opis'          => HtmlSanitizer::clean($_POST['opis'] ?? ''),
            'cijena'        => (float) ($_POST['cijena'] ?? 0),
            'slika'         => $slika,
            'dostupno'      => 1,
        ]);
    }

    public function update(int $id): bool
    {
        Csrf::verify();
        $jelo = $this->requireDishManagement($id);

        $data = [
            'naziv'    => trim($_POST['naziv'] ?? $jelo['naziv']),
            'opis'     => isset($_POST['opis']) ? HtmlSanitizer::clean($_POST['opis']) : $jelo['opis'],
            'cijena'   => (float) ($_POST['cijena'] ?? $jelo['cijena']),
            'dostupno' => isset($_POST['dostupno']) ? 1 : 0,
        ];

        if ($novaSlika = Uploader::handle('slika', 'jela')) {
            $data['slika'] = $novaSlika;
        }

        return $this->model->update($id, $data);
    }

    public function destroy(int $id): string
    {
        Csrf::verify();
        $this->requireDishManagement($id);
        return $this->model->removeSafely($id);
    }
}
