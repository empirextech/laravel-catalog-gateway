<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function merchant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event' => ['required', 'in:offer.updated,offer.deleted'],
            'sku' => ['required', 'string', 'max:64'],
            'status' => ['nullable', 'string', 'max:32'],
        ]);

        $product = Product::query()->where('sku', strtoupper($validated['sku']))->first();
        if ($product && $validated['event'] === 'offer.deleted') {
            $product->update(['synced_at' => null, 'sync_hash' => null]);
        }

        return response()->json(['accepted' => true]);
    }
}
