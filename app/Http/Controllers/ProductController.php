<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function featured() {
        return [
            'isResultOk' => true,
            'comment' => "CLASS: ProductController, METHOD: featured()",
            'objs' => ProductResource::collection(Product::take(9)->get())
        ];
    }



    public function index() {
        return [
            'isResultOk' => true,
            'comment' => "CLASS: ProductController, METHOD: index()",
            'objs' => ProductResource::collection(Product::take(9)->get())
        ];
    }
}
