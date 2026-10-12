<?php

namespace App\Services\Sync;

use App\Models\Product;
use App\Services\Catalog\ProductNormalizer;

/**
 * In-memory merchant channel used by tests and local demos.
 * Swap for a Google Merchant / marketplace HTTP client in a real integration.
 */
final class MerchantChannel
{
    /** @var array<string, array<string, mixed>> */
    private static array $offers = [];

    public function __construct(
        private readonly ProductNormalizer $normalizer,
        private readonly CircuitBreaker $breaker = new CircuitBreaker(),
    ) {
    }

    public function push(Product $product): string
    {
        if (!$this->breaker->allow()) {
            throw new \RuntimeException('Merchant circuit is open.');
        }

        try {
            $payload = $this->normalizer->channelPayload($product);
            self::$offers[$product->sku] = $payload;
            $this->breaker->success();

            return $this->normalizer->hash($product);
        } catch (\Throwable $exception) {
            $this->breaker->failure();
            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $sku): ?array
    {
        return self::$offers[$sku] ?? null;
    }

    public static function reset(): void
    {
        self::$offers = [];
    }
}
