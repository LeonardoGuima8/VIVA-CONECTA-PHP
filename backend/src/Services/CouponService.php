<?php
namespace App\Services;

use App\Repositories\CouponRepository;
use App\Support\Validator;

class CouponService
{
    private CouponRepository $coupons;
    private SupabaseService $supabase;

    public function __construct(CouponRepository $coupons, SupabaseService $supabase)
    {
        $this->coupons = $coupons;
        $this->supabase = $supabase;
    }

    public function list(array $filters = []): array
    {
        $options = [
            'filter' => ['active' => 'eq.true'],
            'order' => [['column' => 'valid_to']],
        ];
        if (!empty($filters['clinic_id'])) {
            $options['filter']['clinic_id'] = 'eq.' . $filters['clinic_id'];
        }
        if (!empty($filters['category'])) {
            $options['filter']['category'] = 'eq.' . $filters['category'];
        }
        $response = $this->supabase->select('coupons_view', $options);
        return $response['data'] ?? [];
    }

    public function validate(string $code): array
    {
        $result = $this->coupons->validate($code);
        $coupon = $result['data'][0] ?? null;
        if (!$coupon) {
            return ['valid' => false];
        }

        return ['valid' => true, 'coupon' => $coupon];
    }

    public function redeem(array $input): array
    {
        Validator::require($input, ['coupon_id', 'user_id']);
        $record = [
            'coupon_id' => $input['coupon_id'],
            'user_id' => $input['user_id'],
            'validation_ref' => $input['validation_ref'] ?? null,
        ];
        $result = $this->supabase->insert('coupon_usages', [$record]);
        return $result['data'][0] ?? [];
    }
}
