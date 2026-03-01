<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CellarWine extends Model
{
    use HasUuids;

    protected $table = 'cellar_wines';

    protected $fillable = [
        'user_id', 'wine_id', 'quantity', 'purchase_date', 'purchase_price', 'location', 'notes',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'purchase_price' => 'float',
        'quantity'       => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }
}
