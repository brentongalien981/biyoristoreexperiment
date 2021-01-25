<?php

namespace App\Http\Controllers;

use App\Team;
use App\Brand;
use App\Product;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ProductResource;

class ListingController extends Controller
{
    private const NUM_OF_PRODUCTS_PER_PAGE = 9;

    private const SORT_BY_NAME_ASC = 1;
    private const SORT_BY_NAME_DESC = 2;
    private const SORT_BY_PRICE_ASC = 3;
    private const SORT_BY_PRICE_DESC = 4;



    public function test()
    {
        $q = DB::table('product_seller')
            ->select('product_id')
            ->orderByRaw('LEAST(sell_price, IFNULL(discount_sell_price, sell_price)) ASC');

        $qResult = $q->get();

            

        $qUnique = $qResult->unique();
        $maxQueriedProducts = $qUnique->count();
        $pageNum = 1;


        $productIds = [];
        foreach ($qUnique as $entry) {
            $productIds[] = $entry->product_id;
        }

        return [
            'qResult' => $qResult,
            'qUnique' => $qUnique,
            'maxQueriedProducts' => $maxQueriedProducts,
            'qUnique-Type' => gettype($qUnique),
            'productIds' => $productIds
        ];
    }



    public function readDataFromQuery($validatedData)
    {
        $numOfProductsPerPage = self::NUM_OF_PRODUCTS_PER_PAGE;
        $currentPageNum = isset($validatedData['page']) ? $validatedData['page'] : 1;
        $numOfSkippedItems = ($currentPageNum - 1) * $numOfProductsPerPage;
        $selectedBrandIds = isset($validatedData['brands']) ? $validatedData['brands'] : null;
        $selectedTeamIds = isset($validatedData['teams']) ? $validatedData['teams'] : null;
        $selectedCategoryId = isset($validatedData['category']) ? $validatedData['category'] : null;
        $sortByCodeVal = isset($validatedData['sort']) ? $validatedData['sort'] : null;
        $productsEloquentBuilder = Product::where('id', '>', 0);
        $products = [];
        $numOfProductsForQuery = 0; // Number of all products for that query without restriction of the page number.



        //
        if (isset($selectedCategoryId)) {
            $category = Category::find($selectedCategoryId);
            $productsEloquentBuilder = $category->products();
        }


        if (isset($selectedBrandIds)) {
            $productsEloquentBuilder = $productsEloquentBuilder->whereIn('brand_id', $selectedBrandIds);
        }


        if (isset($selectedTeamIds)) {
            $productsEloquentBuilder = $productsEloquentBuilder->whereIn('team_id', $selectedTeamIds);
        }


        switch ($sortByCodeVal) {
            case self::SORT_BY_NAME_ASC:
                $productsEloquentBuilder = $productsEloquentBuilder->orderBy('name');
                break;
            case self::SORT_BY_NAME_DESC:
                $productsEloquentBuilder = $productsEloquentBuilder->orderByDesc('name');
                break;
            case self::SORT_BY_PRICE_ASC:
                // TODO
                break;
            case self::SORT_BY_PRICE_DESC:
                // TODO
                break;
            default:
                $productsEloquentBuilder = $productsEloquentBuilder->orderBy('name');
                break;
        }


        $numOfProductsForQuery = count($productsEloquentBuilder->get());
        $products = $productsEloquentBuilder->skip($numOfSkippedItems)->take($numOfProductsPerPage)->get();


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
            'teams' => 'nullable|array',
            'category' => 'nullable|numeric',
            'sort' => 'nullable|numeric'
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
