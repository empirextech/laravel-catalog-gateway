<?php

namespace App\Services\Ai;

interface AiProvider
{
    /**
     * @param array<string, mixed> $product
     * @return array{seo_title:string,bullets:list<string>,search_terms:list<string>}
     */
    public function enrich(array $product): array;
}
