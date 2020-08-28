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
            'objs' => ProductResource::collection(Product::take(2)->get())
        ];
    }
}
