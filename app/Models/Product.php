<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'niche_id', 'store_id', 'name', 'description',
        'image_path', 'price', 'original_price', 'affiliate_url',
        'badge', 'rating', 'rating_count', 'featured', 'active',
        'promotion_ends_at',
    ];

    protected $appends = ['image_url', 'views_today'];

    protected function casts(): array
    {
        return [
            'price'              => 'float',
            'original_price'     => 'float',
            'rating'             => 'float',
            'rating_count'       => 'integer',
            'featured'           => 'boolean',
            'active'             => 'boolean',
            'promotion_ends_at'  => 'datetime',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path
            ? Storage::disk('r2')->url($this->image_path)
            : null
        );
    }

    protected function viewsToday(): Attribute
    {
        return Attribute::get(
            fn () => (int) Cache::get("product_views:{$this->id}:" . now()->format('Y-m-d'), 0)
        );
    }

    public function niche(): BelongsTo
    {
        return $this->belongsTo(Niche::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }
}
