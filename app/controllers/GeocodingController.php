<?php
class GeocodingController
{
    public function search(): void
    {
        Auth::requirePermission('narucivanje');
        header('Content-Type: application/json; charset=utf-8');

        $query = trim((string) ($_GET['q'] ?? ''));

        if (mb_strlen($query) < 3) {
            http_response_code(422);
            echo json_encode(['uspjeh' => false, 'greska' => 'Upišite barem 3 znaka adrese.']);
            return;
        }

        try {
            $results = (new GeocodingService())->search($query);
            echo json_encode(['uspjeh' => true, 'rezultati' => $results], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(503);
            echo json_encode(['uspjeh' => false, 'greska' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function deliveryQuote(): void
    {
        Auth::requirePermission('narucivanje');
        header('Content-Type: application/json; charset=utf-8');

        $lat = filter_var($_GET['lat'] ?? null, FILTER_VALIDATE_FLOAT);
        $lng = filter_var($_GET['lng'] ?? null, FILTER_VALIDATE_FLOAT);

        if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            http_response_code(422);
            echo json_encode(['uspjeh' => false, 'greska' => 'Odaberite valjanu lokaciju dostave.']);
            return;
        }

        $cart = (new Cart())->getForUser(Auth::id());

        if (!$cart) {
            http_response_code(422);
            echo json_encode(['uspjeh' => false, 'greska' => 'Košarica je prazna.']);
            return;
        }

        $restaurant = (new Restaurant())->find((int) $cart[0]['restoran_id']);

        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['uspjeh' => false, 'greska' => 'Restoran nije pronađen.']);
            return;
        }

        try {
            $quote = (new GeocodingService())->deliveryQuote((string) $restaurant['adresa'], (float) $lat, (float) $lng);
            echo json_encode(['uspjeh' => true, 'dostava' => $quote], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(503);
            echo json_encode(['uspjeh' => false, 'greska' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}
