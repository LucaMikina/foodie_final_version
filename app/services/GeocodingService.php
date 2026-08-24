<?php
class GeocodingService
{
    private const GEOCODE_ENDPOINT = 'https://api.geoapify.com/v1/geocode/search';
    private const ROUTING_ENDPOINT = 'https://api.geoapify.com/v1/routing';

    public function search(string $query): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 3) {
            return [];
        }

        $url = self::GEOCODE_ENDPOINT . '?' . http_build_query([
            'text' => $query,
            'format' => 'json',
            'limit' => 6,
            'lang' => 'hr',
            'filter' => 'countrycode:ba',
            'apiKey' => $this->apiKey(),
        ]);

        $data = $this->requestJson($url);
        $results = [];

        foreach (($data['results'] ?? []) as $row) {
            $lat = filter_var($row['lat'] ?? null, FILTER_VALIDATE_FLOAT);
            $lng = filter_var($row['lon'] ?? null, FILTER_VALIDATE_FLOAT);
            $label = trim((string) ($row['formatted'] ?? ''));

            if ($lat === false || $lng === false || $label === '') {
                continue;
            }

            $results[] = [
                'naziv' => $label,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'tip' => (string) ($row['result_type'] ?? ''),
            ];
        }

        return $results;
    }

    public function first(string $query): ?array
    {
        $results = $this->search($query);
        return $results[0] ?? null;
    }

    public function route(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        $url = self::ROUTING_ENDPOINT . '?' . http_build_query([
            'waypoints' => $fromLat . ',' . $fromLng . '|' . $toLat . ',' . $toLng,
            'mode' => 'drive',
            'apiKey' => $this->apiKey(),
        ]);

        $data = $this->requestJson($url);
        $feature = $data['features'][0] ?? null;
        $properties = is_array($feature) ? ($feature['properties'] ?? []) : [];
        $distance = (float) ($properties['distance'] ?? 0);
        $time = (float) ($properties['time'] ?? 0);

        if ($distance <= 0) {
            throw new RuntimeException('Nije moguće izračunati cestovnu udaljenost.');
        }

        return [
            'distance_m' => $distance,
            'duration_s' => max(0, $time),
        ];
    }

    public function deliveryQuote(string $restaurantAddress, float $deliveryLat, float $deliveryLng): array
    {
        $restaurant = $this->first($restaurantAddress);

        if (!$restaurant) {
            throw new RuntimeException('Lokacija restorana nije pronađena.');
        }

        $route = $this->route(
            (float) $restaurant['lat'],
            (float) $restaurant['lng'],
            $deliveryLat,
            $deliveryLng
        );

        $km = round($route['distance_m'] / 1000, 2);
        $base = defined('DELIVERY_BASE_FEE') ? (float) DELIVERY_BASE_FEE : 2.0;
        $perKm = defined('DELIVERY_PER_KM') ? (float) DELIVERY_PER_KM : 0.7;
        $min = defined('DELIVERY_MIN_FEE') ? (float) DELIVERY_MIN_FEE : 2.5;
        $max = defined('DELIVERY_MAX_FEE') ? (float) DELIVERY_MAX_FEE : 15.0;
        $fee = round(max($min, min($max, $base + ($km * $perKm))), 2);

        return [
            'km' => $km,
            'cijena' => $fee,
            'trajanje_min' => max(1, (int) ceil($route['duration_s'] / 60)),
            'restoran_lat' => (float) $restaurant['lat'],
            'restoran_lng' => (float) $restaurant['lng'],
            'restoran_adresa' => (string) $restaurant['naziv'],
        ];
    }

    private function apiKey(): string
    {
        $key = defined('GEOAPIFY_API_KEY') ? trim((string) GEOAPIFY_API_KEY) : '';

        if ($key === '') {
            throw new RuntimeException('Pretraga lokacije nije dostupna.');
        }

        return $key;
    }

    private function requestJson(string $url): array
    {
        $body = null;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
            ]);
            $response = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if (is_string($response) && $status >= 200 && $status < 300) {
                $body = $response;
            }
        } elseif (ini_get('allow_url_fopen')) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'header' => "Accept: application/json\r\n",
                ],
            ]);
            $response = @file_get_contents($url, false, $context);

            if (is_string($response)) {
                $body = $response;
            }
        }

        if ($body === null) {
            throw new RuntimeException('Pretraga lokacije trenutno nije dostupna.');
        }

        $data = json_decode($body, true);

        if (!is_array($data)) {
            throw new RuntimeException('Pretraga lokacije trenutno nije dostupna.');
        }

        return $data;
    }
}
