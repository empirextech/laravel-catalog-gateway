<?php

namespace Tests\Unit;

use App\Services\Ai\CatalogEnricher;
use App\Services\Ai\FakeProvider;
use App\Services\Catalog\ProductNormalizer;
use PHPUnit\Framework\TestCase;

final class ProductNormalizerTest extends TestCase
{
    public function testNormalizesHtmlAndGtin(): void
    {
        $data = (new ProductNormalizer())->normalize([
            'sku' => ' sku-9 ',
            'title' => 'Copper elbow',
            'description' => '<p>15&nbsp;mm</p>',
            'price' => 12.5,
            'gtin' => '590 1234',
            'stock' => 3,
        ]);

        self::assertSame('SKU-9', $data['sku']);
        self::assertSame('15 mm', $data['description']);
        self::assertSame(1250, $data['price_minor']);
        self::assertSame('5901234', $data['gtin']);
    }

    public function testEnricherRejectsShortBulletList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new CatalogEnricher(new FakeProvider()))->assertSchema([
            'seo_title' => 'Ok title',
            'bullets' => ['only one'],
            'search_terms' => ['pipe'],
        ]);
    }

    public function testFakeProviderPassesSchema(): void
    {
        $result = (new CatalogEnricher(new FakeProvider()))->enrich([
            'sku' => 'SKU-1',
            'title' => 'Valve',
            'brand' => 'EMPIREX',
        ]);

        self::assertCount(3, $result['bullets']);
        self::assertNotSame('', $result['seo_title']);
    }
}
