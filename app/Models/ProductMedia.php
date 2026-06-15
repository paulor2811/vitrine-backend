<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductMedia extends Model
{
    use HasUuids;

    protected $table = 'product_media';

    protected $fillable = ['product_id', 'type', 'path', 'video_url', 'sort_order'];

    protected $appends = ['url'];

    protected $hidden = ['path'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => $this->type === 'image' && $this->path
            ? Storage::disk('r2')->url($this->path)
            : $this->video_url
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
