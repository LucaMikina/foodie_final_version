<?php

class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES);
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function verify(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $poslani = $_POST['csrf_token'] ?? '';
        $ocekivani = $_SESSION[self::SESSION_KEY] ?? '';

        if (!$ocekivani || !hash_equals($ocekivani, $poslani)) {
            http_response_code(419);
            exit('Sigurnosni token nije valjan. Osvježite stranicu i pokušajte ponovno.');
        }
    }

    public static function verifyJson(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $poslani = $_POST['csrf_token'] ?? '';
        $ocekivani = $_SESSION[self::SESSION_KEY] ?? '';

        if (!$ocekivani || !hash_equals($ocekivani, $poslani)) {
            http_response_code(419);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['greska' => 'Sigurnosni token nije valjan. Osvježite stranicu.']);
            exit;
        }
    }
}
