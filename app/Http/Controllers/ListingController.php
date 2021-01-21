<?php

namespace App\Http\Controllers;

use App\Team;
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
        $selectedBrandIds = isset($validatedData['brands']) ? $validatedData['brands'] : null;
        $selectedCategoryId = isset($validatedData['category']) ? $validatedData['category'] : null;
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
            'completeUrlQuery' => 'nullable|string|max:128',
            'page' => 'nullable|numeric',
            'search' => 'nullable|string',
            'brands' => 'nullable|array',
            'category' => 'nullable|numeric'
        ]);


        $completeUrlQuery = $validatedData['completeUrlQuery'];
        $dataFromQuery = [];
        $retrievedDataFrom = 'cache';

        if (Cache::has($completeUrlQuery)) {
            $dataFromQuery = Cache::get($completeUrlQuery);
        } else {
            //ish
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
        $teams = [];
        $retrievedDataFrom = "db";

        if (Cache::has('brands') && Cache::has('categories') && Cache::has('teams')) {
            $brands = Cache::get('brands');
            $categories = Cache::get('categories');
            $teams = Cache::get('teams');
            $retrievedDataFrom = 'cache';
        } else {
            $brands = Brand::all();
            $categories = Category::all();
            $teams = Team::all();
            //ish

            Cache::put('brands', $brands, now()->addWeeks(1));
            Cache::put('categories', $categories, now()->addWeeks(1));
            Cache::put('teams', $teams, now()->addWeeks(1));
        }

        return [
            'objs' => [
                'brands' => $brands,
                'categories' => $categories,
                'teams' => $teams,
                'retrievedDataFrom' => $retrievedDataFrom
            ]
        ];
    }
}
