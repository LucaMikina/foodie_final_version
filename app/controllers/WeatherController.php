<?php
require_once __DIR__ . '/../services/WeatherService.php';

class WeatherController
{
    private WeatherService $service;

    public function __construct()
    {
        $this->service = new WeatherService();
    }

    public function show(): void
    {
        $grad = trim($_GET['grad'] ?? WEATHER_DEFAULT_CITY);
        $podaci = $this->service->getByCity($grad);

        header('Content-Type: application/json; charset=utf-8');
        if ($podaci === null) {
            http_response_code(503);
            echo json_encode(['greska' => 'Servis vremenske prognoze trenutno nije dostupan.']);
            return;
        }

        echo json_encode($podaci, JSON_UNESCAPED_UNICODE);
    }
}
