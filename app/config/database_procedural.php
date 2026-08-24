<?php


define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           
define('DB_NAME', 'foodie');


$konekcija = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$konekcija) {
    die('Greška pri spajanju na bazu podataka: ' . mysqli_connect_error());
}

mysqli_set_charset($konekcija, 'utf8mb4');


