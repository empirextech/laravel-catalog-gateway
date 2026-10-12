<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\Ai\CatalogEnricher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnrichProductJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $productId,
    ) {
        $this->onQueue('ai');
    }

    public function handle(CatalogEnricher $enricher): void
    {
        $product = Product::query()->findOrFail($this->productId);
        $result = $enricher->enrich($product->only(['sku', 'title', 'description', 'brand']));

        $payload = $product->payload ?? [];
        $payload['ai'] = $result;

        $product->update([
            'payload' => $payload,
            'enriched_at' => now(),
        ]);
    }
}
