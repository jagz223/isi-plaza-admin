<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'image_url',
        'sort_order',
        'is_active',
        'clicks_count',
        'link_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'clicks_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
