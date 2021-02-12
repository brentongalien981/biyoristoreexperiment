<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReviewResource;
use App\Product;
use App\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /** EXPERIMENT FUNCS */
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



    /** MAIN FUNCS */
    public function read(Request $r)
    {
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



    public function save(Request $r) {

        $v = $r->validate([
            'productId' => 'numeric|min:1',
            'rating' => 'numeric|min:1|max:5',
            'message' => 'string|min:1|max:1024',
        ]);


        $review = new Review();
        $review->product_id = $v['productId'];
        $review->user_id = Auth::user()->id;
        $review->rating = $v['rating'];
        $review->message = $v['message'];
        $review->save();


        return [
            'isResultOk' => true,
            'msg' => 'In CLASS: ReviewController, METHOD: save()',
            'objs' => [
                'validatedData' => $v
            ]
        ];
    }
}
