<?php
require_once __DIR__ . '/../models/User.php';

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(array $user, array $roles): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['ime']      = $user['ime'];
        $_SESSION['prezime']  = $user['prezime'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['roles']    = array_column($roles, 'naziv_uloge');
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }

    public static function hasRole(string $uloga): bool
    {
        self::start();
        return in_array($uloga, $_SESSION['roles'] ?? [], true);
    }

    public static function hasAnyRole(array $uloge): bool
    {
        self::start();
        $trenutne = $_SESSION['roles'] ?? [];
        foreach ($uloge as $uloga) {
            if (in_array($uloga, $trenutne, true)) {
                return true;
            }
        }
        return false;
    }

    public static function requireAnyRole(array $uloge): void
    {
        self::requireLogin();
        if (!self::hasAnyRole($uloge)) {
            http_response_code(403);
            require __DIR__ . '/../views/greske/403.php';
            exit;
        }
    }

    public static function refreshRoles(): void
    {
        self::start();
        $userId = self::id();
        if (!$userId) {
            return;
        }
        $userModel = new User();
        $roles = $userModel->getRoles($userId);
        $_SESSION['roles'] = array_column($roles, 'naziv_uloge');
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/public/index.php?stranica=login');
            exit;
        }
    }

    public static function requireRole(string $uloga): void
    {
        self::requireLogin();
        if (!self::hasRole($uloga)) {
            http_response_code(403);
            require __DIR__ . '/../views/greske/403.php';
            exit;
        }
    }

    public static function hasPermission(string $dozvola): bool
    {
        if (!self::check()) {
            return false;
        }

        $userModel = new User();
        return $userModel->hasPermission((int) self::id(), $dozvola);
    }

    public static function requirePermission(string $dozvola): void
    {
        self::requireLogin();
        if (!self::hasPermission($dozvola)) {
            http_response_code(403);
            require __DIR__ . '/../views/greske/403.php';
            exit;
        }
    }
}
