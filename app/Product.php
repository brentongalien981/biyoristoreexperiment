<?php

namespace App;

use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\MyHelpers\Cache\CacheObjectsLifespanManager;

/**
 * buy_price is display-price of product on seller's website (w/o shipping-fee, shipping-fee-tax, item-tax)
 * sell_price is buy-price + intended-profit-margin (profit_margin is in Filipino "patong")
 * restock_days is the number of days it takes for ASB Inc. to buy the product from seller and for the product to arrive
 *      at ASB Inc.
 */

class Product extends Model
{
    public const NUM_OF_PRODUCTS_PER_PAGE = 9;

    public const SORT_BY_NAME_ASC = 1;
    public const SORT_BY_NAME_DESC = 2;
    public const SORT_BY_PRICE_ASC = 3;
    public const SORT_BY_PRICE_DESC = 4;



    public static function getProductFromCache($productId, $retrieveJsonResource = false) {

        $cacheKey = ($retrieveJsonResource ? 'productResource' : 'product') . '?id=' . $productId;

        $p = Cache::store('redisreader')->get($cacheKey);
        $shouldReferenceObjFromDb = false;

        if ($p) {
            if (CacheObjectsLifespanManager::shouldRefresh('product', $p)) {
                $shouldReferenceObjFromDb = true;
            }
        } else { $shouldReferenceObjFromDb = true; }


        if ($shouldReferenceObjFromDb) {
            $p = self::find($productId);
        }

        if (isset($p)) {
            $p = ($retrieveJsonResource ? new ProductResource($p) : $p);
            $p->lastRefreshedInSec = $p->lastRefreshedInSec ?? getdate()[0];
            Cache::store('redisprimary')->put($cacheKey, $p, now()->addDays(1));
        }


        return [
            'mainData' => $p
        ];
    }



    public function reviews()
    {
        return $this->hasMany('App\Review');
    }



    public function team()
    {
        return $this->belongsTo('App\Team');
    }



    public function sellers()
    {
        return $this->belongsToMany('App\Seller')->withPivot('id', 'sell_price', 'discount_sell_price', 'restock_days', 'quantity');
    }



    public function productItem()
    {
        return $this->belongsTo('App\ProductItem');
    }

    public function productPhotoUrls()
    {
        return $this->hasMany('App\ProductPhotoUrl');
    }

    public function brand()
    {
        return $this->belongsTo('App\Brand');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category', 'product_category');
    }


    
    public static function readSearchedProductsWithParams($params = [])
    {
        $currentPageNum = $params['pageNum'] ?? 1;
        $numOfSkippedItems = ($currentPageNum - 1) * self::NUM_OF_PRODUCTS_PER_PAGE;


        $allQualifiedProductIds = [];
        $searchPhraseQ = '%' . $params['searchPhrase'] . '%';
        $qualifiedProductIds = Product::where('name', 'LIKE', $searchPhraseQ)->pluck('id')->toArray();
        $allQualifiedProductIds = array_merge($allQualifiedProductIds, $qualifiedProductIds);

        
        $qualifiedTeams = Team::where('name', 'LIKE', $searchPhraseQ)->get();
        foreach ($qualifiedTeams as $t) {
            $qualifiedProductIds = $t->products()->whereNotIn('id', $allQualifiedProductIds)->pluck('id')->toArray();
            $allQualifiedProductIds = array_merge($allQualifiedProductIds, $qualifiedProductIds);
        }


        $qualifiedCategories = Category::where('name', 'LIKE', $searchPhraseQ)->get();
        foreach ($qualifiedCategories as $c) {
            $qualifiedProductIds = $c->products()->whereNotIn('id', $allQualifiedProductIds)->pluck('id')->toArray();
            $allQualifiedProductIds = array_merge($allQualifiedProductIds, $qualifiedProductIds);
        }


        $qualifiedBrands = Brand::where('name', 'LIKE', $searchPhraseQ)->get();
        foreach ($qualifiedBrands as $b) {
            $qualifiedProductIds = $b->products()->whereNotIn('id', $allQualifiedProductIds)->pluck('id')->toArray();
            $allQualifiedProductIds = array_merge($allQualifiedProductIds, $qualifiedProductIds);
        }


        $numOfProductsForQuery = count($allQualifiedProductIds);
        $products = Product::whereIn('id', $allQualifiedProductIds)->skip($numOfSkippedItems)->take(self::NUM_OF_PRODUCTS_PER_PAGE)->get();
        $products = ProductResource::collection($products);

        $numOfPages = $numOfProductsForQuery / (self::NUM_OF_PRODUCTS_PER_PAGE * 1.0);
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



    public static function readProductsWithParams($params = [])
    {
        $numOfProductsPerPage = self::NUM_OF_PRODUCTS_PER_PAGE;
        $currentPageNum = isset($params['page']) ? $params['page'] : 1;
        $numOfSkippedItems = ($currentPageNum - 1) * $numOfProductsPerPage;
        $selectedBrandIds = isset($params['brands']) ? $params['brands'] : null;
        $selectedTeamIds = isset($params['teams']) ? $params['teams'] : null;
        $selectedCategoryId = isset($params['category']) ? $params['category'] : null;
        $sortByCodeVal = isset($params['sort']) ? $params['sort'] : null;
        $productsEloquentBuilder = self::where('id', '>', 0);
        $products = [];
        $numOfProductsForQuery = 0; // Number of all products for that query without restriction of the page number.



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
            case self::SORT_BY_NAME_DESC:
                $productsEloquentBuilder = $productsEloquentBuilder->orderByDesc('name');
                break;
            default:
                $productsEloquentBuilder = $productsEloquentBuilder->orderBy('name');
                break;
        }


        $numOfProductsForQuery = count($productsEloquentBuilder->get());
        $products = $productsEloquentBuilder->skip($numOfSkippedItems)->take($numOfProductsPerPage)->get();
        $products = ProductResource::collection($products);


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
}
