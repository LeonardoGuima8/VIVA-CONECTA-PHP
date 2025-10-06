<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\ProfessionalService;

class ProfessionalsController extends ApiController
{
    private ProfessionalService $service;

    public function __construct(ProfessionalService $service)
    {
        $this->service = $service;
    }

    public function show(Request $request, array $params)
    {
        $profile = $this->service->detail($params['id']);
        if (!$profile) {
            return $this->json(['error' => 'Professional not found'], 404);
        }

        return $this->json(['data' => $profile]);
    }

    public function availability(Request $request, array $params)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $slots = $this->service->availability($params['id'], $from, $to);
        return $this->json(['data' => $slots]);
    }
}
