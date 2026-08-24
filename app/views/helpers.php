<?php

if (!function_exists('skratiTekst')) {
    function skratiTekst(string $tekst, int $duljina): string
    {
        $tekst = trim($tekst);
        if ($tekst === '') {
            return '';
        }

        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($tekst, 0, $duljina, '...');
        }

        if (strlen($tekst) <= $duljina) {
            return $tekst;
        }

        return substr($tekst, 0, $duljina - 3) . '...';
    }
}

if (!function_exists('foodieKategorijaIkona')) {
    function foodieKategorijaIkona(string $naziv): string
    {
        $naziv = strtolower(trim($naziv));
        $putanja = match ($naziv) {
            'sve'                       => 'sve-photo.png',
            'burgeri', 'burger'         => 'burger-photo.png',
            'pizze', 'pizza'            => 'pizza-photo.png',
            'tortilje', 'tortilja'      => 'sendvici.png',
            'piletina'                  => 'kineski.png',
            'gluten free'               => 'brunch.png',
            'slatko', 'desert'          => 'desert.png',
            'palačinke', 'palacinke'    => 'dorucak.png',
            'roštilj', 'rostilj'        => 'burger-photo.png',
            'kolači', 'kolaci'          => 'kruh-kolaci.png',
            'sushi', 'japanski'         => 'sushi-photo.png',
            'zdravo', 'salata', 'tajlandski' => 'salata-photo.png',
            'kafići', 'kafici', 'kava'  => 'kafici.png',
            'doručak', 'dorucak'        => 'dorucak.png',
            'brunch'                    => 'brunch.png',
            'kruh i kolači', 'kruh i kolaci' => 'kruh-kolaci.png',
            'sendviči', 'sendvici'      => 'sendvici.png',
            'tjestenina', 'talijanski'  => 'tjestenina.png',
            'azijski'                   => 'azijski.png',
            'kineski'                   => 'kineski.png',
            default                     => 'sve-photo.png',
        };

        return BASE_URL . '/public/assets/img/categories/' . $putanja;
    }
}
