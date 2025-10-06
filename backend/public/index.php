<?php
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require $vendorAutoload;
} else {
    require __DIR__ . '/../src/Core/Autoloader.php';
    (new App\Core\Autoloader(__DIR__ . '/../src'))->register();
}

use App\Core\Application;
use App\Http\Request;
use App\Controllers\AppointmentsController;
use App\Controllers\AuthController;
use App\Controllers\ChatController;
use App\Controllers\CouponsController;
use App\Controllers\DashboardController;
use App\Controllers\GamificationController;
use App\Controllers\NotificationsController;
use App\Controllers\ProfessionalsController;
use App\Controllers\ReviewsController;
use App\Controllers\SearchController;

$app = Application::getInstance(dirname(__DIR__));
$container = $app->container();
$router = $app->router();

$router->add('GET', '/api/v1/health', fn(Request $request) => ['status' => 'ok', 'ts' => time()]);

$router->add('POST', '/api/v1/auth/register', [$container->get(AuthController::class), 'register']);
$router->add('POST', '/api/v1/auth/login', [$container->get(AuthController::class), 'login']);
$router->add('GET', '/api/v1/auth/profile', [$container->get(AuthController::class), 'profile']);

$router->add('GET', '/api/v1/search', [$container->get(SearchController::class), 'index']);
$router->add('GET', '/api/v1/specialties', [$container->get(SearchController::class), 'specialties']);
$router->add('GET', '/api/v1/subspecialties', [$container->get(SearchController::class), 'subspecialties']);

$router->add('GET', '/api/v1/professionals/:id', [$container->get(ProfessionalsController::class), 'show']);
$router->add('GET', '/api/v1/professionals/:id/availability', [$container->get(ProfessionalsController::class), 'availability']);

$router->add('POST', '/api/v1/appointments', [$container->get(AppointmentsController::class), 'create']);
$router->add('PATCH', '/api/v1/appointments/:id', [$container->get(AppointmentsController::class), 'update']);
$router->add('POST', '/api/v1/appointments/:id/checkin', [$container->get(AppointmentsController::class), 'checkin']);
$router->add('GET', '/api/v1/appointments/:id/timeline', [$container->get(AppointmentsController::class), 'timeline']);

$router->add('POST', '/api/v1/reviews', [$container->get(ReviewsController::class), 'create']);
$router->add('GET', '/api/v1/professionals/:id/reviews', [$container->get(ReviewsController::class), 'listByProfessional']);

$router->add('GET', '/api/v1/coupons', [$container->get(CouponsController::class), 'index']);
$router->add('GET', '/api/v1/coupons/validate', [$container->get(CouponsController::class), 'validate']);
$router->add('POST', '/api/v1/coupons/redeem', [$container->get(CouponsController::class), 'redeem']);

$router->add('POST', '/api/v1/chat/threads', [$container->get(ChatController::class), 'createThread']);
$router->add('POST', '/api/v1/chat/messages', [$container->get(ChatController::class), 'postMessage']);
$router->add('GET', '/api/v1/chat/threads/:id/messages', [$container->get(ChatController::class), 'listMessages']);

$router->add('GET', '/api/v1/gamification/summary', [$container->get(GamificationController::class), 'summary']);
$router->add('POST', '/api/v1/gamification/events', [$container->get(GamificationController::class), 'register']);

$router->add('GET', '/api/v1/dashboard/patient', [$container->get(DashboardController::class), 'patient']);
$router->add('GET', '/api/v1/dashboard/professional', [$container->get(DashboardController::class), 'professional']);
$router->add('GET', '/api/v1/dashboard/admin', [$container->get(DashboardController::class), 'admin']);

$router->add('GET', '/api/v1/notifications', [$container->get(NotificationsController::class), 'index']);
$router->add('POST', '/api/v1/notifications/test', [$container->get(NotificationsController::class), 'test']);
$router->add('POST', '/api/v1/webhooks/whatsapp', [$container->get(NotificationsController::class), 'whatsappWebhook']);

$router->dispatch();
