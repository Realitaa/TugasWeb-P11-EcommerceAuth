<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductApiResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
    ) {}

    /**
     * Display a listing of products.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Product::class);

        $perPage = (int) $request->input('per_page', 15);
        $products = $this->productService->getProducts($request->all(), $perPage);

        return ProductApiResource::collection($products);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): ProductApiResource
    {
        Gate::authorize('view', $product);

        $product->load(['category', 'user:id,name,email', 'tags']);

        return new ProductApiResource($product);
    }

    /**
     * Store a newly created product.
     * Only Admin can access and execute this method.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated(), $request->user());

        return (new ProductApiResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the specified product.
     * Admin can update all fields.
     * Editor can only update numerical quantities (price, stock) and manage/assign tags.
     */
    public function update(UpdateProductRequest $request, Product $product): ProductApiResource
    {
        $updatedProduct = $this->productService->updateProduct(
            $product,
            $request->validated(),
            $request->user(),
        );

        return new ProductApiResource($updatedProduct);
    }

    /**
     * Remove the specified product.
     * Only Admin can execute this method.
     */
    public function destroy(Product $product): JsonResponse
    {
        Gate::authorize('delete', $product);

        $this->productService->deleteProduct($product, request()->user());

        return response()->json([
            'message' => 'Produk berhasil dihapus.',
        ], Response::HTTP_OK);
    }
}
