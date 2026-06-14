<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Niche extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug', 'name', 'description', 'icon',
        'bg_color', 'instagram_url', 'tiktok_url', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
