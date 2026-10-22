<?php

namespace App\Services\Omdb\Exceptions;

/**
 * Thrown when OMDb has no movie for the given title or IMDb ID.
 */
class MovieNotFoundException extends OmdbException
{
    public function userMessage(): string
    {
        return 'Filma netika atrasta.';
    }
}
