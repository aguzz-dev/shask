<?php
namespace App\Services;

/**
 * Reads procedurally-generated design presets from resources/design_presets/*.json.
 * Each file is a publishable payload (title, colors, icon, background, canvas);
 * the filename (without extension) is the stable preset key.
 *
 * These presets are authored by the Flutter-side generator (tool/seed_assets)
 * and copied here verbatim. The backend treats `canvas` as an opaque blob — it
 * only stores and serves it; the app renders it.
 */
class DesignPresetCatalog
{
    private string $dir;

    public function __construct(?string $dir = null)
    {
        $this->dir = $dir ?? resource_path('design_presets');
    }

    /** @return array<int,array> presets sorted by title */
    public function all(): array
    {
        if (!is_dir($this->dir)) {
            return [];
        }
        $out = [];
        foreach (glob($this->dir . '/*.json') as $path) {
            $data = $this->load($path, basename($path, '.json'));
            if ($data !== null) {
                $out[] = $data;
            }
        }
        usort($out, fn ($a, $b) => strcmp($a['title'] ?? $a['key'], $b['title'] ?? $b['key']));
        return $out;
    }

    public function find(string $key): ?array
    {
        $key = preg_replace('/[^a-z0-9_]/', '', strtolower($key));
        if ($key === '') {
            return null;
        }
        $path = $this->dir . '/' . $key . '.json';
        return is_file($path) ? $this->load($path, $key) : null;
    }

    private function load(string $path, string $key): ?array
    {
        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data)) {
            return null;
        }
        $data['key'] = $key;
        $data['preview'] = $this->preview($data['canvas'] ?? []);
        return $data;
    }

    /**
     * Representative (NOT pixel-perfect) preview hints for the web panel:
     * background CSS, pattern name and the title color/font. The real canvas
     * is rendered by the Flutter engine, not reproduced here.
     */
    private function preview(array $canvas): array
    {
        $layers = $canvas['shareCard']['layers'] ?? [];
        $preview = ['bg' => '#dddddd', 'pattern' => null, 'titleColor' => '#111111', 'titleFont' => 'Hanken Grotesk'];

        foreach ($layers as $layer) {
            switch ($layer['type'] ?? '') {
                case 'background':
                    $preview['bg'] = $this->fillToCss($layer['fill'] ?? []);
                    break;
                case 'pattern':
                    $preview['pattern'] = $layer['kind'] ?? null;
                    break;
                case 'group':
                    foreach ($layer['children'] ?? [] as $child) {
                        if (($child['type'] ?? '') === 'text'
                            && str_contains((string) ($child['content'] ?? ''), '{{title}}')) {
                            $style = $child['style'] ?? [];
                            $preview['titleColor'] = $style['color'] ?? $preview['titleColor'];
                            $preview['titleFont'] = ($style['fontFamily'] ?? '') === 'LondrinaSolid'
                                ? 'Londrina Solid' : 'Hanken Grotesk';
                        }
                    }
                    break;
            }
        }
        return $preview;
    }

    private function fillToCss(array $fill): string
    {
        if (($fill['type'] ?? 'color') === 'gradient') {
            $colors = $fill['colors'] ?? [];
            return count($colors) >= 2
                ? 'linear-gradient(135deg, ' . implode(', ', $colors) . ')'
                : ($colors[0] ?? '#dddddd');
        }
        return $fill['value'] ?? '#dddddd';
    }
}
