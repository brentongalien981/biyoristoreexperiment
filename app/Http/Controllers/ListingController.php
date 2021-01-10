<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Category;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function readFilters() {
        return [
            'objs' => [
                'brands' => Brand::all(),
                'categories' => Category::all(),
                'retrievedDataFrom' => 'db'
            ]
        ];
    }
}
