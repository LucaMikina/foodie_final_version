<?php
require_once __DIR__ . '/Model.php';

class Dish extends Model
{
    protected string $table = 'jela';

    public function findByRestaurant(int $restoranId): array
    {
        return $this->all(['restoran_id' => $restoranId, 'dostupno' => 1]);
    }

    public function removeSafely(int $id): string
    {
        $this->db->beginTransaction();

        try {

            $stmt = $this->db->prepare('DELETE FROM kosarica WHERE jelo_id = :id');
            $stmt->execute([':id' => $id]);

            $stmt = $this->db->prepare('SELECT COUNT(*) FROM stavke_narudzbe WHERE jelo_id = :id');
            $stmt->execute([':id' => $id]);
            $koristenoUNarudzbi = (int) $stmt->fetchColumn() > 0;

            if ($koristenoUNarudzbi) {
                $stmt = $this->db->prepare('UPDATE jela SET dostupno = 0 WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $rezultat = 'archived';
            } else {
                $stmt = $this->db->prepare('DELETE FROM jela WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $rezultat = 'deleted';
            }

            $this->db->commit();
            return $rezultat;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function filter(array $filteri): array
    {
        $sql = "SELECT j.*, r.naziv AS restoran_naziv, k.naziv AS kategorija_naziv
                FROM jela j
                JOIN restorani r ON r.id = j.restoran_id
                JOIN kategorije k ON k.id = j.kategorija_id
                WHERE j.dostupno = 1";
        $params = [];

        if (!empty($filteri['kategorija_id'])) {
            $sql .= " AND j.kategorija_id = :kategorija_id";
            $params[':kategorija_id'] = $filteri['kategorija_id'];
        }
        if (!empty($filteri['restoran_id'])) {
            $sql .= " AND j.restoran_id = :restoran_id";
            $params[':restoran_id'] = $filteri['restoran_id'];
        }
        if (!empty($filteri['cijena_min'])) {
            $sql .= " AND j.cijena >= :cijena_min";
            $params[':cijena_min'] = $filteri['cijena_min'];
        }
        if (!empty($filteri['cijena_max'])) {
            $sql .= " AND j.cijena <= :cijena_max";
            $params[':cijena_max'] = $filteri['cijena_max'];
        }
        if (!empty($filteri['pojam'])) {
            $sql .= " AND j.naziv LIKE :pojam";
            $params[':pojam'] = '%' . $filteri['pojam'] . '%';
        }

        $sql .= " ORDER BY j.naziv ASC";
        return $this->query($sql, $params);
    }
}
