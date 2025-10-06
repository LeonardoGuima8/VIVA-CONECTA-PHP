<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\CouponService;

class CouponsController extends ApiController
{
    private CouponService $coupons;

    public function __construct(CouponService $coupons)
    {
        $this->coupons = $coupons;
    }

    public function index(Request $request)
    {
        $filters = [
            'clinic_id' => $request->query('clinic_id'),
            'category' => $request->query('category'),
        ];
        $items = $this->coupons->list(array_filter($filters));
        return $this->json(['data' => $items]);
    }

    public function validate(Request $request)
    {
        $code = $request->query('code') ?? $request->input('code');
        if (!$code) {
            return $this->json(['error' => 'code is required'], 422);
        }
        $result = $this->coupons->validate($code);
        return $this->json($result);
    }

    public function redeem(Request $request)
    {
        $data = $this->coupons->redeem($request->input());
        return $this->created(['data' => $data]);
    }
}
