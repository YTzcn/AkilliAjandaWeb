<?php

namespace App\Providers;

use App\Models\Event;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use App\Repositories\EventRepository;
use App\Repositories\NoteRepository;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use App\Services\EventService;
use App\Services\NoteService;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(EventRepository::class, function ($app) {
            return new EventRepository($app->make(Event::class));
        });

        $this->app->bind(TaskRepository::class, function ($app) {
            return new TaskRepository($app->make(Task::class));
        });

        $this->app->bind(NoteRepository::class, function ($app) {
            return new NoteRepository($app->make(Note::class));
        });

        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });

        // Register services
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

        $this->app->bind(NoteService::class, function ($app) {
            return new NoteService(
                $app->make(NoteRepository::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
    }
}
