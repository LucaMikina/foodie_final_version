<?php
require_once __DIR__ . '/../models/User.php';

class UserController
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function index(): array
    {
        Auth::requireRole('administrator');

        $pojam = trim($_GET['pojam'] ?? '');
        $uloga = trim($_GET['uloga'] ?? '');

        if ($pojam !== '' || $uloga !== '') {
            return $this->model->search($pojam, $uloga);
        }

        return $this->model->all([], 'prezime ASC');
    }

    public function store(): int
    {
        Auth::requireRole('administrator');
        Csrf::verify();
        return $this->model->register([
            'ime'     => trim($_POST['ime'] ?? ''),
            'prezime' => trim($_POST['prezime'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'lozinka' => $_POST['lozinka'] ?? bin2hex(random_bytes(4)),
        ], $_POST['uloga'] ?? 'kupac');
    }

    public function update(int $id): bool
    {
        Auth::requireRole('administrator');
        Csrf::verify();
        return $this->model->update($id, [
            'ime'     => trim($_POST['ime'] ?? ''),
            'prezime' => trim($_POST['prezime'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
        ]);
    }

    public function destroy(int $id): bool
    {
        Auth::requireRole('administrator');
        Csrf::verify();
        if ($id === Auth::id()) {
            throw new InvalidArgumentException('Ne možeš deaktivirati vlastiti administratorski račun.');
        }
        return $this->model->deactivate($id);
    }

    public function updateRoles(int $id): bool
    {
        Auth::requireRole('administrator');
        Csrf::verify();

        $uloge = $_POST['uloge'] ?? [];
        if (!is_array($uloge)) {
            $uloge = [$uloge];
        }

        if ($id === Auth::id() && !in_array('administrator', $uloge, true)) {
            $uloge[] = 'administrator';
        }

        if (!in_array('restoran', $uloge, true)) {
            $dodijeljeniRestoran = (new Restaurant())->findAssignedToOwner($id);
            if ($dodijeljeniRestoran) {
                throw new InvalidArgumentException(
                    'Korisniku je dodijeljen restoran "' . $dodijeljeniRestoran['naziv'] . '". ' .
                    'Prvo uredi profil restorana i dodijeli ga drugom računu.'
                );
            }
        }

        $uspjeh = $this->model->setRoles($id, $uloge);

        if ($uspjeh && $id === Auth::id()) {
            Auth::refreshRoles();
        }

        return $uspjeh;
    }

    public function changeRole(int $id): bool
    {
        return $this->updateRoles($id);
    }
}
