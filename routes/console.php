<?php

use App\Jobs\PushCatalogBatch;
use App\Models\SyncRun;
use Illuminate\Support\Facades\Artisan;

Artisan::command('catalog:sync {--limit=100}', function (): void {
    $run = SyncRun::query()->create([
        'channel' => 'merchant',
        'status' => 'queued',
    ]);
    PushCatalogBatch::dispatchSync($run->id, (int) $this->option('limit'));
    $this->info('Sync run ' . $run->fresh()->status . ' processed=' . $run->fresh()->processed);
})->purpose('Push catalog offers to the merchant channel');
