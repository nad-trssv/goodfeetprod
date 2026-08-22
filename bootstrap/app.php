<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureAllAppointmentsScope;
use App\Http\Middleware\EnsureSuperAdminRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => EnsurePermission::class,
            'scope.all' => EnsureAllAppointmentsScope::class,
            'super-admin-role' => EnsureSuperAdminRole::class,
        ]);
        $middleware->append(MaintenanceMode::class);
        $middleware->web(append:[
            SetLocale::class,
        ]);
        $middleware->appendToGroup('admin', [
            AdminMiddleware::class,
        ]);
        $middleware->appendToGroup('super-admin', [
            SuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash(['credentials']);
    })->create();
