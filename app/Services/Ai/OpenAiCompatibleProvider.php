<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

final class OpenAiCompatibleProvider implements AiProvider
{
    public function __construct(
        private readonly string $endpoint,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $timeout = 15,
    ) {
    }

    /**
     * @param array<string, mixed> $product
     * @return array{seo_title:string,bullets:list<string>,search_terms:list<string>}
     */
    public function enrich(array $product): array
    {
        if ($this->apiKey === '') {
            throw new \RuntimeException('AI_KEY is not configured.');
        }

        $response = Http::timeout($this->timeout)
            ->retry(2, 200)
            ->withToken($this->apiKey)
            ->acceptJson()
            ->post($this->endpoint, [
                'model' => $this->model,
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Return JSON with keys seo_title (max 70 chars), bullets (3-6 strings), search_terms (array of strings). No markdown.',
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode([
                            'sku' => $product['sku'] ?? '',
                            'title' => $product['title'] ?? '',
                            'description' => mb_substr((string) ($product['description'] ?? ''), 0, 800),
                            'brand' => $product['brand'] ?? '',
                        ], JSON_THROW_ON_ERROR),
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('AI provider returned HTTP ' . $response->status());
        }

        $content = (string) data_get($response->json(), 'choices.0.message.content', '{}');
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('AI provider returned non-JSON content.');
        }

        /** @var array{seo_title?:string,bullets?:list<string>,search_terms?:list<string>} $decoded */
        return [
            'seo_title' => (string) ($decoded['seo_title'] ?? ''),
            'bullets' => array_values((array) ($decoded['bullets'] ?? [])),
            'search_terms' => array_values((array) ($decoded['search_terms'] ?? [])),
        ];
    }
}
