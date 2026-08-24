<?php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register(): void
    {
        Csrf::verify();
        $greske = [];

        $ime     = trim($_POST['ime'] ?? '');
        $prezime = trim($_POST['prezime'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $lozinka = $_POST['lozinka'] ?? '';
        $uloga   = 'kupac';

        if ($ime === '' || $prezime === '') {
            $greske[] = 'Ime i prezime su obavezni.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $greske[] = 'Unesite ispravnu e-mail adresu.';
        }
        if (strlen($lozinka) < 6) {
            $greske[] = 'Lozinka mora imati najmanje 6 znakova.';
        }
        if ($this->userModel->findByEmail($email)) {
            $greske[] = 'Korisnik s tom e-mail adresom već postoji.';
        }

        if (!empty($greske)) {
            $_SESSION['greske'] = $greske;
            header('Location: ' . BASE_URL . '/public/index.php?stranica=registracija');
            exit;
        }

        $this->userModel->register([
            'ime'     => $ime,
            'prezime' => $prezime,
            'email'   => $email,
            'lozinka' => $lozinka,
        ], $uloga);

        header('Location: ' . BASE_URL . '/public/index.php?stranica=login&poruka=registracija_uspjesna');
        exit;
    }

    public function login(): void
    {
        Csrf::verify();
        $this->guardAgainstBruteForce();

        $email   = trim($_POST['email'] ?? '');
        $lozinka = $_POST['lozinka'] ?? '';

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($lozinka, $user['lozinka'])) {
            $this->registerFailedAttempt();
            $_SESSION['greske'] = ['Neispravna e-mail adresa ili lozinka.'];
            header('Location: ' . BASE_URL . '/public/index.php?stranica=login');
            exit;
        }

        unset($_SESSION['login_pokusaji'], $_SESSION['login_blokiran_do']);

        if ($user['status'] !== 'aktivan') {
            $_SESSION['greske'] = ['Vaš račun je deaktiviran. Kontaktirajte administratora.'];
            header('Location: ' . BASE_URL . '/public/index.php?stranica=login');
            exit;
        }

        $roles = $this->userModel->getRoles((int) $user['id']);
        Auth::login($user, $roles);

        header('Location: ' . BASE_URL . '/public/index.php?stranica=pocetna');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . BASE_URL . '/public/index.php?stranica=login');
        exit;
    }

    private const MAX_POKUSAJA = 5;
    private const BLOKADA_SEKUNDI = 60;

    private function guardAgainstBruteForce(): void
    {
        $blokiranDo = $_SESSION['login_blokiran_do'] ?? 0;
        if (time() < $blokiranDo) {
            $preostalo = $blokiranDo - time();
            $_SESSION['greske'] = ["Previše neuspjelih pokušaja. Pokušajte ponovno za {$preostalo}s."];
            header('Location: ' . BASE_URL . '/public/index.php?stranica=login');
            exit;
        }
    }

    private function registerFailedAttempt(): void
    {
        $_SESSION['login_pokusaji'] = ($_SESSION['login_pokusaji'] ?? 0) + 1;
        if ($_SESSION['login_pokusaji'] >= self::MAX_POKUSAJA) {
            $_SESSION['login_blokiran_do'] = time() + self::BLOKADA_SEKUNDI;
            $_SESSION['login_pokusaji'] = 0;
        }
    }
}
