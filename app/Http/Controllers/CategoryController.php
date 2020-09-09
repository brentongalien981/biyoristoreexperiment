<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return [
            'isResultOk' => true,
            'comment' => "CLASS: CategoryController, METHOD: index()",
            'objs' => Category::all()
        ];
    }
}
