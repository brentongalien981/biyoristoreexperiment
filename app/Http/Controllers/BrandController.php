<?php

namespace App\Http\Controllers;

use App\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        return [
            'isResultOk' => true,
            'comment' => "CLASS: BrandController, METHOD: index()",
            'objs' => Brand::all()
        ];
    }
}
