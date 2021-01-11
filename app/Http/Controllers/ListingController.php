<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Product;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ProductResource;

class ListingController extends Controller
{
    private const NUM_OF_PRODUCTS_PER_PAGE = 9;



    public function readDataFromQuery($validatedData)
    {
        $numOfProductsPerPage = self::NUM_OF_PRODUCTS_PER_PAGE;
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


        return [
            'products' => $products,
            'paginationData' => [
                'numOfProductsForQuery' => $numOfProductsForQuery,
                'numOfPages' => $numOfPages,
                'roundedNumOfPages' => $roundedNumOfPages,
                'currentPageNum' => $currentPageNum,
                'numOfSkippedItems' => $numOfSkippedItems
            ]
        ];
    }

    public function readProducts(Request $request)
    {

        $validatedData = $request->validate([
            'completeUrlQuery' => 'nullable|string',
            'page' => 'nullable|numeric',
            'search' => 'nullable|string',
            'selectedBrandIds' => 'nullable',
            'selectedCategoryId' => 'nullable|numeric'
        ]);


        $completeUrlQuery = $validatedData['completeUrlQuery'];
        $dataFromQuery = [];
        $retrievedDataFrom = 'cache';

        if (Cache::has($completeUrlQuery)) {
            $dataFromQuery = Cache::get($completeUrlQuery);
        } else {
            $dataFromQuery = $this->readDataFromQuery($validatedData);
            $dataFromQuery['products'] = ProductResource::collection($dataFromQuery['products']);
            Cache::put($completeUrlQuery, $dataFromQuery, now()->addHours(6));
            $retrievedDataFrom = 'db';
        }


        return [
            'objs' => [
                'products' => $dataFromQuery['products'],
                'paginationData' => $dataFromQuery['paginationData'],
                'retrievedDataFrom' => $retrievedDataFrom
            ]
        ];
    }



    public function readFilters()
    {

        $brands = [];
        $categories = [];
        $retrievedDataFrom = "db";

        if (Cache::has('brands') && Cache::has('categories')) {
            $brands = Cache::get('brands');
            $categories = Cache::get('categories');
            $retrievedDataFrom = 'cache';
        } else {
            $brands = Brand::all();
            $categories = Category::all();

            Cache::put('brands', $brands, now()->addWeeks(1));
            Cache::put('categories', $categories, now()->addWeeks(1));
        }

        return [
            'objs' => [
                'brands' => Brand::all(),
                'categories' => Category::all(),
                'retrievedDataFrom' => $retrievedDataFrom
            ]
        ];
    }
}
