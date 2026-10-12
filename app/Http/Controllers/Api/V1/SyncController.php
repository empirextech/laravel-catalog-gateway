<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\PushCatalogBatch;
use App\Models\SyncRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function dispatchRun(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'channel' => ['nullable', 'string', 'max:32'],
        ]);

        $run = SyncRun::query()->create([
            'channel' => $validated['channel'] ?? 'merchant',
            'status' => 'queued',
        ]);

        PushCatalogBatch::dispatch($run->id, (int) ($validated['limit'] ?? 100));

        return response()->json(['sync_run_id' => $run->id, 'status' => $run->status], 202);
    }

    public function show(SyncRun $syncRun): JsonResponse
    {
        return response()->json($syncRun);
    }
}
