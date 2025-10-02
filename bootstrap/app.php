<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php', // lädt console.php (artisan commands)
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Scheduler hier definieren → von schedule:list sicher erkannt
        $schedule->command('moco:sync-projects')->everyFifteenMinutes();
        $schedule->command('moco:sync-projects --full')->dailyAt('03:00');
    })
    ->withCommands([
        \App\Console\Commands\SyncProjectsCommand::class, // registriert den Command
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // optional
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // optional
    })
    ->create();
