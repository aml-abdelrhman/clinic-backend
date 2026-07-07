<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // تعريف الـ Aliases (الأسماء المستعارة للميدل وير)
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'check.ownership' => \App\Http\Middleware\EnsureServiceOwnership::class,
            'is_doctor' => \App\Http\Middleware\IsDoctor::class,
        ]);

        // هذا الجزء ضروري جداً لمنع الخطأ 500 وتحويله إلى خطأ 401
        $middleware->redirectGuestsTo(fn (Request $request) => 
            $request->is('api/*') 
                ? response()->json(['message' => 'Unauthenticated.'], 401) 
                : route('login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();