<?php

class WeatherService
{
    private const API_URL = 'https://api.openweathermap.org/data/2.5/weather';
    private const TIMEOUT_SEKUNDI = 5;

    public function getByCity(string $grad, string $countryCode = 'BA'): ?array
    {
        if (!defined('WEATHER_API_KEY') || WEATHER_API_KEY === '') {
            return null;
        }

        $query = http_build_query([
            'q'     => $grad . ',' . $countryCode,
            'appid' => WEATHER_API_KEY,
            'units' => 'metric',
            'lang'  => 'hr',
        ]);

        $context = stream_context_create([
            'http' => ['timeout' => self::TIMEOUT_SEKUNDI, 'ignore_errors' => true],
        ]);

        $response = @file_get_contents(self::API_URL . '?' . $query, false, $context);
        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['main'])) {
            return null;
        }

        return $this->normalize($data);
    }

    private function normalize(array $data): array
    {
        $opisVremena = $data['weather'][0]['description'] ?? '';
        $ikonaKod    = $data['weather'][0]['icon'] ?? '01d';

        return [
            'grad'            => $data['name'] ?? '',
            'temperatura'     => round($data['main']['temp'] ?? 0),
            'osjecaj'         => round($data['main']['feels_like'] ?? 0),
            'opis'            => $opisVremena,
            'ikona_url'       => "https://openweathermap.org/img/wn/{$ikonaKod}@2x.png",
            'vjetar_ms'       => $data['wind']['speed'] ?? 0,
            'upozorenje_dostava' => $this->dostavnoUpozorenje($data),
        ];
    }

    private function dostavnoUpozorenje(array $data): ?string
    {
        $glavniUvjet = strtolower($data['weather'][0]['main'] ?? '');
        $vjetar = $data['wind']['speed'] ?? 0;

        if (in_array($glavniUvjet, ['rain', 'thunderstorm', 'snow'], true)) {
            return 'Trenutni vremenski uvjeti mogu produžiti vrijeme dostave.';
        }
        if ($vjetar > 10) {
            return 'Jak vjetar - dostava biciklom/skuterom može kasniti.';
        }
        return null;
    }
}
