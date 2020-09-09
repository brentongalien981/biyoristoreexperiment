<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
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
            'selectedBrandIds' => 'nullable'
        ]);


        //
        $numOfProductsPerPage = 9;
        $currentPageNum = isset($validatedData['page']) ? $validatedData['page'] : 1;
        $numOfSkippedItems = ($currentPageNum - 1) * $numOfProductsPerPage;
        $selectedBrandIds = isset($validatedData['selectedBrandIds']) ? $validatedData['selectedBrandIds'] : null;
        $products = [];
        $numOfProductsForQuery = 0;

        if (isset($selectedBrandIds)) {
            $products = Product::whereIn('brand_id', $selectedBrandIds)->skip($numOfSkippedItems)->take($numOfProductsPerPage)->get();
            $numOfProductsForQuery = count(Product::whereIn('brand_id', $selectedBrandIds)->get());
        } else {
            $products = Product::skip($numOfSkippedItems)->take($numOfProductsPerPage)->get();
            $numOfProductsForQuery = count(Product::all());
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
