<?php
namespace App\Controllers;

use App\Http\ApiController;
use App\Http\Request;
use App\Services\AppointmentService;
use InvalidArgumentException;

class AppointmentsController extends ApiController
{
    private AppointmentService $appointments;

    public function __construct(AppointmentService $appointments)
    {
        $this->appointments = $appointments;
    }

    public function create(Request $request)
    {
        try {
            $appointment = $this->appointments->create($request->input());
            return $this->created(['data' => $appointment]);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, array $params)
    {
        $updated = $this->appointments->update($params['id'], $request->input());
        return $this->json(['data' => $updated]);
    }

    public function checkin(Request $request, array $params)
    {
        $method = $request->input('method', 'qr');
        $checkin = $this->appointments->checkin($params['id'], $method);
        return $this->json(['data' => $checkin]);
    }

    public function timeline(Request $request, array $params)
    {
        $events = $this->appointments->timeline($params['id']);
        return $this->json(['data' => $events]);
    }
}
