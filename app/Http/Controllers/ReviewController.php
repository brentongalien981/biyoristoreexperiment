<?php

namespace App\Http\Controllers;

use App\Http\BmdHelpers\BmdAuthProvider;
use App\Review;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    /** EXPERIMENT FUNCS */
    public function test()
    {

        $v = [
            'productId' => 2,
            'batchNum' => 1
        ];

        $p = Product::find($v['productId']);
        $allReviews = $p->reviews;
        $numOfReviewsPerBatch = 2;
        $numOfSkippedReviews = ($v['batchNum'] - 1) * $numOfReviewsPerBatch;

        $chunkReviews = $p->reviews()->skip($numOfSkippedReviews)->take($numOfReviewsPerBatch)->get();
        $chunkReviews = ReviewResource::collection($chunkReviews);

        $avgRating = null;

        if ($v['batchNum'] == 1) {
            $totalNumOfProductReviews = count($allReviews);
            $sumOfProductRatings = 0;
            foreach ($allReviews as $r) {
                $sumOfProductRatings += $r->rating;
            }

            if ($sumOfProductRatings != 0) {
                $avgRating = $sumOfProductRatings / $totalNumOfProductReviews;
                $avgRating = round($avgRating, 1);
            }
        }



        return [
            'objs' => [
                'msg' => 'In CLASS: ReviewController, METHOD: test()',
                'reviews' => $chunkReviews,
                'avgRating' => $avgRating
            ]
        ];
    }



    /** MAIN FUNCS */
    public function read(Request $r)
    {
        $validatedData = $r->validate([
            'requestUrlQ' => 'nullable|string|max:128',
            'productId' => 'nullable|numeric',
            'batchNum' => 'nullable|numeric',
        ]);


        $requestUrlQ = $validatedData['requestUrlQ'] ?? 'DEFAULT-REQUEST-URL-Q-FOR-ROUTE=reviews/read';
        $productRatingUrlQ = 'product-rating?productId=' . $validatedData['productId'];
        $retrievedDataFrom = 'db';
        $chunkReviews = [];
        $avgRating = null;

        if (Cache::store('redisreader')->has($requestUrlQ)) {
            $retrievedDataFrom = 'cache';
            $chunkReviews = Cache::store('redisreader')->get($requestUrlQ);
            $avgRating = Cache::store('redisreader')->get($productRatingUrlQ);
        } else {
            /** Get product-reviews. */
            $p = Product::find($validatedData['productId']);
            $allReviews = $p->reviews;
            $numOfReviewsPerBatch = 15;
            $numOfSkippedReviews = ($validatedData['batchNum'] - 1) * $numOfReviewsPerBatch;

            $chunkReviews = $p->reviews()->skip($numOfSkippedReviews)->take($numOfReviewsPerBatch)->get();
            $chunkReviews = ReviewResource::collection($chunkReviews);


            /** Get product-rating. */
            if (Cache::store('redisreader')->has($productRatingUrlQ)) {
                $avgRating = Cache::store('redisreader')->get($productRatingUrlQ);
            } else if ($validatedData['batchNum'] == 1) {
                $totalNumOfProductReviews = count($allReviews);
                $sumOfProductRatings = 0;
                foreach ($allReviews as $r) {
                    $sumOfProductRatings += $r->rating;
                }

                if ($sumOfProductRatings != 0) {
                    $avgRating = $sumOfProductRatings / $totalNumOfProductReviews;
                    $avgRating = round($avgRating, 1);
                }
            }


            /** Save data in cache. */
            Cache::store('redisprimary')->put($requestUrlQ, $chunkReviews, now()->addDays(1));
            Cache::store('redisprimary')->put($productRatingUrlQ, $avgRating, now()->addDays(1));
        }


        return [
            'objs' => [
                'msg' => 'In CLASS: ReviewController, METHOD: read()',
                'reviews' => $chunkReviews,
                'avgRating' => $avgRating,
                'retrievedDataFrom' => $retrievedDataFrom,
            ]
        ];
    }



    public function save(Request $r)
    {
        $v = $r->validate([
            'productId' => 'numeric|min:1',
            'rating' => 'numeric|min:1|max:5',
            'message' => 'string|min:1|max:1024',
        ]);

        $isResultOk = false;


        $review = new Review();
        $review->product_id = $v['productId'];
        $review->user_id = BmdAuthProvider::user()->id;
        $review->rating = $v['rating'];
        $review->message = $v['message'];
        $review->save();

        $isResultOk = true;


        return [
            'isResultOk' => $isResultOk,
            'msg' => 'In CLASS: ReviewController, METHOD: save()',
        ];
    }
}
