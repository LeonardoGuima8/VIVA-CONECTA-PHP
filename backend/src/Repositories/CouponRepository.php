<?php
namespace App\Repositories;

class CouponRepository extends BaseRepository
{
    public function __construct($supabase)
    {
        parent::__construct($supabase, 'coupons');
    }

    public function activeForClinic(string $clinicId): array
    {
        return $this->supabase->select($this->table, [
            'filter' => [
                'clinic_id' => 'eq.' . $clinicId,
                'active' => 'eq.true',
            ],
            'order' => [['column' => 'valid_to', 'ascending' => false]],
        ]);
    }

    public function validate(string $code): array
    {
        return $this->supabase->select($this->table, [
            'filter' => [
                'code' => 'eq.' . strtoupper($code),
                'active' => 'eq.true',
            ],
            'limit' => 1,
        ]);
    }
}
