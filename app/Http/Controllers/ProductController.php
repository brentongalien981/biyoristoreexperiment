<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

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
            'comment' => "CLASS: ProductController, METHOD: show()",
            'objs' => [],
            'product' => new ProductResource($product),
            'relatedProducts' => $relatedProducts,
            'validatedData' => $validatedData
        ];
    }

    public function featured()
    {
        return [
            'isResultOk' => true,
            'comment' => "CLASS: ProductController, METHOD: featured()",
            'objs' => ProductResource::collection(Product::take(9)->get())
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
            'validatedData' => $validatedData,
        ];
    }
}
