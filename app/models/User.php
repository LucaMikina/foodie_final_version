<?php
require_once __DIR__ . '/Model.php';

class User extends Model
{
    protected string $table = 'korisnici';

    public function register(array $data, string $nazivUloge = 'kupac'): int
    {
        $data['lozinka'] = password_hash($data['lozinka'], PASSWORD_DEFAULT);
        $userId = $this->create($data);

        $stmt = $this->db->prepare(
            "INSERT INTO korisnik_uloga (korisnik_id, uloga_id)
             SELECT :korisnik_id, id FROM uloge WHERE naziv_uloge = :naziv_uloge"
        );
        $stmt->execute([':korisnik_id' => $userId, ':naziv_uloge' => $nazivUloge]);

        return $userId;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM korisnici WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function getRoles(int $userId): array
    {
        return $this->query(
            "SELECT ul.naziv_uloge
             FROM korisnik_uloga ku
             JOIN uloge ul ON ul.id = ku.uloga_id
             WHERE ku.korisnik_id = :id",
            [':id' => $userId]
        );
    }

    public function hasPermission(int $userId, string $nazivDozvole): bool
    {
        $result = $this->query(
            "SELECT 1
             FROM korisnik_uloga ku
             JOIN uloga_dozvola ud ON ud.uloga_id = ku.uloga_id
             JOIN dozvole d ON d.id = ud.dozvola_id
             WHERE ku.korisnik_id = :id AND d.naziv_dozvole = :dozvola
             LIMIT 1",
            [':id' => $userId, ':dozvola' => $nazivDozvole]
        );
        return count($result) > 0;
    }

    public function getAllRoles(): array
    {
        return $this->query("SELECT id, naziv_uloge FROM uloge WHERE naziv_uloge IN ('administrator','restoran','dostavljac','kupac') ORDER BY id ASC");
    }

    public function setRoles(int $userId, array $uloge): bool
    {
        $uloge = array_values(array_unique(array_filter(array_map(
            static fn($uloga) => trim((string) $uloga),
            $uloge
        ))));

        if (empty($uloge)) {
            throw new InvalidArgumentException('Korisnik mora imati barem jednu ulogu.');
        }

        $dostupneUloge = array_column($this->getAllRoles(), 'naziv_uloge');
        $nepoznate = array_diff($uloge, $dostupneUloge);
        if (!empty($nepoznate)) {
            throw new InvalidArgumentException('Odabrana je nepoznata uloga.');
        }

        try {
            $this->db->beginTransaction();

            $this->db->prepare("DELETE FROM korisnik_uloga WHERE korisnik_id = :id")
                     ->execute([':id' => $userId]);

            $stmt = $this->db->prepare(
                "INSERT INTO korisnik_uloga (korisnik_id, uloga_id)
                 SELECT :korisnik_id, id FROM uloge WHERE naziv_uloge = :naziv_uloge"
            );

            foreach ($uloge as $uloga) {
                $stmt->execute([
                    ':korisnik_id' => $userId,
                    ':naziv_uloge' => $uloga,
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function hasRole(int $userId, string $nazivUloge): bool
    {
        $rows = $this->query(
            "SELECT 1
             FROM korisnik_uloga ku
             JOIN uloge ul ON ul.id = ku.uloga_id
             WHERE ku.korisnik_id = :id AND ul.naziv_uloge = :uloga
             LIMIT 1",
            [':id' => $userId, ':uloga' => $nazivUloge]
        );
        return !empty($rows);
    }

    public function findAvailableRestaurantOwners(?int $currentRestaurantId = null): array
    {
        $sql = "SELECT DISTINCT k.id, k.ime, k.prezime, k.email
                FROM korisnici k
                JOIN korisnik_uloga ku ON ku.korisnik_id = k.id
                JOIN uloge ul ON ul.id = ku.uloga_id
                WHERE ul.naziv_uloge = 'restoran'
                  AND k.status = 'aktivan'
                  AND NOT EXISTS (
                      SELECT 1 FROM restorani r
                      WHERE r.vlasnik_id = k.id";
        $params = [];

        if ($currentRestaurantId !== null && $currentRestaurantId > 0) {
            $sql .= " AND r.id <> :current_restaurant_id";
            $params[':current_restaurant_id'] = $currentRestaurantId;
        }

        $sql .= ") ORDER BY k.prezime, k.ime";
        return $this->query($sql, $params);
    }

    public function changeRole(int $userId, string $novaUloga): bool
    {
        return $this->setRoles($userId, [$novaUloga]);
    }

    public function findByRole(string $nazivUloge): array
    {
        return $this->query(
            "SELECT k.id, k.ime, k.prezime, k.email
             FROM korisnici k
             JOIN korisnik_uloga ku ON ku.korisnik_id = k.id
             JOIN uloge ul ON ul.id = ku.uloga_id
             WHERE ul.naziv_uloge = :uloga AND k.status = 'aktivan'
             ORDER BY k.prezime, k.ime",
            [':uloga' => $nazivUloge]
        );
    }

    public function search(string $pojam = '', string $uloga = ''): array
    {
        $sql = "SELECT DISTINCT k.*
                FROM korisnici k
                LEFT JOIN korisnik_uloga ku ON ku.korisnik_id = k.id
                LEFT JOIN uloge ul ON ul.id = ku.uloga_id
                WHERE 1=1";
        $params = [];

        if ($pojam !== '') {
            $sql .= " AND (k.ime LIKE :pojam1 OR k.prezime LIKE :pojam2 OR k.email LIKE :pojam3)";
            $vrijednostPojam = '%' . $pojam . '%';
            $params[':pojam1'] = $vrijednostPojam;
            $params[':pojam2'] = $vrijednostPojam;
            $params[':pojam3'] = $vrijednostPojam;
        }

        if ($uloga !== '') {
            $sql .= " AND ul.naziv_uloge = :uloga";
            $params[':uloga'] = $uloga;
        }

        $sql .= " ORDER BY k.prezime ASC, k.ime ASC";

        return $this->query($sql, $params);
    }
}
