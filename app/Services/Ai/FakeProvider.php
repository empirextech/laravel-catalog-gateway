<?php

namespace App\Services\Ai;

final class FakeProvider implements AiProvider
{
    /**
     * @param array<string, mixed> $product
     * @return array{seo_title:string,bullets:list<string>,search_terms:list<string>}
     */
    public function enrich(array $product): array
    {
        $title = mb_substr((string) ($product['title'] ?? 'Product'), 0, 50);

        return [
            'seo_title' => $title . ' | shop',
            'bullets' => [
                'In-stock catalog item ready for merchant feed.',
                'Mapped identifiers and integer pricing.',
                'Structured copy generated locally for tests.',
            ],
            'search_terms' => array_values(array_filter([
                strtolower((string) ($product['sku'] ?? '')),
                strtolower((string) ($product['brand'] ?? 'generic')),
                'catalog',
            ])),
        ];
    }
}
