<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\ReviewService;
use InvalidArgumentException;

class ReviewsController extends ApiController
{
    private ReviewService $reviews;

    public function __construct(ReviewService $reviews)
    {
        $this->reviews = $reviews;
    }

    public function create(Request $request)
    {
        try {
            $review = $this->reviews->create($request->input());
            return $this->created(['data' => $review]);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function listByProfessional(Request $request, array $params)
    {
        $reviews = $this->reviews->listForProfessional($params['id']);
        return $this->json(['data' => $reviews]);
    }
}
