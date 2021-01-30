<?php

namespace App\Http\Controllers;

use App\Team;
use App\Brand;
use Exception;
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



    public function getProductIdsSortedByPrice($validatedData)
    {
        // Make use of cache.
        $productIdsSortedByPrice = [
            'key' => 'product-ids-sorted-by-price-asc',
            'val' => null
        ];
        $qForFilterOrderByRaw = 'LEAST(sell_price, IFNULL(discount_sell_price, sell_price)) ASC';
        $returnData['cacheKey'] = 'product-ids-sorted-by-price-asc';

        if ($validatedData['sort'] == self::SORT_BY_PRICE_DESC) {
            $productIdsSortedByPrice['key'] = 'product-ids-sorted-by-price-desc';
            $returnData['cacheKey'] = 'product-ids-sorted-by-price-desc';
            $qForFilterOrderByRaw = 'LEAST(sell_price, IFNULL(discount_sell_price, sell_price)) DESC';
        }



        if (Cache::store('redisreader')->has($productIdsSortedByPrice['key'])) {
            $returnData['productIds'] = Cache::store('redisreader')->get($productIdsSortedByPrice['key']);
            $returnData['retrievedFrom'] = 'cache';
        } else {

            $q = DB::table('product_seller')
                ->select('product_id')
                ->orderByRaw($qForFilterOrderByRaw);

            $returnData['qResult'] = $q->get();


            $returnData['qUnique'] = $returnData['qResult']->unique();
            $returnData['maxQueriedProducts'] = $returnData['qUnique']->count();


            // productIds of all products sorted by price.
            $productIds = [];
            foreach ($returnData['qUnique'] as $entry) {
                $productIds[] = $entry->product_id;
            }

            // Cache the product-ids based on the sort-order.
            Cache::store('redisprimary')->put($productIdsSortedByPrice['key'], $productIds, now()->addHours(6));
            $returnData['productIds'] = $productIds;
            $returnData['retrievedFrom'] = 'db';
        }



        return $returnData;
    }



    public function test_stringReplace()
    {
        $urlQ = "?page=1&brands=1,2,3&sort=2";
        $parsedUrlQ = $this->getUrlQueryWithoutPageFilterVal($urlQ);
        return $parsedUrlQ;
    }



    public function getUrlQueryWithoutPageFilterVal($urlQ)
    {

        try {
            if (strlen($urlQ) === 0) {
                throw new Exception('URL Query has no value.');
            }

            $strToRef = 'page=';
            $strPosOfStrToRemove = strpos($urlQ, $strToRef, 0);

            if (!$strPosOfStrToRemove) {
                return [
                    'msg' => 'String to remove not found...',
                    'val' => $urlQ
                ];
            }

            $posOfFirstAmpAfterStrToRemove = strpos($urlQ, '&', $strPosOfStrToRemove);
            if (!$posOfFirstAmpAfterStrToRemove) {
                // Meaning the "page=[x]" is at the end of the url-query without the "&" at the end.
                $posOfFirstAmpAfterStrToRemove = strlen($urlQ);
            }

            $lengthOfStrToRemove = $posOfFirstAmpAfterStrToRemove - $strPosOfStrToRemove + 1;
            $strToRemove = substr($urlQ, $strPosOfStrToRemove, $lengthOfStrToRemove);
            $val = str_replace($strToRemove, "", $urlQ);

            return [
                'urlQ' => $urlQ,
                'strToRef' => $strToRef,
                'strPosOfStrToRemove' => $strPosOfStrToRemove,
                'posOfFirstAmpAfterStrToRemove' => $posOfFirstAmpAfterStrToRemove,
                'lengthOfStrToRemove' => $lengthOfStrToRemove,
                'strToRemove' => $strToRemove,
                'val' => $val
            ];
        } catch (Exception $e) {
            return [
                'msg' => 'Caught EXCEPTION: ' . $e->getMessage(),
                'val' => 'DEFAULT-URL-QUERY-WITHOUT-PAGE-FILTER'
            ];
        }
    }



    public function getProductIdsForUrlQuery($data)
    {
        // Extract product-ids based on the url-query-exluding-page-filter.
        $dataCacheKey = 'product-ids-for-url-query=' . $data['urlQueryExcludingPageFilter'];
        $returnData['dataCacheKey'] = $dataCacheKey;


        if (Cache::store('redisreader')->has($dataCacheKey)) {
            $returnData['productIds'] = Cache::store('redisreader')->get($dataCacheKey);
            $returnData['retrievedFrom'] = 'cache';
        } else {

            $v = $data['validatedData'];
            $tempProductIdsForUrlQuery = [];

            foreach ($data['productIdsSortedByPrice'] as $sortedProductId) {
                $sortedProduct = Product::find($sortedProductId);

                // Filter by category.
                $categoryFilterPassed = false;
                $selectedCategoryId = $v['category'] ?? null;
                if ($selectedCategoryId) {
                    foreach ($sortedProduct->categories as $c) {
                        if ($c->id == $selectedCategoryId) {
                            $categoryFilterPassed = true;
                            break;
                        }
                    }
                } else {
                    $categoryFilterPassed = true;
                }

                if (!$categoryFilterPassed) {
                    continue;
                }


                // Filter by brand.
                $brandFilterPassed = false;
                $selectedBrands = $v['brands'] ?? null;
                if ($selectedBrands) {
                    foreach ($selectedBrands as $brandId) {
                        if ($brandId == $sortedProduct->brand_id) {
                            $brandFilterPassed = true;
                            break;
                        }
                    }
                } else {
                    $brandFilterPassed = true;
                }

                if (!$brandFilterPassed) {
                    continue;
                }


                // Filter by team.
                $teamFilterPassed = false;
                $selectedTeams = $v['teams'] ?? null;
                if ($selectedTeams) {
                    foreach ($selectedTeams as $teamId) {
                        if ($teamId == $sortedProduct->team_id) {
                            $teamFilterPassed = true;
                            break;
                        }
                    }
                } else {
                    $teamFilterPassed = true;
                }

                if (!$teamFilterPassed) {
                    continue;
                }


                // If all checks passed, add the product-id to array “product-ids-for-url-query”
                $tempProductIdsForUrlQuery[] = $sortedProduct->id;
            }


            $returnData['productIds'] = $tempProductIdsForUrlQuery;
            $returnData['retrievedFrom'] = 'db';
            Cache::store('redisprimary')->put($dataCacheKey, $tempProductIdsForUrlQuery, now()->addHours(6));
        }

        return $returnData;
    }



    public function getListingPageDataForQueryWithPriceSort($params)
    {

        $v = $params['validatedData'];
        $productIdsForUrlQuery = $params['productIdsForUrlQuery'];
        $numOfProductsForQuery = count($productIdsForUrlQuery);
        $numOfPages = $numOfProductsForQuery / (self::NUM_OF_PRODUCTS_PER_PAGE * 1.0);
        $roundedNumOfPages = ceil($numOfPages);
        $currentPageNum = $v['page'];
        $numOfSkippedItems = ($currentPageNum - 1) * self::NUM_OF_PRODUCTS_PER_PAGE;


        $startIndex = $numOfSkippedItems;
        $toBeDisplayedProductIds = [];
        for ($i = 0; $i < self::NUM_OF_PRODUCTS_PER_PAGE; $i++) {
            $currentIndex = $startIndex + $i;
            if ($currentIndex >= $numOfProductsForQuery) {
                break;
            }
            $toBeDisplayedProductIds[] = $productIdsForUrlQuery[$currentIndex];
        }


        $products = [];
        foreach ($toBeDisplayedProductIds as $productId) {
            $products[] = new ProductResource(Product::find($productId));
        }


        $dataForQuery = [
            'products' => $products,
            'paginationData' => [
                'numOfProductsForQuery' => $numOfProductsForQuery,
                'numOfPages' => $numOfPages,
                'roundedNumOfPages' => $roundedNumOfPages,
                'currentPageNum' => $currentPageNum,
                'numOfSkippedItems' => $numOfSkippedItems
            ]
        ];


        return $dataForQuery;
    }



    public function test_readDataFromQueryWithPriceSort()
    {

        $validatedData = [
            'page' => 1,
            'sort' => self::SORT_BY_PRICE_DESC,
            // 'completeUrlQuery' => "?page=2&category=5&sort=3",
            'completeUrlQuery' => "?teams=6,11&sort=4",
            // 'completeUrlQuery' => "?page=2&category=1&brands=1,2,3&sort=1",
            // 'category' => 5,
            // 'brands' => [2],
            'teams' => [6, 11]
        ];


        if (Cache::store('redisreader')->has($validatedData['completeUrlQuery'])) {
            return [
                'products' => Cache::store('redisreader')->get($validatedData['completeUrlQuery']),
                'retrievedDataFrom' => 'cache'
            ];
        }


        $productIdsSortedByPrice = $this->getProductIdsSortedByPrice($validatedData);
        $urlQueryExcludingPageFilter = $this->getUrlQueryWithoutPageFilterVal($validatedData['completeUrlQuery']);

        $productIdsForUrlQuery = $this->getProductIdsForUrlQuery([
            'productIdsSortedByPrice' => $productIdsSortedByPrice['productIds'],
            'urlQueryExcludingPageFilter' => $urlQueryExcludingPageFilter['val'],
            'validatedData' => $validatedData
        ]);

        $listingPageData = $this->getListingPageDataForQueryWithPriceSort([
            'productIdsForUrlQuery' => $productIdsForUrlQuery['productIds'],
            'validatedData' => $validatedData
        ]);



        return [
            'msg' => 'METHOD: test_readDataFromQueryWithPriceSort()',
            'validatedData' => $validatedData,
            'productIdsSortedByPrice' => $productIdsSortedByPrice,
            'urlQueryExcludingPageFilter' => $urlQueryExcludingPageFilter,
            'productIdsForUrlQuery' => $productIdsForUrlQuery,
            'listingPageData' => $listingPageData
        ];
    }



    public function readDataFromQueryWithPriceSort($validatedData)
    {
        $productIdsSortedByPrice = $this->getProductIdsSortedByPrice($validatedData);
        $urlQueryExcludingPageFilter = $this->getUrlQueryWithoutPageFilterVal($validatedData['completeUrlQuery']);

        $productIdsForUrlQuery = $this->getProductIdsForUrlQuery([
            'productIdsSortedByPrice' => $productIdsSortedByPrice['productIds'],
            'urlQueryExcludingPageFilter' => $urlQueryExcludingPageFilter['val'],
            'validatedData' => $validatedData
        ]);

        $listingPageData = $this->getListingPageDataForQueryWithPriceSort([
            'productIdsForUrlQuery' => $productIdsForUrlQuery['productIds'],
            'validatedData' => $validatedData
        ]);


        return [
            'msg' => 'METHOD: readDataFromQueryWithPriceSort()',
            'validatedData' => $validatedData,
            'productIdsSortedByPrice' => $productIdsSortedByPrice,
            'urlQueryExcludingPageFilter' => $urlQueryExcludingPageFilter,
            'productIdsForUrlQuery' => $productIdsForUrlQuery,
            'products' => $listingPageData['products'],
            'paginationData' => $listingPageData['paginationData'],
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
        $sortVal = $validatedData['sort'] ?? self::SORT_BY_NAME_ASC;
        $extraData = null;


        if (Cache::store('redisreader')->has($completeUrlQuery)) {
            $dataFromQuery = Cache::store('redisreader')->get($completeUrlQuery);
        } else {

            $retrievedDataFrom = 'db';

            switch ($sortVal) {
                case self::SORT_BY_PRICE_ASC:
                case self::SORT_BY_PRICE_DESC:
                    $data = $this->readDataFromQueryWithPriceSort($validatedData);
                    $dataFromQuery['products'] = $data['products'];
                    $dataFromQuery['paginationData'] = $data['paginationData'];
                    $extraData['productIdsSortedByPrice'] = $data['productIdsSortedByPrice'];
                    $extraData['urlQueryExcludingPageFilter'] = $data['urlQueryExcludingPageFilter'];
                    $extraData['productIdsForUrlQuery'] = $data['productIdsForUrlQuery'];
                    break;
                default:
                    $dataFromQuery = $this->readDataFromQuery($validatedData);
                    break;
            }

            Cache::store('redisprimary')->put($completeUrlQuery, $dataFromQuery, now()->addHours(6));
        }


        return [
            'objs' => [
                'products' => $dataFromQuery['products'],
                'paginationData' => $dataFromQuery['paginationData'],
                'retrievedDataFrom' => $retrievedDataFrom,
                // 'extraData' => $extraData // FOR-DEBUG
            ]
        ];
    }



    public function readFilters()
    {

        $brands = [];
        $categories = [];
        $teams = [];
        $retrievedDataFrom = "db";

        if (Cache::store('redisreader')->has('brands') && Cache::store('redisreader')->has('categories') && Cache::store('redisreader')->has('teams')) {
            $brands = Cache::store('redisreader')->get('brands');
            $categories = Cache::store('redisreader')->get('categories');
            $teams = Cache::store('redisreader')->get('teams');
            $retrievedDataFrom = 'cache';
        } else {
            $brands = Brand::all();
            $categories = Category::all();
            $teams = Team::all();

            Cache::store('redisprimary')->put('brands', $brands, now()->addWeeks(1));
            Cache::store('redisprimary')->put('categories', $categories, now()->addWeeks(1));
            Cache::store('redisprimary')->put('teams', $teams, now()->addWeeks(1));
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
