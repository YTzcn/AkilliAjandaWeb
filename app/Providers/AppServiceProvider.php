<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use App\Repositories\EventRepository;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use App\Services\EventService;
use App\Services\TaskService;
use App\View\Components\CustomAuthLayout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Observers\EventObserver;
use App\Observers\TaskObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });

        $this->app->bind(EventRepository::class, function ($app) {
            return new EventRepository($app->make(Event::class));
        });

        $this->app->bind(TaskRepository::class, function ($app) {
            return new TaskRepository($app->make(Task::class));
        });

        // Services
        $this->app->bind(EventService::class, function ($app) {
            return new EventService(
                $app->make(EventRepository::class)
            );
        });

        $this->app->bind(TaskService::class, function ($app) {
            return new TaskService(
                $app->make(TaskRepository::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
        
        // Register custom blade components
        Blade::component('custom-auth-layout', CustomAuthLayout::class);

        Event::observe(EventObserver::class);
        Task::observe(TaskObserver::class);
    }
}
