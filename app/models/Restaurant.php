<?php
require_once __DIR__ . '/Model.php';

class Restaurant extends Model
{
    protected string $table = 'restorani';

    public function search(string $pojam = ''): array
    {
        $sql = "SELECT * FROM restorani WHERE status = 'aktivan'";
        $params = [];

        if ($pojam !== '') {
            $sql .= " AND naziv LIKE :pojam";
            $params[':pojam'] = '%' . $pojam . '%';
        }

        $sql .= " ORDER BY naziv ASC";
        return $this->query($sql, $params);
    }

    public function findAssignedToOwner(int $vlasnikId): ?array
    {
        $rows = $this->query(
            "SELECT * FROM restorani
             WHERE vlasnik_id = :vlasnik_id
             ORDER BY status = 'aktivan' DESC, id ASC
             LIMIT 1",
            [':vlasnik_id' => $vlasnikId]
        );
        return $rows[0] ?? null;
    }

    public function findAssignedToOwnerWithProfileStats(int $vlasnikId): ?array
    {
        $rows = $this->profileStatsQuery(
            'WHERE r.vlasnik_id = :vlasnik_id',
            [':vlasnik_id' => $vlasnikId],
            1
        );
        return $rows[0] ?? null;
    }

    public function ownerHasRestaurant(int $vlasnikId, ?int $excludeRestaurantId = null): bool
    {
        $sql = "SELECT id FROM restorani WHERE vlasnik_id = :vlasnik_id";
        $params = [':vlasnik_id' => $vlasnikId];
        if ($excludeRestaurantId !== null && $excludeRestaurantId > 0) {
            $sql .= " AND id <> :exclude_id";
            $params[':exclude_id'] = $excludeRestaurantId;
        }
        $sql .= " LIMIT 1";
        return !empty($this->query($sql, $params));
    }

    public function findByOwner(int $vlasnikId): array
    {
        return $this->all(['vlasnik_id' => $vlasnikId], 'datum_kreiranja DESC');
    }

    public function findByOwnerWithProfileStats(int $vlasnikId): array
    {
        return $this->profileStatsQuery('WHERE r.vlasnik_id = :vlasnik_id', [':vlasnik_id' => $vlasnikId]);
    }

    public function allWithProfileStats(): array
    {
        return $this->profileStatsQuery('', []);
    }

    private function profileStatsQuery(string $where, array $params, ?int $limit = null): array
    {
        return $this->query(
            "SELECT r.*,
                    k.ime AS vlasnik_ime,
                    k.prezime AS vlasnik_prezime,
                    k.email AS vlasnik_email,
                    (SELECT COUNT(*) FROM jela j WHERE j.restoran_id = r.id) AS broj_jela,
                    (SELECT COUNT(*) FROM narudzbe n WHERE n.restoran_id = r.id) AS broj_narudzbi,
                    (SELECT COUNT(*) FROM narudzbe n WHERE n.restoran_id = r.id AND n.status = 'primljena') AS nove_narudzbe,
                    (SELECT COUNT(*) FROM narudzbe n WHERE n.restoran_id = r.id AND n.status IN ('prihvacena','priprema','na_dostavi')) AS aktivne_narudzbe
             FROM restorani r
             JOIN korisnici k ON k.id = r.vlasnik_id
             {$where}
             ORDER BY r.status = 'aktivan' DESC, r.datum_kreiranja DESC" . ($limit ? " LIMIT " . (int) $limit : ""),
            $params
        );
    }

    public function filterByCategory(int $kategorijaId, string $pojam = ''): array
    {
        $sql = "SELECT DISTINCT r.*
                FROM restorani r
                JOIN jela j ON j.restoran_id = r.id
                WHERE r.status = 'aktivan' AND j.kategorija_id = :kategorija_id AND j.dostupno = 1";
        $params = [':kategorija_id' => $kategorijaId];

        if ($pojam !== '') {
            $sql .= " AND r.naziv LIKE :pojam";
            $params[':pojam'] = '%' . $pojam . '%';
        }

        $sql .= " ORDER BY r.naziv ASC";
        return $this->query($sql, $params);
    }
}
