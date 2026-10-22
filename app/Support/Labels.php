<?php

namespace App\Support;

/**
 * Latvian labels used in the views.
 */
final class Labels
{
    private const TYPES = [
        'movie' => 'Filma',
        'series' => 'Seriāls',
        'episode' => 'Epizode',
        'game' => 'Spēle',
    ];

    public static function type(?string $type): string
    {
        return self::TYPES[$type ?? ''] ?? (string) $type;
    }

    /**
     * "1 rezultāts", "21 rezultāts", "2 rezultāti", "11 rezultāti" …
     * (Latvian uses the singular for numbers ending in 1, except 11).
     */
    public static function results(int $count): string
    {
        $singular = $count % 10 === 1 && $count % 100 !== 11;

        return number_format($count, 0, ',', ' ').' '.($singular ? 'rezultāts' : 'rezultāti');
    }
}
