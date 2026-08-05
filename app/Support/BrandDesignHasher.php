<?php

namespace App\Support;

/**
 * Deterministic idempotency key for a brand design payload.
 *
 * Shared by `brand:export-designs` (writes it into the export envelope) and
 * `brand:import-designs` (recomputes it for every design already present at
 * the destination, so re-running an import is a no-op instead of creating
 * duplicates). Keeping the formula in one place — instead of duplicating it
 * in both commands — prevents the two sides from silently drifting apart.
 */
final class BrandDesignHasher
{
    /**
     * @param mixed $color  Decoded color payload (array or scalar as stored)
     * @param mixed $canvas Decoded canvas payload (array, or null)
     */
    public static function hash(string $title, $color, string $icon, string $background, $canvas): string
    {
        return hash(
            'sha256',
            $title . '|' . json_encode(self::canonical($color)) . '|' . $icon . '|' . $background
                . '|' . json_encode(self::canonical($canvas))
        );
    }

    /**
     * MySQL's native JSON column type does NOT preserve the original key
     * order of an object on read — empirically confirmed: inserting
     * `{"r":1,"g":2,"b":3}` and reading it back returns `{"b":3,"g":2,"r":1}`.
     * Without canonicalizing, the same logical payload hashes differently
     * depending on whether it was just decoded from a PHP array (export
     * side / envelope) or round-tripped through a MySQL JSON column
     * (existing destination rows on import) — breaking idempotency. List
     * arrays (JSON arrays, e.g. canvas layers) are order-significant and are
     * left alone; only associative arrays (JSON objects) are key-sorted.
     */
    private static function canonical($value)
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                return array_map([self::class, 'canonical'], $value);
            }
            ksort($value);
            return array_map([self::class, 'canonical'], $value);
        }
        return $value;
    }
}
