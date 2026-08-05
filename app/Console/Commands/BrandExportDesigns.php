<?php

namespace App\Console\Commands;

use App\Database;
use App\Support\BrandDesignHasher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Exports a submitter's UGC designs from the LOCAL connection configured in
 * `.env` into a JSON envelope, so they can be moved to another environment
 * (typically production) and imported there as brand-attributed designs via
 * `brand:import-designs`.
 *
 * Deliberately reads whatever connection `.env` already points at — no
 * connection-switching inside the command. On a developer's machine that is
 * the local `sshask` DB; in production it would be the prod DB. The operator
 * is responsible for running this where the SOURCE designs live.
 */
class BrandExportDesigns extends Command
{
    protected $signature = 'brand:export-designs {--submitter=} {--out=}';

    protected $description = "Exporta a JSON los diseños UGC de un submitter (para publicarlos como marca en otro ambiente)";

    public function handle(): int
    {
        $submitterOpt = $this->option('submitter');
        if ($submitterOpt === null || $submitterOpt === '' || !ctype_digit((string) $submitterOpt)) {
            $this->error('--submitter=<user_id> es obligatorio (id numérico, nunca inferido).');
            return self::FAILURE;
        }
        $submitterId = (int) $submitterOpt;

        $db = new Database;
        $stmt = $db->dbConnection->prepare(
            "SELECT pa.title, pa.color, pa.icon, pa.background, pa.canvas, c.slug AS category_slug
             FROM public_assets pa
             LEFT JOIN categories c ON c.id = pa.category_id
             WHERE pa.submitter_user_id = ? AND pa.status IN ('pending', 'approved')
             ORDER BY pa.id ASC"
        );
        $stmt->bind_param('i', $submitterId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $designs = [];
        foreach ($rows as $row) {
            $title      = (string) $row['title'];
            $color      = $row['color'] !== null ? json_decode($row['color'], true) : [];
            $icon       = (string) ($row['icon'] ?? '');
            $background = (string) ($row['background'] ?? '');
            $canvas     = $row['canvas'] !== null ? json_decode($row['canvas'], true) : null;

            $designs[] = [
                'title'           => $title,
                'color'           => $color,
                'icon'            => $icon,
                'background'      => $background,
                'canvas'          => $canvas,
                'category_slug'   => $row['category_slug'],
                'idempotency_key' => BrandDesignHasher::hash($title, $color, $icon, $background, $canvas),
            ];
        }

        $envelope = [
            'version'                  => 1,
            'exported_at'              => now()->toIso8601String(),
            'source_db'                => DB::connection()->getDatabaseName(),
            'source_submitter_user_id' => $submitterId,
            'designs'                  => $designs,
        ];

        $out = $this->option('out') ?: storage_path('app/brand-export-' . now()->format('Ymd_His') . '.json');
        $dir = dirname($out);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($out, json_encode($envelope, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('exported: ' . count($designs) . ", source_db: {$envelope['source_db']}, out: {$out}");

        return self::SUCCESS;
    }
}
