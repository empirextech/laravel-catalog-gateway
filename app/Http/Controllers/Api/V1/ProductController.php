<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertProductRequest;
use App\Jobs\EnrichProductJob;
use App\Models\Product;
use App\Services\Catalog\ProductNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->when($request->string('q')->toString(), function ($query, string $q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('sku', 'like', '%' . $q . '%')
                        ->orWhere('title', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('id')
            ->paginate(25);

        return response()->json($products);
    }

    public function store(UpsertProductRequest $request, ProductNormalizer $normalizer): JsonResponse
    {
        $data = $normalizer->normalize($request->validated());
        $product = Product::query()->updateOrCreate(['sku' => $data['sku']], $data);

        return response()->json($product, $product->wasRecentlyCreated ? 201 : 200);
    }

    public function enrich(Product $product): JsonResponse
    {
        EnrichProductJob::dispatch($product->id);

        return response()->json([
            'queued' => true,
            'product_id' => $product->id,
        ], 202);
    }
}
