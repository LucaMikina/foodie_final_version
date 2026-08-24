
USE foodie;

START TRANSACTION;

DELETE FROM stavke_narudzbe;
DELETE FROM narudzbe;
DELETE FROM kosarica;

DELETE FROM jela;
DELETE FROM restorani;

DELETE FROM korisnik_uloga;

DELETE FROM korisnici
WHERE id <> 1;

UPDATE korisnici
SET status = 'aktivan'
WHERE id = 1;

INSERT INTO korisnik_uloga (korisnik_id, uloga_id)
SELECT 1, id
FROM uloge
WHERE naziv_uloge = 'administrator';

COMMIT;

ALTER TABLE korisnici AUTO_INCREMENT = 2;
ALTER TABLE restorani AUTO_INCREMENT = 1;
ALTER TABLE jela AUTO_INCREMENT = 1;
ALTER TABLE narudzbe AUTO_INCREMENT = 1;
ALTER TABLE stavke_narudzbe AUTO_INCREMENT = 1;
ALTER TABLE kosarica AUTO_INCREMENT = 1;

SELECT k.id, k.ime, k.prezime, k.email, k.status, u.naziv_uloge
FROM korisnici k
LEFT JOIN korisnik_uloga ku ON ku.korisnik_id = k.id
LEFT JOIN uloge u ON u.id = ku.uloga_id
ORDER BY k.id;

SELECT 'restorani' AS tablica, COUNT(*) AS broj_redaka FROM restorani
UNION ALL SELECT 'jela', COUNT(*) FROM jela
UNION ALL SELECT 'narudzbe', COUNT(*) FROM narudzbe
UNION ALL SELECT 'stavke_narudzbe', COUNT(*) FROM stavke_narudzbe
UNION ALL SELECT 'kosarica', COUNT(*) FROM kosarica;
