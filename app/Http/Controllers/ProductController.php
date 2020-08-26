<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function featured() {
        return [
            'isResultOk' => true,
            'comment' => "CLASS: ProductController, METHOD: featured()"
        ];
    }
}
