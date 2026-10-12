<?php

namespace App\Services\Catalog;

use App\Models\Product;

final class ProductNormalizer
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function normalize(array $input): array
    {
        $price = $input['price_minor'] ?? $this->toMinor($input['price'] ?? 0);

        return [
            'sku' => strtoupper(trim((string) $input['sku'])),
            'title' => mb_substr(trim((string) $input['title']), 0, 150),
            'description' => $this->plain((string) ($input['description'] ?? '')),
            'url' => (string) ($input['url'] ?? ''),
            'price_minor' => max(0, (int) $price),
            'currency' => strtoupper((string) ($input['currency'] ?? 'EUR')),
            'stock' => max(0, (int) ($input['stock'] ?? 0)),
            'brand' => $this->nullIfEmpty($input['brand'] ?? null),
            'gtin' => $this->gtin($input['gtin'] ?? null),
        ];
    }

    public function hash(Product $product): string
    {
        return hash('sha256', implode('|', [
            $product->sku,
            $product->title,
            (string) $product->price_minor,
            (string) $product->stock,
            (string) $product->gtin,
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function channelPayload(Product $product): array
    {
        return [
            'offerId' => $product->sku,
            'title' => $product->title,
            'description' => $product->description,
            'link' => $product->url,
            'price' => [
                'value' => number_format($product->price_minor / 100, 2, '.', ''),
                'currency' => $product->currency,
            ],
            'availability' => $product->stock > 0 ? 'in_stock' : 'out_of_stock',
            'brand' => $product->brand,
            'gtin' => $product->gtin,
        ];
    }

    private function toMinor(mixed $price): int
    {
        return (int) round(((float) $price) * 100);
    }

    private function plain(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return mb_substr(trim(preg_replace('/\s+/u', ' ', $text) ?? $text), 0, 5000);
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function gtin(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        return $digits === '' ? null : $digits;
    }
}
