<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Store extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug', 'name', 'logo_path', 'color', 'text_color', 'active',
    ];

    protected $appends = ['logo_url'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->logo_path
            ? Storage::disk('r2')->url($this->logo_path)
            : null
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
