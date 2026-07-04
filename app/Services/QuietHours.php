<?php

namespace App\Services;

/**
 * Shared quiet-hours window check for the creator-acquisition push model
 * (D4.2). Used by both the real-time notifier (to decide whether to defer)
 * and the flush command (to decide whether the window has closed).
 */
class QuietHours
{
    /**
     * True when the current time falls inside the configured quiet-hours
     * window (`marketplace.push_quiet_start`/`push_quiet_end`, HH:MM, app
     * timezone). Handles windows that wrap past midnight (default
     * 22:00-08:00).
     */
    public static function isNow(): bool
    {
        $start = (string) config('marketplace.push_quiet_start', '22:00');
        $end   = (string) config('marketplace.push_quiet_end', '08:00');
        $now   = date('H:i');

        if ($start <= $end) {
            return $now >= $start && $now < $end;
        }

        return $now >= $start || $now < $end;
    }
}
