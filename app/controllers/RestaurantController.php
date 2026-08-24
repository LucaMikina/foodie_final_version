<?php
require_once __DIR__ . '/../models/Restaurant.php';
require_once __DIR__ . '/../models/User.php';

class RestaurantController
{
    private Restaurant $model;

    public function __construct()
    {
        $this->model = new Restaurant();
    }

    public function index(): array
    {
        $pojam = trim($_GET['pojam'] ?? '');
        $kategorijaId = (int) ($_GET['kategorija_id'] ?? 0);

        if ($kategorijaId > 0) {
            return $this->model->filterByCategory($kategorijaId, $pojam);
        }

        return $this->model->search($pojam);
    }

    public function show(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function myProfiles(): array
    {
        Auth::requireAnyRole(['restoran', 'administrator']);

        if (Auth::hasRole('administrator')) {
            return $this->model->allWithProfileStats();
        }

        $profil = $this->model->findAssignedToOwnerWithProfileStats((int) Auth::id());
        return $profil ? [$profil] : [];
    }

    public function myAssignedRestaurant(): ?array
    {
        Auth::requireRole('restoran');
        return $this->model->findAssignedToOwner((int) Auth::id());
    }

    public function requireManageAccess(int $id): array
    {
        Auth::requireLogin();
        $restoran = $this->model->find($id);

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

            $dodijeljeni = $this->model->findAssignedToOwner((int) Auth::id());
            if (!$dodijeljeni || (int) $dodijeljeni['id'] !== $id) {
                http_response_code(403);
                require __DIR__ . '/../views/greske/403.php';
                exit;
            }
        }

        return $restoran;
    }

    public function store(): int
    {
        Auth::requireRole('administrator');
        Csrf::verify();

        $vlasnikId = (int) ($_POST['vlasnik_id'] ?? 0);
        $this->assertOwnerCanReceiveRestaurant($vlasnikId);

        $slika = Uploader::handle('slika', 'restorani');

        return $this->model->create([
            'vlasnik_id' => $vlasnikId,
            'naziv'      => trim($_POST['naziv'] ?? ''),
            'adresa'     => trim($_POST['adresa'] ?? ''),
            'opis'       => HtmlSanitizer::clean($_POST['opis'] ?? ''),
            'slika'      => $slika,
        ]);
    }

    public function update(int $id): bool
    {
        Auth::requireRole('administrator');
        Csrf::verify();

        $restoran = $this->model->find($id);
        if (!$restoran) {
            throw new InvalidArgumentException('Restoran nije pronađen.');
        }

        $vlasnikId = (int) ($_POST['vlasnik_id'] ?? $restoran['vlasnik_id']);
        $this->assertOwnerCanReceiveRestaurant($vlasnikId, $id);

        $data = [
            'vlasnik_id' => $vlasnikId,
            'naziv'      => trim($_POST['naziv'] ?? $restoran['naziv']),
            'adresa'     => trim($_POST['adresa'] ?? $restoran['adresa']),
            'opis'       => isset($_POST['opis']) ? HtmlSanitizer::clean($_POST['opis']) : $restoran['opis'],
        ];

        if ($novaSlika = Uploader::handle('slika', 'restorani')) {
            $data['slika'] = $novaSlika;
        }

        return $this->model->update($id, $data);
    }

    public function destroy(int $id): bool
    {
        Auth::requireRole('administrator');
        Csrf::verify();
        return $this->model->deactivate($id);
    }

    private function assertOwnerCanReceiveRestaurant(int $vlasnikId, ?int $currentRestaurantId = null): void
    {
        if ($vlasnikId <= 0) {
            throw new InvalidArgumentException('Odaberite korisnika kojem se dodjeljuje restoran.');
        }

        $userModel = new User();
        if (!$userModel->hasRole($vlasnikId, 'restoran')) {
            throw new InvalidArgumentException('Odabrani korisnik mora imati ulogu Restoran.');
        }

        if ($this->model->ownerHasRestaurant($vlasnikId, $currentRestaurantId)) {
            throw new InvalidArgumentException('Taj korisnik već ima dodijeljen restoran. Jedan račun restorana može pratiti samo jedan restoran.');
        }
    }
}
