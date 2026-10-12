<?php

namespace App\Services\Sync;

use Illuminate\Support\Facades\Cache;

final class CircuitBreaker
{
    public function __construct(
        private readonly string $name = 'merchant',
        private readonly int $threshold = 5,
        private readonly int $openSeconds = 60,
    ) {
    }

    public function allow(): bool
    {
        return Cache::get($this->key('state'), 'closed') !== 'open'
            || now()->timestamp >= (int) Cache::get($this->key('opened_at'), 0) + $this->openSeconds;
    }

    public function success(): void
    {
        Cache::forget($this->key('failures'));
        Cache::put($this->key('state'), 'closed', $this->openSeconds * 10);
    }

    public function failure(): void
    {
        $failures = (int) Cache::increment($this->key('failures'));
        if ($failures >= $this->threshold) {
            Cache::put($this->key('state'), 'open', $this->openSeconds);
            Cache::put($this->key('opened_at'), now()->timestamp, $this->openSeconds);
        }
    }

    private function key(string $suffix): string
    {
        return 'circuit:' . $this->name . ':' . $suffix;
    }
}
