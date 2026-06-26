<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentSection extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Treatment, $this>
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<Treatment, $this>
     */
    public function activeTreatments(): HasMany
    {
        return $this->treatments()->where('is_active', true);
    }
}
