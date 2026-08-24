
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS foodie;
CREATE DATABASE foodie CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE foodie;

CREATE TABLE korisnici (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ime VARCHAR(50) NOT NULL,
    prezime VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    lozinka VARCHAR(255) NOT NULL,          -- password_hash()
    telefon VARCHAR(20) DEFAULT NULL,
    adresa VARCHAR(255) DEFAULT NULL,
    status ENUM('aktivan','neaktivan') NOT NULL DEFAULT 'aktivan',
    datum_registracije DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE uloge (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv_uloge ENUM('administrator','restoran','dostavljac','kupac') NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE dozvole (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv_dozvole VARCHAR(100) NOT NULL UNIQUE,
    opis VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE uloga_dozvola (
    uloga_id INT NOT NULL,
    dozvola_id INT NOT NULL,
    PRIMARY KEY (uloga_id, dozvola_id),
    FOREIGN KEY (uloga_id) REFERENCES uloge(id) ON DELETE CASCADE,
    FOREIGN KEY (dozvola_id) REFERENCES dozvole(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE korisnik_uloga (
    korisnik_id INT NOT NULL,
    uloga_id INT NOT NULL,
    PRIMARY KEY (korisnik_id, uloga_id),
    FOREIGN KEY (korisnik_id) REFERENCES korisnici(id) ON DELETE CASCADE,
    FOREIGN KEY (uloga_id) REFERENCES uloge(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE restorani (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vlasnik_id INT NOT NULL,
    naziv VARCHAR(100) NOT NULL,
    adresa VARCHAR(255) NOT NULL,
    opis TEXT DEFAULT NULL,                 -- popunjava se preko WYSIWYG (TinyMCE) editora
    slika VARCHAR(255) DEFAULT NULL,
    status ENUM('aktivan','neaktivan') NOT NULL DEFAULT 'aktivan',
    datum_kreiranja DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_restoran_vlasnik (vlasnik_id),
    FOREIGN KEY (vlasnik_id) REFERENCES korisnici(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE kategorije (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE jela (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restoran_id INT NOT NULL,
    kategorija_id INT NOT NULL,
    naziv VARCHAR(100) NOT NULL,
    opis TEXT DEFAULT NULL,
    cijena DECIMAL(8,2) NOT NULL,
    slika VARCHAR(255) DEFAULT NULL,
    dostupno TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (restoran_id) REFERENCES restorani(id) ON DELETE CASCADE,
    FOREIGN KEY (kategorija_id) REFERENCES kategorije(id)
) ENGINE=InnoDB;

CREATE TABLE narudzbe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kupac_id INT NOT NULL,
    restoran_id INT NOT NULL,
    dostavljac_id INT DEFAULT NULL,
    status ENUM('primljena','prihvacena','priprema','na_dostavi','dostavljena','otkazana') NOT NULL DEFAULT 'primljena',
    adresa_dostave VARCHAR(255) NOT NULL,
    dostava_lat DECIMAL(10,7) DEFAULT NULL,
    dostava_lng DECIMAL(10,7) DEFAULT NULL,
    dostava_km DECIMAL(8,2) DEFAULT NULL,
    cijena_dostave DECIMAL(8,2) NOT NULL DEFAULT 0,
    ukupna_cijena DECIMAL(8,2) NOT NULL DEFAULT 0,
    vrijeme_narudzbe DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    zeljeno_vrijeme_dostave DATETIME DEFAULT NULL,  -- jQuery UI datepicker
    FOREIGN KEY (kupac_id) REFERENCES korisnici(id),
    FOREIGN KEY (restoran_id) REFERENCES restorani(id),
    FOREIGN KEY (dostavljac_id) REFERENCES korisnici(id)
) ENGINE=InnoDB;

CREATE TABLE stavke_narudzbe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    narudzba_id INT NOT NULL,
    jelo_id INT NOT NULL,
    kolicina INT NOT NULL DEFAULT 1,
    cijena_u_trenutku_narudzbe DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (narudzba_id) REFERENCES narudzbe(id) ON DELETE CASCADE,
    FOREIGN KEY (jelo_id) REFERENCES jela(id)
) ENGINE=InnoDB;

CREATE TABLE kosarica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    korisnik_id INT NOT NULL,
    jelo_id INT NOT NULL,
    kolicina INT NOT NULL DEFAULT 1,
    dodano DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (korisnik_id) REFERENCES korisnici(id) ON DELETE CASCADE,
    FOREIGN KEY (jelo_id) REFERENCES jela(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO uloge (naziv_uloge) VALUES
    ('administrator'), ('restoran'), ('dostavljac'), ('kupac');

INSERT INTO dozvole (naziv_dozvole, opis) VALUES
    ('upravljanje_korisnicima', 'Dodavanje, uređivanje, brisanje i promjena uloge korisnika'),
    ('upravljanje_restoranima', 'Odobravanje i uređivanje restorana'),
    ('upravljanje_vlastitim_restoranom', 'Uređivanje jelovnika i podataka vlastitog restorana'),
    ('azuriranje_pripreme', 'Ažuriranje statusa pripreme narudžbe'),
    ('azuriranje_dostave', 'Preuzimanje i ažuriranje statusa dostave'),
    ('narucivanje', 'Kreiranje narudžbe kao kupac'),
    ('pregled_ponude', 'Pregled restorana i jelovnika (dostupno i gostima)');

INSERT INTO uloga_dozvola (uloga_id, dozvola_id)
SELECT (SELECT id FROM uloge WHERE naziv_uloge = 'administrator'), id FROM dozvole;

INSERT INTO uloga_dozvola (uloga_id, dozvola_id) VALUES
    ((SELECT id FROM uloge WHERE naziv_uloge='restoran'), (SELECT id FROM dozvole WHERE naziv_dozvole='upravljanje_vlastitim_restoranom')),
    ((SELECT id FROM uloge WHERE naziv_uloge='restoran'), (SELECT id FROM dozvole WHERE naziv_dozvole='azuriranje_pripreme')),
    ((SELECT id FROM uloge WHERE naziv_uloge='restoran'), (SELECT id FROM dozvole WHERE naziv_dozvole='pregled_ponude'));

INSERT INTO uloga_dozvola (uloga_id, dozvola_id) VALUES
    ((SELECT id FROM uloge WHERE naziv_uloge='dostavljac'), (SELECT id FROM dozvole WHERE naziv_dozvole='azuriranje_dostave'));

INSERT INTO uloga_dozvola (uloga_id, dozvola_id) VALUES
    ((SELECT id FROM uloge WHERE naziv_uloge='kupac'), (SELECT id FROM dozvole WHERE naziv_dozvole='narucivanje')),
    ((SELECT id FROM uloge WHERE naziv_uloge='kupac'), (SELECT id FROM dozvole WHERE naziv_dozvole='pregled_ponude'));

INSERT INTO kategorije (naziv) VALUES
    ('Burgeri'), ('Pizze'), ('Tortilje'), ('Piletina'), ('Gluten free'),
    ('Slatko'), ('Palačinke'), ('Roštilj'), ('Kolači');

