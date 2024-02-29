<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "products";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name', 'description', 'price', 'quantity', 'slug', 'thumbnail', 'status'
    ];

    /**
     * @var array<int,string>
     */
    public $appends = [
        'thumbnail_url'
    ];

    /**
     * @return string
     */
    public function getThumbnailUrlAttribute(): string
    {
        return asset($this->thumbnail);
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, "category_product", 'product_id', 'category_id')
            ->withTimestamps();
    }
}
