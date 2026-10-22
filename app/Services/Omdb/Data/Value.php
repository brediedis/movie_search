<?php

namespace App\Services\Omdb\Data;

/**
 * Helpers for OMDb values, which use the string "N/A" for missing data.
 */
final class Value
{
    public static function orNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return ($value === '' || strcasecmp($value, 'N/A') === 0) ? null : $value;
    }
}
