<?php

namespace App\Services\Ai;

use InvalidArgumentException;

final class CatalogEnricher
{
    public function __construct(
        private readonly AiProvider $provider,
    ) {
    }

    /**
     * @param array<string, mixed> $product
     * @return array{seo_title:string,bullets:list<string>,search_terms:list<string>}
     */
    public function enrich(array $product): array
    {
        $raw = $this->provider->enrich($product);

        return $this->assertSchema($raw);
    }

    /**
     * @param array<string, mixed> $raw
     * @return array{seo_title:string,bullets:list<string>,search_terms:list<string>}
     */
    public function assertSchema(array $raw): array
    {
        $title = trim((string) ($raw['seo_title'] ?? ''));
        $bullets = array_values(array_filter(array_map('strval', (array) ($raw['bullets'] ?? []))));
        $terms = array_values(array_filter(array_map('strval', (array) ($raw['search_terms'] ?? []))));

        if ($title === '' || mb_strlen($title) > 70) {
            throw new InvalidArgumentException('AI response seo_title is missing or too long.');
        }

        if (count($bullets) < 3 || count($bullets) > 6) {
            throw new InvalidArgumentException('AI response must include 3 to 6 bullets.');
        }

        if ($terms === []) {
            throw new InvalidArgumentException('AI response must include search_terms.');
        }

        return [
            'seo_title' => $title,
            'bullets' => array_map(static fn (string $item): string => mb_substr($item, 0, 160), $bullets),
            'search_terms' => array_slice($terms, 0, 12),
        ];
    }
}
