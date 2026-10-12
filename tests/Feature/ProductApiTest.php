<?php

namespace Tests\Feature;

use App\Jobs\EnrichProductJob;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalog\ProductNormalizer;
use App\Services\Sync\MerchantChannel;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    public function testRequiresAuthentication(): void
    {
        $this->postJson('/api/v1/products', ['sku' => 'A', 'title' => 'A', 'price' => 1])
            ->assertUnauthorized();
    }

    public function testCreatesProductAndQueuesEnrichment(): void
    {
        Queue::fake();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/products', [
                'sku' => 'PIPE-15',
                'title' => 'Copper pipe 15mm',
                'price' => 19.9,
                'stock' => 4,
                'gtin' => '5901234123457',
            ])
            ->assertCreated()
            ->assertJsonPath('sku', 'PIPE-15')
            ->assertJsonPath('price_minor', 1990);

        $product = Product::query()->firstOrFail();

        $this->postJson('/api/v1/products/' . $product->id . '/enrich')
            ->assertAccepted();

        Queue::assertPushed(EnrichProductJob::class);
    }

    public function testDispatchesSyncAndPushesOffer(): void
    {
        MerchantChannel::reset();
        Sanctum::actingAs(User::factory()->create());
        Product::factory()->create(['sku' => 'SKU-SYNC', 'stock' => 2]);

        $this->postJson('/api/v1/sync/merchant', ['limit' => 10])
            ->assertAccepted();

        $this->assertNotNull((new MerchantChannel(new ProductNormalizer()))->find('SKU-SYNC'));
    }

    public function testWebhookRequiresSignature(): void
    {
        $this->postJson('/api/webhooks/merchant', ['event' => 'offer.updated', 'sku' => 'X'])
            ->assertUnauthorized();

        $payload = json_encode(['event' => 'offer.updated', 'sku' => 'SKU-SYNC'], JSON_THROW_ON_ERROR);
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'test-webhook-secret');

        $this->call('POST', '/api/webhooks/merchant', [], [], [], [
            'HTTP_X_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();
    }
}
