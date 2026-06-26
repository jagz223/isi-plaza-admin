<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Treatment extends Model
{
    protected $fillable = [
        'treatment_section_id',
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
     * @return BelongsTo<TreatmentSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(TreatmentSection::class, 'treatment_section_id');
    }

    /**
     * @return HasMany<DoctorService, $this>
     */
    public function doctorServices(): HasMany
    {
        return $this->hasMany(DoctorService::class);
    }
}
