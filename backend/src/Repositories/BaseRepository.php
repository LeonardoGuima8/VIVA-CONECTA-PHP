<?php
namespace App\Repositories;

use App\Services\SupabaseService;

abstract class BaseRepository
{
    protected SupabaseService $supabase;
    protected string $table;

    public function __construct(SupabaseService $supabase, string $table)
    {
        $this->supabase = $supabase;
        $this->table = $table;
    }

    public function all(array $options = []): array
    {
        return $this->supabase->select($this->table, $options);
    }

    public function findById(string $id, array $options = []): array
    {
        $options['filter']['id'] = 'eq.' . $id;
        $options['limit'] = 1;
        return $this->supabase->select($this->table, $options);
    }

    public function insert(array $payload): array
    {
        return $this->supabase->insert($this->table, [$payload]);
    }

    public function update(string $id, array $payload): array
    {
        return $this->supabase->update($this->table, ['id' => 'eq.' . $id], $payload);
    }

    public function delete(string $id): array
    {
        return $this->supabase->delete($this->table, ['id' => 'eq.' . $id]);
    }
}
