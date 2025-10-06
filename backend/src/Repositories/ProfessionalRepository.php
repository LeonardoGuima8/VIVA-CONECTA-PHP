<?php
namespace App\Repositories;

class ProfessionalRepository extends BaseRepository
{
    public function __construct($supabase)
    {
        parent::__construct($supabase, 'professionals');
    }

    public function availability(string $professionalId, ?string $from = null, ?string $to = null): array
    {
        $options = [
            'filter' => ['professional_id' => 'eq.' . $professionalId],
            'order' => [
                ['column' => 'date'],
                ['column' => 'start_time'],
            ],
        ];

        $bounds = [];
        if ($from) {
            $bounds[] = 'date.gte.' . $from;
        }
        if ($to) {
            $bounds[] = 'date.lte.' . $to;
        }
        if ($bounds) {
            $options['and'] = '(' . implode(',', $bounds) . ')';
        }

        return $this->supabase->select('availability_slots_view', $options);
    }

    public function search(array $criteria): array
    {
        $query = [
            'select' => '*',
            'limit' => $criteria['limit'] ?? 20,
            'order' => $criteria['order'] ?? [['column' => 'rating_average', 'ascending' => false]],
        ];

        $filter = [];
        if (!empty($criteria['city'])) {
            $filter['city'] = 'ilike.' . $criteria['city'] . '%';
        }
        if (!empty($criteria['state'])) {
            $filter['state'] = 'eq.' . strtoupper($criteria['state']);
        }
        if (!empty($criteria['vertical'])) {
            $filter['vertical_segments'] = 'cs.{' . strtolower($criteria['vertical']) . '}';
        }
        if (!empty($criteria['specialty'])) {
            $filter['specialty_slugs'] = 'cs.{' . strtolower($criteria['specialty']) . '}';
        }
        if (!empty($criteria['subspecialty'])) {
            $filter['subspecialty_slugs'] = 'cs.{' . strtolower($criteria['subspecialty']) . '}';
        }

        if (!empty($criteria['query'])) {
            $query['or'] = implode(',', [
                'name.ilike.%' . $criteria['query'] . '%',
                'city.ilike.%' . $criteria['query'] . '%'
            ]);
        }

        if (!empty($filter)) {
            $query['filter'] = $filter;
        }

        return $this->supabase->select('professionals_view', $query);
    }
}
