<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\DashboardService;

class DashboardController extends ApiController
{
    private DashboardService $dashboards;

    public function __construct(DashboardService $dashboards)
    {
        $this->dashboards = $dashboards;
    }

    public function patient(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) {
            return $this->json(['error' => 'user_id is required'], 422);
        }
        $data = $this->dashboards->patient($userId);
        return $this->json(['data' => $data]);
    }

    public function professional(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) {
            return $this->json(['error' => 'user_id is required'], 422);
        }
        $data = $this->dashboards->professional($userId);
        return $this->json(['data' => $data]);
    }

    public function admin()
    {
        $data = $this->dashboards->admin();
        return $this->json(['data' => $data]);
    }
}
