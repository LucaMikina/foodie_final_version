<?php
require_once __DIR__ . '/Model.php';

class Cart extends Model
{
    protected string $table = 'kosarica';

    public function getForUser(int $korisnikId): array
    {
        return $this->query(
            "SELECT k.id, k.kolicina,
                    j.id AS jelo_id, j.naziv, j.cijena, j.slika, j.restoran_id,
                    r.naziv AS restoran_naziv
             FROM kosarica k
             JOIN jela j ON j.id = k.jelo_id
             JOIN restorani r ON r.id = j.restoran_id
             WHERE k.korisnik_id = :korisnik_id
             ORDER BY k.dodano ASC, k.id ASC",
            [':korisnik_id' => $korisnikId]
        );
    }

    public function addItem(int $korisnikId, int $jeloId, int $kolicina = 1): void
    {
        $existing = $this->query(
            "SELECT id, kolicina FROM kosarica WHERE korisnik_id = :k AND jelo_id = :j",
            [':k' => $korisnikId, ':j' => $jeloId]
        );

        if ($existing) {
            $this->update((int) $existing[0]['id'], ['kolicina' => (int) $existing[0]['kolicina'] + $kolicina]);
        } else {
            $this->create(['korisnik_id' => $korisnikId, 'jelo_id' => $jeloId, 'kolicina' => $kolicina]);
        }
    }

    public function canAddDish(int $korisnikId, int $jeloId): bool
    {
        $stmt = $this->db->prepare("SELECT restoran_id FROM jela WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $jeloId]);
        $dishRestaurant = $stmt->fetchColumn();
        if ($dishRestaurant === false) {
            return false;
        }

        $stmt = $this->db->prepare(
            "SELECT j.restoran_id
             FROM kosarica k
             JOIN jela j ON j.id = k.jelo_id
             WHERE k.korisnik_id = :korisnik_id
             LIMIT 1"
        );
        $stmt->execute([':korisnik_id' => $korisnikId]);
        $cartRestaurant = $stmt->fetchColumn();

        return $cartRestaurant === false || (int) $cartRestaurant === (int) $dishRestaurant;
    }

    public function updateQuantityForUser(int $korisnikId, int $cartId, int $kolicina): bool
    {
        $kolicina = max(1, min(99, $kolicina));
        $stmt = $this->db->prepare(
            "UPDATE kosarica
             SET kolicina = :kolicina
             WHERE id = :id AND korisnik_id = :korisnik_id"
        );
        $stmt->execute([
            ':kolicina' => $kolicina,
            ':id' => $cartId,
            ':korisnik_id' => $korisnikId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function removeItemForUser(int $korisnikId, int $cartId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM kosarica WHERE id = :id AND korisnik_id = :korisnik_id"
        );
        $stmt->execute([':id' => $cartId, ':korisnik_id' => $korisnikId]);
        return $stmt->rowCount() > 0;
    }

    public function clearForUser(int $korisnikId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM kosarica WHERE korisnik_id = :id");
        return $stmt->execute([':id' => $korisnikId]);
    }
}
