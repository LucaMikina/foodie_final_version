
SET NAMES utf8mb4;

INSERT IGNORE INTO korisnik_uloga (korisnik_id, uloga_id)
SELECT ku.korisnik_id, kupac.id
FROM korisnik_uloga ku
JOIN uloge kuhar ON kuhar.id = ku.uloga_id AND kuhar.naziv_uloge = 'kuhar'
JOIN uloge kupac ON kupac.naziv_uloge = 'kupac'
WHERE NOT EXISTS (
    SELECT 1
    FROM korisnik_uloga ostalo
    WHERE ostalo.korisnik_id = ku.korisnik_id
      AND ostalo.uloga_id <> ku.uloga_id
);

INSERT IGNORE INTO uloga_dozvola (uloga_id, dozvola_id)
SELECT r.id, d.id
FROM uloge r
JOIN dozvole d ON d.naziv_dozvole = 'azuriranje_pripreme'
WHERE r.naziv_uloge = 'restoran';

DELETE ku
FROM korisnik_uloga ku
JOIN uloge u ON u.id = ku.uloga_id
WHERE u.naziv_uloge = 'kuhar';

DELETE ud
FROM uloga_dozvola ud
JOIN uloge u ON u.id = ud.uloga_id
WHERE u.naziv_uloge = 'kuhar';

DELETE FROM uloge WHERE naziv_uloge = 'kuhar';

ALTER TABLE uloge
    MODIFY naziv_uloge ENUM('administrator','restoran','dostavljac','kupac') NOT NULL;

