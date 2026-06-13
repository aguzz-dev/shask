<?php

namespace App\Models;

use App\Database;

class MediaCatalog extends Database
{
    /**
     * Devuelve packs + imágenes del catálogo.
     * Si la tabla media_images está vacía, cae al escaneo del directorio
     * public/images para no romper antes de correr el seeder.
     */
    public function getCatalog()
    {
        $count = $this->query("SELECT COUNT(*) as c FROM media_images")
            ->fetch_assoc()['c'] ?? 0;

        if ((int) $count === 0) {
            return [
                'packs'  => [],
                'images' => $this->scanDirectory(),
            ];
        }

        $packs = $this->query(
            "SELECT id, name, slug, is_premium, price, cover, sort
             FROM sticker_packs ORDER BY sort ASC, id ASC"
        )->fetch_all(MYSQLI_ASSOC);

        $images = $this->query(
            "SELECT mi.id, mi.name, mi.type, mi.category, mi.tags,
                    mi.pack_id, mi.sort,
                    sp.name AS pack_name, sp.is_premium AS pack_is_premium
             FROM media_images mi
             LEFT JOIN sticker_packs sp ON mi.pack_id = sp.id
             ORDER BY mi.sort ASC, mi.id ASC"
        )->fetch_all(MYSQLI_ASSOC);

        return [
            'packs'  => $packs,
            'images' => $images,
        ];
    }

    /**
     * Fallback: escanea public/images y devuelve los nombres con tipo heurístico.
     */
    private function scanDirectory()
    {
        $directory = public_path('images');
        if (!is_dir($directory)) {
            return [];
        }

        $files  = array_diff(scandir($directory), ['.', '..']);
        $images = [];
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
                continue;
            }
            $name  = pathinfo($file, PATHINFO_FILENAME);
            $lower = strtolower($name);
            $isBg  = str_contains($lower, 'background')
                || str_contains($lower, 'bg_')
                || str_contains($lower, 'texture')
                || str_contains($lower, 'pattern');

