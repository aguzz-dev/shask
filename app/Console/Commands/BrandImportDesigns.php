<?php

namespace App\Console\Commands;

use App\Database;
use App\Models\Asset;
use App\Models\AdminAuditLog;
use App\Support\BrandDesignHasher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Imports a JSON envelope produced by `brand:export-designs` into whatever
 * connection `.env` points at (typically production for a real run). Every
 * inserted design is forced `approved` via `Asset::createPublicAsset(...,
 * forceStatus: 'approved')`, bypassing the "first asset from this submitter
 * → pending" rule that exists for organic user submissions.
 *
 * Safety notes (operational, not code-enforced — there is no reliable way
 * from inside a single CLI invocation to prove a PRIOR `--dry-run` happened):
 *   - ALWAYS run with `--dry-run` first against the destination and review
 *     `target_db` in the printed report before running for real.
 *   - `--submitter` is REQUIRED and never inferred — this command refuses to
 *     run without it.
 *   - Idempotent by content hash (title + color + icon + background +
 *     canvas, see BrandDesignHasher): re-running the same file is a no-op.
 */
class BrandImportDesigns extends Command
{
    protected $signature = 'brand:import-designs {file} {--submitter=} {--dry-run} {--admin=0}';

    protected $description = 'Importa un JSON exportado con brand:export-designs, insertando cada diseño ya aprobado bajo el submitter indicado';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!is_readable($path)) {
            $this->error("No se puede leer el archivo: {$path}");
            return self::FAILURE;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            $this->error("No se pudo leer el contenido de: {$path}");
            return self::FAILURE;
        }
        $fileHash = hash('sha256', $raw);

        $envelope = json_decode($raw, true);
        if (!is_array($envelope) || !isset($envelope['designs']) || !is_array($envelope['designs'])) {
            $this->error('El archivo no es un envelope válido (falta la clave "designs").');
            return self::FAILURE;
        }

        $submitterOpt = $this->option('submitter');
        if ($submitterOpt === null || $submitterOpt === '' || !ctype_digit((string) $submitterOpt)) {
            $this->error('--submitter=<user_id> es obligatorio (id numérico, nunca inferido).');
            return self::FAILURE;
        }
        $submitterId = (int) $submitterOpt;

        $dryRun  = (bool) $this->option('dry-run');
        $adminId = (int) $this->option('admin');

        $asset = new Asset;
        $db    = new Database;

        // Idempotencia por hash de contenido, no por id (el import re-emite
        // ids nuevos en destino). Se recalcula el mismo hash sobre lo que YA
        // existe en destino para este submitter.
        $existingStmt = $db->dbConnection->prepare(
            'SELECT title, color, icon, background, canvas FROM public_assets WHERE submitter_user_id = ?'
        );
        $existingStmt->bind_param('i', $submitterId);
        $existingStmt->execute();
        $existingRows = $existingStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $existingStmt->close();

        $existingHashes = [];
        foreach ($existingRows as $row) {
            $color  = $row['color'] !== null ? json_decode($row['color'], true) : [];
            $canvas = $row['canvas'] !== null ? json_decode($row['canvas'], true) : null;
            $existingHashes[BrandDesignHasher::hash(
                (string) $row['title'],
                $color,
                (string) ($row['icon'] ?? ''),
                (string) ($row['background'] ?? ''),
                $canvas
            )] = true;
        }

        $created = [];
        $skipped = [];

        foreach ($envelope['designs'] as $design) {
            $title        = (string) ($design['title'] ?? '');
            $color        = is_array($design['color'] ?? null) ? $design['color'] : [];
            $icon         = (string) ($design['icon'] ?? '');
            $background   = (string) ($design['background'] ?? '');
            $canvas       = is_array($design['canvas'] ?? null) ? $design['canvas'] : null;
            $categorySlug = $design['category_slug'] ?? null;

            $hash = BrandDesignHasher::hash($title, $color, $icon, $background, $canvas);
            $key  = $design['idempotency_key'] ?? $hash;

            if (isset($existingHashes[$hash])) {
                $skipped[] = $key;
                continue;
            }

            if ($dryRun) {
                // Would-create — no row exists yet, so report by key, not id.
                $created[] = $key;
                continue;
            }

            try {
                $newId = $asset->createPublicAsset(
                    $title,
                    $color,
                    $icon,
                    $background,
                    $canvas,
                    $submitterId,
                    'approved'
                );
            } catch (\Throwable $e) {
                $this->error("Fallo insertando '{$title}': " . $e->getMessage());
                return self::FAILURE;
            }

            if ($categorySlug !== null) {
                $catStmt = $db->dbConnection->prepare(
                    'UPDATE public_assets SET category_id = (SELECT id FROM categories WHERE slug = ?) WHERE id = ?'
                );
                $catStmt->bind_param('si', $categorySlug, $newId);
                $catStmt->execute();
                $catStmt->close();
            }

            $existingHashes[$hash] = true;
            $created[] = $newId;
        }

        $targetDb = DB::connection()->getDatabaseName();

        $this->info(sprintf(
            'created: %d, skipped: %d, target_db: %s, file_sha256: %s%s',
            count($created),
            count($skipped),
            $targetDb,
            $fileHash,
            $dryRun ? ' (dry-run — no rows written, no audit logged)' : ''
        ));

        if (!$dryRun) {
            AdminAuditLog::log($adminId, 'brand.import', [
                'actor'         => $adminId === 0 ? 'system:cli' : "admin:{$adminId}",
                'submitter_id'  => $submitterId,
                'created'       => $created,
                'skipped_count' => count($skipped),
                'target_db'     => $targetDb,
                'file_sha256'   => $fileHash,
            ]);
        }

        return self::SUCCESS;
    }
}
