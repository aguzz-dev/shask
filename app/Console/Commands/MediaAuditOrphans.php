<?php

namespace App\Console\Commands;

use App\Database;
use App\Models\Asset;
use Illuminate\Console\Command;

/**
 * Read-only audit for the sticker catalog gate (see Asset::findMissingStickerRefs
 * and Asset::collectStickerSrcs).
 *
 * Forward mode (default): scans EVERY row in `public_assets` (no status
 * filter — this is a full audit, not a moderation queue) and reports any
 * canvas sticker `src` that has no matching row in `media_images`. This is
 * the exact class of bug found in production on the imported "Y2K" design
 * (submitter_user_id=2): its canvas references `y2k_sticker_1` /
 * `y2k_sticker_2`, both uncatalogued — invisible/uneditable from the admin
 * panel, even though the files exist on the `media` disk and render fine
 * in the app.
 *
 * Reverse mode (`--reverse`): the opposite direction — catalogued
 * `media_images` rows that are never referenced by any `public_assets`
 * canvas (dead catalog entries).
 *
 * This command NEVER writes, deletes, or "fixes" anything. It only reports.
 */
class MediaAuditOrphans extends Command
{
    protected $signature = 'media:audit-orphans {--reverse : List cataloged media_images never referenced by any design instead}';

    protected $description = 'Read-only audit: reports public_assets canvas stickers missing from media_images (or, with --reverse, cataloged images never referenced by any design)';

    public function handle(): int
    {
        return $this->option('reverse') ? $this->auditReverse() : $this->auditForward();
    }

    private function auditForward(): int
    {
        $db    = new Database;
        $asset = new Asset;

        $rows = $db->query('SELECT id, title, canvas FROM public_assets')->fetch_all(MYSQLI_ASSOC);

        $problemCount    = 0;
        $uniqueMissing   = [];

        foreach ($rows as $row) {
            $canvas = $row['canvas'] !== null ? json_decode((string) $row['canvas'], true) : null;
            if (!is_array($canvas)) {
                continue;
            }

            $missing = $asset->findMissingStickerRefs($canvas);
            if (empty($missing)) {
                continue;
            }

            $problemCount++;
            foreach ($missing as $src) {
                $uniqueMissing[$src] = true;
            }

            $this->warn(sprintf(
                "asset #%d '%s': stickers no catalogados: %s",
                (int) $row['id'],
                (string) $row['title'],
                implode(', ', $missing)
            ));
        }

        $this->info(sprintf(
            'auditados: %d, con problemas: %d, srcs faltantes únicos: %d',
            count($rows),
            $problemCount,
            count($uniqueMissing)
        ));

        return self::SUCCESS;
    }

    private function auditReverse(): int
    {
        $db = new Database;

        $rows = $db->query('SELECT canvas FROM public_assets')->fetch_all(MYSQLI_ASSOC);

        $referenced = [];
        foreach ($rows as $row) {
            $canvas = $row['canvas'] !== null ? json_decode((string) $row['canvas'], true) : null;
            if (!is_array($canvas)) {
                continue;
            }
            foreach (Asset::collectStickerSrcs($canvas) as $src) {
                $referenced[$src] = true;
            }
        }

        $cataloged = $db->query('SELECT name FROM media_images')->fetch_all(MYSQLI_ASSOC);

        $orphanImages = [];
        foreach ($cataloged as $row) {
            if (!isset($referenced[$row['name']])) {
                $orphanImages[] = $row['name'];
            }
        }

        foreach ($orphanImages as $name) {
            $this->warn("imagen catalogada nunca referenciada: {$name}");
        }

        $this->info(sprintf(
            'catalogadas: %d, nunca referenciadas: %d',
            count($cataloged),
            count($orphanImages)
        ));

        return self::SUCCESS;
    }
}