            $images[] = [
                'name'            => $name,
                'type'            => $isBg ? 'background' : 'sticker',
                'category'        => null,
                'tags'            => null,
                'pack_id'         => null,
                'pack_name'       => null,
                'pack_is_premium' => 0,
            ];
        }
        usort($images, fn ($a, $b) => strcmp($a['name'], $b['name']));
        return $images;
    }

    // ============ Escritura / consultas del back office ============

    /** Lista para el admin con búsqueda por nombre/tag y filtro por tipo. */
    public function searchImages(?string $q, ?string $type): array
    {
        $where = ['1=1'];
        if ($q !== null && $q !== '') {
            $q = $this->dbConnection->real_escape_string($q);
            $where[] = "(mi.name LIKE '%{$q}%' OR mi.tags LIKE '%{$q}%' OR mi.category LIKE '%{$q}%')";
        }
        if (in_array($type, ['sticker', 'background'], true)) {
            $where[] = "mi.type = '{$type}'";
        }
        $whereSql = implode(' AND ', $where);
        return $this->query(
            "SELECT mi.*, sp.name AS pack_name, sp.is_premium AS pack_is_premium
             FROM media_images mi
             LEFT JOIN sticker_packs sp ON mi.pack_id = sp.id
             WHERE {$whereSql}
             ORDER BY mi.id DESC"
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function findImageById(int $id): ?array
    {
        $rows = $this->query("SELECT * FROM media_images WHERE id = {$id}")
            ->fetch_all(MYSQLI_ASSOC);
        return $rows[0] ?? null;
    }

    public function imageNameExists(string $name): bool
    {
        $name = $this->dbConnection->real_escape_string($name);
        $row = $this->query(
            "SELECT COUNT(*) AS c FROM media_images WHERE name = '{$name}'"
        )->fetch_assoc();
        return (int) $row['c'] > 0;
    }

    public function insertImage(string $name, string $type, ?string $category, array $tags, ?int $packId): int
    {
        $name = $this->dbConnection->real_escape_string($name);
        $type = $type === 'background' ? 'background' : 'sticker';
        $categorySql = $category !== null && $category !== ''
            ? "'" . $this->dbConnection->real_escape_string($category) . "'" : 'NULL';
        $tagsSql = "'" . $this->dbConnection->real_escape_string(json_encode(array_values($tags), JSON_UNESCAPED_UNICODE)) . "'";
        $packSql = $packId !== null ? (int) $packId : 'NULL';
        $this->query(
            "INSERT INTO media_images (name, type, category, tags, pack_id, created_at, updated_at)
             VALUES ('{$name}', '{$type}', {$categorySql}, {$tagsSql}, {$packSql}, NOW(), NOW())"
        );
        return (int) $this->dbConnection->insert_id;
    }

    public function updateImage(int $id, string $type, ?string $category, array $tags, ?int $packId, int $sort): void
    {
        $type = $type === 'background' ? 'background' : 'sticker';
        $categorySql = $category !== null && $category !== ''
            ? "'" . $this->dbConnection->real_escape_string($category) . "'" : 'NULL';
        $tagsSql = "'" . $this->dbConnection->real_escape_string(json_encode(array_values($tags), JSON_UNESCAPED_UNICODE)) . "'";
        $packSql = $packId !== null ? (int) $packId : 'NULL';
        $this->query(
            "UPDATE media_images
             SET type = '{$type}', category = {$categorySql}, tags = {$tagsSql},
                 pack_id = {$packSql}, sort = {$sort}, updated_at = NOW()
             WHERE id = {$id}"
        );
    }

    public function deleteImage(int $id): void
    {
        $this->query("DELETE FROM media_images WHERE id = {$id}");
    }

    // ---------------- Packs ----------------

    public function allPacks(): array
    {
        return $this->query(
            "SELECT * FROM sticker_packs ORDER BY sort ASC, id ASC"
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function findPackById(int $id): ?array
    {
        $rows = $this->query("SELECT * FROM sticker_packs WHERE id = {$id}")
            ->fetch_all(MYSQLI_ASSOC);
        return $rows[0] ?? null;
    }

    public function insertPack(string $name, string $slug, bool $isPremium, int $price, ?string $cover, int $sort): int
    {
        $name = $this->dbConnection->real_escape_string($name);
        $slug = $this->dbConnection->real_escape_string($slug);
        $premium = $isPremium ? 1 : 0;
        $coverSql = $cover !== null && $cover !== ''
            ? "'" . $this->dbConnection->real_escape_string($cover) . "'" : 'NULL';
        $this->query(
            "INSERT INTO sticker_packs (name, slug, is_premium, price, cover, sort, created_at, updated_at)
             VALUES ('{$name}', '{$slug}', {$premium}, {$price}, {$coverSql}, {$sort}, NOW(), NOW())"
        );
        return (int) $this->dbConnection->insert_id;
    }

    public function updatePack(int $id, string $name, bool $isPremium, int $price, ?string $cover, int $sort): void
    {
        $name = $this->dbConnection->real_escape_string($name);
        $premium = $isPremium ? 1 : 0;
        $coverSql = $cover !== null && $cover !== ''
            ? "'" . $this->dbConnection->real_escape_string($cover) . "'" : 'NULL';
        $this->query(
            "UPDATE sticker_packs
             SET name = '{$name}', is_premium = {$premium}, price = {$price},
                 cover = {$coverSql}, sort = {$sort}, updated_at = NOW()
             WHERE id = {$id}"
        );
    }

    /** Borra el pack; sus imágenes quedan sin pack (no se borran). */
    public function deletePack(int $id): void
    {
        $this->query("UPDATE media_images SET pack_id = NULL WHERE pack_id = {$id}");
        $this->query("DELETE FROM sticker_packs WHERE id = {$id}");
    }

    /** Limpia la portada de los packs que usaban una imagen borrada. */
    public function clearCoverReferences(string $imageName): void
    {
        $imageName = $this->dbConnection->real_escape_string($imageName);
        $this->query("UPDATE sticker_packs SET cover = NULL WHERE cover = '{$imageName}'");
    }
}
