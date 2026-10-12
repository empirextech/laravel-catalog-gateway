<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'sku',
        'title',
        'description',
        'url',
        'price_minor',
        'currency',
        'stock',
        'brand',
        'gtin',
        'payload',
        'enriched_at',
        'synced_at',
        'sync_hash',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'enriched_at' => 'datetime',
            'synced_at' => 'datetime',
            'price_minor' => 'integer',
            'stock' => 'integer',
        ];
    }
}
