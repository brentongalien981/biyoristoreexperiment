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

        // 1)
        $validatedData = $request->validate([
            'page' => 'nullable|numeric',
            'search' => 'nullable|string',
        ]);


        //
        $numOfProductsPerPage = 9.0;
        $numOfProducts = count(Product::all());
        $numOfPages = $numOfProducts / $numOfProductsPerPage;
        $roundedNumOfPages = ceil($numOfPages);

        $paginationData = [
            'numOfProducts' => $numOfProducts,
            'numOfPages' => $numOfPages,
            'roundedNumOfPages' => $roundedNumOfPages,
            'currentPageNum' => $validatedData['page']
        ];


        //
        return [
            'isResultOk' => true,
            'comment' => "CLASS: ProductController, METHOD: index()",
            'objs' => ProductResource::collection(Product::take(9)->get()),
            'paginationData' => $paginationData,
            'validatedData' => $validatedData,
        ];
    }
}
