<?php
require_once __DIR__ . '/Model.php';

class Order extends Model
{
    protected string $table = 'narudzbe';

    public function createWithItems(array $orderData, array $stavke): int
    {
        $this->db->beginTransaction();
        try {
            $deliveryFee = (float) ($orderData['_delivery_fee'] ?? $orderData['cijena_dostave'] ?? 0);
            unset($orderData['_delivery_fee']);
            $ukupno = array_reduce($stavke, fn($sum, $s) => $sum + ($s['cijena'] * $s['kolicina']), 0);
            $ukupno += $deliveryFee;
            $orderData['ukupna_cijena'] = $ukupno;

            $orderId = $this->create($orderData);

            $stmt = $this->db->prepare(
                "INSERT INTO stavke_narudzbe (narudzba_id, jelo_id, kolicina, cijena_u_trenutku_narudzbe)
                 VALUES (:narudzba_id, :jelo_id, :kolicina, :cijena)"
            );
            foreach ($stavke as $s) {
                $stmt->execute([
                    ':narudzba_id' => $orderId,
                    ':jelo_id'     => $s['jelo_id'],
                    ':kolicina'    => $s['kolicina'],
                    ':cijena'      => $s['cijena'],
                ]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function supportsDeliveryCoordinates(): bool
    {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM narudzbe LIKE 'dostava_lat'");
            $hasLat = (bool) $stmt->fetch();
            $stmt = $this->db->query("SHOW COLUMNS FROM narudzbe LIKE 'dostava_lng'");
            $hasLng = (bool) $stmt->fetch();
            return $hasLat && $hasLng;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function supportsDeliveryPricing(): bool
    {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM narudzbe LIKE 'cijena_dostave'");
            $hasFee = (bool) $stmt->fetch();
            $stmt = $this->db->query("SHOW COLUMNS FROM narudzbe LIKE 'dostava_km'");
            $hasKm = (bool) $stmt->fetch();
            return $hasFee && $hasKm;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getItems(int $orderId): array
    {
        return $this->query(
            "SELECT sn.*, j.naziv AS jelo_naziv
             FROM stavke_narudzbe sn
             JOIN jela j ON j.id = sn.jelo_id
             WHERE sn.narudzba_id = :id",
            [':id' => $orderId]
        );
    }

    public function findByCustomer(int $kupacId): array
    {
        return $this->all(['kupac_id' => $kupacId], 'vrijeme_narudzbe DESC');
    }

    public function findByDeliveryPerson(int $dostavljacId): array
    {
        return $this->all(['dostavljac_id' => $dostavljacId], 'vrijeme_narudzbe DESC');
    }

    public function findActiveByDeliveryPerson(int $dostavljacId): array
    {
        return $this->query(
            "SELECT n.*, r.naziv AS restoran_naziv, r.adresa AS restoran_adresa,
                    k.ime AS kupac_ime, k.prezime AS kupac_prezime
             FROM narudzbe n
             JOIN restorani r ON r.id = n.restoran_id
             JOIN korisnici k ON k.id = n.kupac_id
             WHERE n.dostavljac_id = :dostavljac_id
               AND n.status NOT IN ('dostavljena', 'otkazana')
             ORDER BY n.vrijeme_narudzbe ASC",
            [':dostavljac_id' => $dostavljacId]
        );
    }

    public function findByRestaurant(int $restoranId): array
    {
        return $this->all(['restoran_id' => $restoranId], 'vrijeme_narudzbe DESC');
    }

    public function findByRestaurantWithDetails(int $restoranId): array
    {
        return $this->query(
            "SELECT n.*,
                    k.ime AS kupac_ime, k.prezime AS kupac_prezime, k.telefon AS kupac_telefon,
                    d.ime AS dostavljac_ime, d.prezime AS dostavljac_prezime,
                    (SELECT COALESCE(SUM(sn.kolicina), 0) FROM stavke_narudzbe sn WHERE sn.narudzba_id = n.id) AS broj_stavki
             FROM narudzbe n
             JOIN korisnici k ON k.id = n.kupac_id
             LEFT JOIN korisnici d ON d.id = n.dostavljac_id
             WHERE n.restoran_id = :restoran_id
             ORDER BY FIELD(n.status, 'primljena', 'prihvacena', 'priprema', 'na_dostavi', 'dostavljena', 'otkazana'),
                      n.vrijeme_narudzbe DESC",
            [':restoran_id' => $restoranId]
        );
    }

    public function getItemsForOrders(array $orderIds): array
    {
        $orderIds = array_values(array_unique(array_filter(array_map('intval', $orderIds), fn($id) => $id > 0)));
        if (empty($orderIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT sn.narudzba_id, sn.jelo_id, sn.kolicina, sn.cijena_u_trenutku_narudzbe,
                    j.naziv AS jelo_naziv, j.slika AS jelo_slika
             FROM stavke_narudzbe sn
             JOIN jela j ON j.id = sn.jelo_id
             WHERE sn.narudzba_id IN ({$placeholders})
             ORDER BY sn.id ASC"
        );
        $stmt->execute($orderIds);

        $grupirano = [];
        foreach ($stmt->fetchAll() as $stavka) {
            $grupirano[(int) $stavka['narudzba_id']][] = $stavka;
        }
        return $grupirano;
    }

    public function updateStatusIfCurrent(int $orderId, string $trenutniStatus, string $noviStatus): bool
    {
        $dozvoljeni = ['primljena', 'prihvacena', 'priprema', 'na_dostavi', 'dostavljena', 'otkazana'];
        if (!in_array($trenutniStatus, $dozvoljeni, true) || !in_array($noviStatus, $dozvoljeni, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE narudzbe SET status = :novi_status
             WHERE id = :id AND status = :trenutni_status"
        );
        $stmt->execute([
            ':novi_status' => $noviStatus,
            ':id' => $orderId,
            ':trenutni_status' => $trenutniStatus,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function findDeliveryBoard(): array
    {
        return $this->query(
            "SELECT n.*, r.naziv AS restoran_naziv, r.adresa AS restoran_adresa,
                    k.ime AS kupac_ime, k.prezime AS kupac_prezime,
                    d.ime AS dostavljac_ime, d.prezime AS dostavljac_prezime,
                    CASE
                        WHEN n.dostavljac_id IS NULL THEN 'slobodno'
                        ELSE 'zauzeto'
                    END AS dostava_stanje
             FROM narudzbe n
             JOIN restorani r ON r.id = n.restoran_id
             JOIN korisnici k ON k.id = n.kupac_id
             LEFT JOIN korisnici d ON d.id = n.dostavljac_id
             WHERE n.status NOT IN ('dostavljena', 'otkazana')
             ORDER BY FIELD(n.status, 'priprema', 'prihvacena', 'primljena', 'na_dostavi'),
                      n.vrijeme_narudzbe ASC"
        );
    }

    public function findAvailableForPickup(): array
    {
        return $this->query(
            "SELECT n.*, r.naziv AS restoran_naziv, r.adresa AS restoran_adresa
             FROM narudzbe n
             JOIN restorani r ON r.id = n.restoran_id
             WHERE n.status = 'priprema' AND n.dostavljac_id IS NULL
             ORDER BY n.vrijeme_narudzbe ASC"
        );
    }

    public function acceptDelivery(int $orderId, int $dostavljacId): bool
    {
        $sql = "UPDATE narudzbe
                SET dostavljac_id = :dostavljac_id
                WHERE id = :id
                  AND dostavljac_id IS NULL
                  AND status = 'priprema'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':dostavljac_id' => $dostavljacId, ':id' => $orderId]);
        return $stmt->rowCount() > 0;
    }

    public function assignDelivery(int $orderId, int $dostavljacId): bool
    {
        $sql = "UPDATE narudzbe
                SET dostavljac_id = :dostavljac_id
                WHERE id = :id
                  AND dostavljac_id IS NULL
                  AND status IN ('primljena', 'prihvacena', 'priprema')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':dostavljac_id' => $dostavljacId, ':id' => $orderId]);
        return $stmt->rowCount() > 0;
    }

    public function allWithDetails(): array
    {
        return $this->query(
            "SELECT n.*, r.naziv AS restoran_naziv,
                    k.ime AS kupac_ime, k.prezime AS kupac_prezime,
                    d.ime AS dostavljac_ime, d.prezime AS dostavljac_prezime,
                    CASE
                        WHEN n.status IN ('dostavljena', 'otkazana') THEN 'zatvoreno'
                        WHEN n.dostavljac_id IS NULL THEN 'slobodno'
                        ELSE 'zauzeto'
                    END AS dostava_stanje
             FROM narudzbe n
             JOIN restorani r ON r.id = n.restoran_id
             JOIN korisnici k ON k.id = n.kupac_id
             LEFT JOIN korisnici d ON d.id = n.dostavljac_id
             ORDER BY n.vrijeme_narudzbe DESC"
        );
    }

    public function updateStatus(int $orderId, string $noviStatus): bool
    {
        $dozvoljeni = ['primljena', 'prihvacena', 'priprema', 'na_dostavi', 'dostavljena', 'otkazana'];
        if (!in_array($noviStatus, $dozvoljeni, true)) {
            return false;
        }
        return $this->update($orderId, ['status' => $noviStatus]);
    }
}
