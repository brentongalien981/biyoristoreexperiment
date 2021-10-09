<?php

namespace App\Http\Controllers;

use App\Brand;
use Exception;
use App\Product;
use App\Category;
use App\Http\Resources\BrandResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ProductResource;
use stdClass;

class ProductController extends Controller
{
    public function relatedProducts(Request $request)
    {
        //
        $validatedData = $request->validate([
            'productId' => 'nullable|numeric',
        ]);


        //
        $product = Product::find($validatedData['productId']);
        $category = $product->categories[0];
        $relatedProducts = ProductResource::collection($category->products()->take(9)->get());


        return [
            'isResultOk' => true,
            'comment' => "CLASS: ProductController, METHOD: relatedProducts()",
            'objs' => $relatedProducts
        ];
    }

    public function show(Request $request)
    {
        $validatedData = $request->validate([
            'requestUrlQ' => 'nullable|string|max:128',
            'productId' => 'nullable|numeric',
        ]);


        $requestUrlQ = $validatedData['requestUrlQ'] ?? 'DEFAULT-REQUEST-URL-Q-FOR-ROUTE=products/show';
        $retrievedDataFrom = 'db';
        $data = null;


        if (Cache::store('redisreader')->has($requestUrlQ)) {
            $retrievedDataFrom = 'cache';
            $data = Cache::store('redisreader')->get($requestUrlQ);
        } else {
            $product = Product::find($validatedData['productId']);
            $category = $product->categories[0];
            $relatedProducts = ProductResource::collection($category->products()->take(9)->get());

            $data = [
                'product' => new ProductResource($product),
                'relatedProducts' => $relatedProducts
            ];

            Cache::store('redisprimary')->put($requestUrlQ, $data, now()->addHours(6));
        }



        return [
            'isResultOk' => true,
            'objs' => [
                'product' => $data['product'],
                'relatedProducts' => $data['relatedProducts'],
                'retrievedDataFrom' => $retrievedDataFrom,
            ],
        ];
    }

    public function featured()
    {
        $featuredBrands = Brand::orderBy('name', 'asc')->take(10)->get();

        $modifiedFeaturedBrands = [];

        foreach ($featuredBrands as $b) {                

            $aModifiedBrand = new stdClass;
            $aModifiedBrand->id = $b->id;
            $aModifiedBrand->name = $b->name;
            $aModifiedBrand->featuredProductPhotoUrl = $b->products[0]->productPhotoUrls[0];

            $modifiedFeaturedBrands[] = $aModifiedBrand;
        }
        

        return [
            'isResultOk' => true,
            'comment' => "CLASS: ProductController, METHOD: featured()",
            'objs' => [
                'featuredBrands' => $modifiedFeaturedBrands
            ]
        ];
    }



    public function index(Request $request)
    {

        //
        $validatedData = $request->validate([
            'page' => 'nullable|numeric',
            'search' => 'nullable|string',
            'selectedBrandIds' => 'nullable',
            'selectedCategoryId' => 'nullable|numeric'
        ]);


        //
        $numOfProductsPerPage = 9;
        $currentPageNum = isset($validatedData['page']) ? $validatedData['page'] : 1;
        $numOfSkippedItems = ($currentPageNum - 1) * $numOfProductsPerPage;
        $selectedBrandIds = isset($validatedData['selectedBrandIds']) ? $validatedData['selectedBrandIds'] : null;
        $selectedCategoryId = isset($validatedData['selectedCategoryId']) ? $validatedData['selectedCategoryId'] : null;
        $products = [];
        $numOfProductsForQuery = 0;


        if (isset($selectedCategoryId)) {
            $category = Category::find($selectedCategoryId);

            if (isset($selectedBrandIds)) {
                $products = $category->products()->whereIn('brand_id', $selectedBrandIds)->skip($numOfSkippedItems)->take($numOfProductsPerPage)->get();
                $numOfProductsForQuery = count($category->products()->whereIn('brand_id', $selectedBrandIds)->get());
            } else {
                $products = $category->products()->skip($numOfSkippedItems)->take($numOfProductsPerPage)->get();
                $numOfProductsForQuery = count($category->products()->get());
            }
        } else {
            if (isset($selectedBrandIds)) {
                $products = Product::whereIn('brand_id', $selectedBrandIds)->skip($numOfSkippedItems)->take($numOfProductsPerPage)->get();
                $numOfProductsForQuery = count(Product::whereIn('brand_id', $selectedBrandIds)->get());
            } else {
                $products = Product::skip($numOfSkippedItems)->take($numOfProductsPerPage)->get();
                $numOfProductsForQuery = count(Product::all());
            }
        }



        $numOfPages = $numOfProductsForQuery / ($numOfProductsPerPage * 1.0);
        $roundedNumOfPages = ceil($numOfPages);

        $paginationData = [
            'numOfProductsForQuery' => $numOfProductsForQuery,
            'numOfPages' => $numOfPages,
            'roundedNumOfPages' => $roundedNumOfPages,
            'currentPageNum' => $currentPageNum,
            'numOfSkippedItems' => $numOfSkippedItems
        ];


        //
        return [
            'isResultOk' => true,
            'comment' => "CLASS: ProductController, METHOD: index()",
            'products' => $products,
            'objs' => ProductResource::collection($products),
            'paginationData' => $paginationData,
        ];
    }
}
