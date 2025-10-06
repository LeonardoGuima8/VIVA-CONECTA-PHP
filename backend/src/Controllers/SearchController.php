<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\SearchService;

class SearchController extends ApiController
{
    private SearchService $search;

    public function __construct(SearchService $search)
    {
        $this->search = $search;
    }

    public function index(Request $request)
    {
        $criteria = [
            'query' => $request->query('q'),
            'vertical' => $request->query('vertical'),
            'specialty' => $request->query('specialty'),
            'subspecialty' => $request->query('subspecialty'),
            'city' => $request->query('city'),
            'state' => $request->query('state'),
            'limit' => (int) ($request->query('limit') ?? 20),
            'order' => [['column' => 'score', 'ascending' => false]],
        ];

        $results = $this->search->search($criteria);
        return $this->json([
            'query' => $criteria,
            'results' => $results,
        ]);
    }

    public function specialties(Request $request)
    {
        $vertical = $request->query('vertical');
        $items = $this->search->specialties($vertical);
        return $this->json(['data' => $items]);
    }

    public function subspecialties(Request $request)
    {
        $specialty = $request->query('specialty_id');
        $items = $this->search->subspecialties($specialty);
        return $this->json(['data' => $items]);
    }
}
