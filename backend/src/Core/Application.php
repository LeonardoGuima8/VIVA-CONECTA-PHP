<?php
namespace App\Core;

use App\Config\Env;
use App\Repositories\AppointmentRepository;
use App\Repositories\CouponRepository;
use App\Repositories\GamificationRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ProfessionalRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\UserRepository;
use App\Services\AppointmentService;
use App\Services\AuthService;
use App\Services\ChatService;
use App\Services\CouponService;
use App\Services\DashboardService;
use App\Services\GamificationService;
use App\Services\NotificationService;
use App\Services\ProfessionalService;
use App\Services\ReviewService;
use App\Services\SearchService;
use App\Services\SupabaseAuthService;
use App\Services\SupabaseService;

class Application
{
    private static ?self $instance = null;

    private Container $container;
    private Router $router;
    private string $basePath;

    private function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->container = new Container();
        $this->router = new Router();
        $this->bootstrap();
    }

    public static function getInstance(string $basePath): self
    {
        if (self::$instance === null) {
            self::$instance = new self($basePath);
        }
        return self::$instance;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function container(): Container
    {
        return $this->container;
    }

    private function bootstrap(): void
    {
        $envPath = $this->basePath . DIRECTORY_SEPARATOR . '.env';
        if (is_file($envPath)) {
            Env::load($envPath);
        }
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

        $this->registerBindings();
    }

    private function registerBindings(): void
    {
        $this->container->set(SupabaseService::class, fn($c) => new SupabaseService());
        $this->container->set(SupabaseAuthService::class, fn($c) => new SupabaseAuthService());

        $this->container->set(UserRepository::class, fn($c) => new UserRepository($c->get(SupabaseService::class)));
        $this->container->set(ProfessionalRepository::class, fn($c) => new ProfessionalRepository($c->get(SupabaseService::class)));
        $this->container->set(AppointmentRepository::class, fn($c) => new AppointmentRepository($c->get(SupabaseService::class)));
        $this->container->set(ReviewRepository::class, fn($c) => new ReviewRepository($c->get(SupabaseService::class)));
        $this->container->set(NotificationRepository::class, fn($c) => new NotificationRepository($c->get(SupabaseService::class)));
        $this->container->set(CouponRepository::class, fn($c) => new CouponRepository($c->get(SupabaseService::class)));
        $this->container->set(GamificationRepository::class, fn($c) => new GamificationRepository($c->get(SupabaseService::class)));

        $this->container->set(AuthService::class, fn($c) => new AuthService(
            $c->get(UserRepository::class),
            $c->get(SupabaseAuthService::class),
            $c->get(SupabaseService::class)
        ));

        $this->container->set(SearchService::class, fn($c) => new SearchService(
            $c->get(SupabaseService::class),
            $c->get(ProfessionalRepository::class)
        ));

        $this->container->set(ProfessionalService::class, fn($c) => new ProfessionalService(
            $c->get(ProfessionalRepository::class),
            $c->get(SupabaseService::class)
        ));

        $this->container->set(AppointmentService::class, fn($c) => new AppointmentService(
            $c->get(AppointmentRepository::class),
            $c->get(SupabaseService::class)
        ));

        $this->container->set(ChatService::class, fn($c) => new ChatService($c->get(SupabaseService::class)));
        $this->container->set(ReviewService::class, fn($c) => new ReviewService(
            $c->get(ReviewRepository::class),
            $c->get(SupabaseService::class)
        ));

        $this->container->set(NotificationService::class, fn($c) => new NotificationService(
            $c->get(NotificationRepository::class),
            $c->get(SupabaseService::class)
        ));

        $this->container->set(CouponService::class, fn($c) => new CouponService(
            $c->get(CouponRepository::class),
            $c->get(SupabaseService::class)
        ));

        $this->container->set(GamificationService::class, fn($c) => new GamificationService(
            $c->get(GamificationRepository::class),
            $c->get(SupabaseService::class)
        ));

        $this->container->set(DashboardService::class, fn($c) => new DashboardService(
            $c->get(SupabaseService::class)
        ));
    }
}
