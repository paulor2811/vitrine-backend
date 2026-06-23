<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Niche extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug', 'name', 'description', 'icon',
        'image_path', 'bg_color', 'instagram_url', 'tiktok_url', 'active',
        'meta_pixel_id', 'meta_access_token',
    ];

    protected $hidden = [
        'meta_access_token',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path
            ? Storage::disk('r2')->url($this->image_path)
            : null
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
