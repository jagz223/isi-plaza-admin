<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $fillable = [
        'comprador_id',
        'mayorista_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function comprador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'comprador_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function mayorista(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mayorista_id');
    }
}
