<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $query
 * @property string $normalized
 */
class SearchQuery extends Model
{
    protected $fillable = ['query', 'normalized'];
}
