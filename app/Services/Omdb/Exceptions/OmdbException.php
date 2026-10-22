<?php

namespace App\Services\Omdb\Exceptions;

use RuntimeException;

/**
 * Thrown when the OMDb API cannot be reached or returns an error.
 */
class OmdbException extends RuntimeException
{
    /**
     * A message that is safe to show to the end user (in Latvian).
     */
    public function userMessage(): string
    {
        return match (true) {
            str_contains($this->getMessage(), 'Too many results') => 'Atrasts pārāk daudz rezultātu. Lūdzu, precizējiet nosaukumu.',
            str_contains($this->getMessage(), 'API key') => 'OMDb API atslēga nav norādīta vai nav derīga. Pārbaudiet OMDB_API_KEY vērtību .env failā.',
            str_contains($this->getMessage(), 'limit') => 'Sasniegts OMDb pieprasījumu dienas limits. Mēģiniet vēlreiz vēlāk.',
            default => 'Neizdevās iegūt datus no OMDb servisa. Lūdzu, mēģiniet vēlreiz vēlāk.',
        };
    }
}
