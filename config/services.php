<?php

return [
    'ai' => [
        'fake' => (bool) env('AI_FAKE', true),
        'endpoint' => env('AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'key' => env('AI_KEY', ''),
        'model' => env('AI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('AI_TIMEOUT', 15),
    ],
    'webhook' => [
        'secret' => env('WEBHOOK_SECRET', ''),
    ],
];
