<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\SyncRun;
use App\Services\Sync\MerchantChannel;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PushCatalogBatch implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $uniqueFor = 300;

    public function __construct(
        public readonly int $syncRunId,
        public readonly int $limit = 100,
    ) {
        $this->onQueue('sync');
    }

    public function uniqueId(): string
    {
        return 'catalog-push';
    }

    public function handle(MerchantChannel $channel): void
    {
        $run = SyncRun::query()->findOrFail($this->syncRunId);
        $run->update(['status' => 'running', 'started_at' => now()]);

        $processed = 0;
        $failed = 0;

        Product::query()
            ->orderBy('id')
            ->limit($this->limit)
            ->get()
            ->each(function (Product $product) use ($channel, &$processed, &$failed): void {
                try {
                    $hash = $channel->push($product);
                    $product->update([
                        'synced_at' => now(),
                        'sync_hash' => $hash,
                    ]);
                    $processed++;
                } catch (\Throwable $exception) {
                    $failed++;
                    report($exception);
                }
            });

        $run->update([
            'status' => $failed > 0 && $processed === 0 ? 'failed' : 'completed',
            'processed' => $processed,
            'failed' => $failed,
            'finished_at' => now(),
        ]);
    }
}
