<?php

namespace App\Http\Controllers;

use App\Product;
use Exception;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $r)
    {
        $isResultOk = false;
        $searchParams = [
            'searchPhrase' => $r->searchPhrase,
            'pageNum' => $r->pageNum
        ];
        $searchPageProducts = null;        


        try {
            
            $searchPageProducts = Product::readSearchedProductsWithParams($searchParams);
            $isResultOk = true;
        } catch (\Throwable $th) {
            
        }


        return [
            'isResultOk' => $isResultOk,
            'objs' => [
                'searchPageProducts' => $searchPageProducts
            ],
            // BMD-DELETE
            'requestData' => [
                'searchPhrase' => $r->searchPhrase,
                'pageNum' => $r->pageNum
            ]
        ];
    }
}
