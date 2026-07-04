<?php

namespace App\Services;

/**
 * Push copy for the creator-acquisition push model (D4.2). Centralized so
 * the individual push (real-time + flush) and the digest use the exact same
 * title/body — matches the existing NotifyPostLifecycle style (Spanish,
 * short, with emoji), just centralized instead of inlined per call-site
 * since the individual text is shared by two commands.
 */
final class CreatorPushCopy
{
    public const SINGLE_TITLE = '🔥 ¡Alguien adquirió tu diseño!';
    public const SINGLE_BODY  = 'Mirá cuánto hype ganaste en tu perfil de creador';

    public const DIGEST_TITLE = '🎉 Tu diseño está on fire';

    public static function digestBody(int $count): string
    {
        return "{$count} personas adquirieron tus diseños hoy";
    }

    private function __construct()
    {
        // Static-only class — never instantiated.
    }
}
