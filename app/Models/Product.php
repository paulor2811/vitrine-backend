<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'niche_id', 'store_id', 'name', 'description',
        'image_url', 'price', 'original_price', 'affiliate_url',
        'badge', 'rating', 'rating_count', 'featured', 'active',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'float',
            'original_price' => 'float',
            'rating'         => 'float',
            'rating_count'   => 'integer',
            'featured'       => 'boolean',
            'active'         => 'boolean',
        ];
    }

    public function niche(): BelongsTo
    {
        return $this->belongsTo(Niche::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
