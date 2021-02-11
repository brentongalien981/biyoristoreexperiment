<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReviewResource;
use App\Product;
use App\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function test2()
    {

    }



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



    public function read(Request $r)
    {
        sleep(3);
        $validatedData = $r->validate([
            'requestUrlQ' => 'nullable|string|max:128',
            'productId' => 'nullable|numeric',
            'batchNum' => 'nullable|numeric',
        ]);


        $p = Product::find($validatedData['productId']);
        $allReviews = $p->reviews;
        $numOfReviewsPerBatch = 15;
        $numOfSkippedReviews = ($validatedData['batchNum'] - 1) * $numOfReviewsPerBatch;

        $chunkReviews = $p->reviews()->skip($numOfSkippedReviews)->take($numOfReviewsPerBatch)->get();
        $chunkReviews = ReviewResource::collection($chunkReviews);

        $avgRating = null;

        if ($validatedData['batchNum'] == 1) {
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
                'msg' => 'In CLASS: ReviewController, METHOD: read()',
                'reviews' => $chunkReviews,
                'avgRating' => $avgRating
            ]
        ];
    }
}
