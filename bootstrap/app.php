<?php

use App\Http\Middleware\EnsureUserIsChief;
use Illuminate\Foundation\Application;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('admin', [AdminMiddleware::class]);
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // 1) ミドルウェアの別名（alias）
        $middleware->alias([
            'is.chief' => EnsureUserIsChief::class,
            'admin'    => AdminMiddleware::class,   // ← ルートで 'admin' として使える
        ]);

        // 2) （必要なら）独自グループ 'admin' を定義
        //    ※ ここで定義しておくと routes で Route::middleware('admin.group') のように1語で使える
        //    ※ 既にグループがあって “中に追加したい” ときだけ appendToGroup を使う
        // 例: 'admin.group' というグループを作る（auth + web + admin をまとめる）
        $middleware->group('admin.group', [
            'web',
            'auth',
            'admin', // ← 上で alias 済み
        ]);

        // 既存グループ（例: 'web'）の**末尾に**追加したい場合だけ
        // $middleware->appendToGroup('web', [ AdminMiddleware::class ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
