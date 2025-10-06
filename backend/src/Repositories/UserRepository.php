<?php
namespace App\Repositories;

class UserRepository extends BaseRepository
{
    public function __construct($supabase)
    {
        parent::__construct($supabase, 'users');
    }

    public function findByEmail(string $email): array
    {
        return $this->supabase->select($this->table, [
            'filter' => ['email' => 'eq.' . strtolower($email)],
            'limit' => 1,
        ]);
    }

    public function findByPhone(string $phone): array
    {
        return $this->supabase->select($this->table, [
            'filter' => ['phone' => 'eq.' . $phone],
            'limit' => 1,
        ]);
    }
}
