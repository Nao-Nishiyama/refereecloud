<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // モデルポリシーがあればここに
    ];

    public function boot(): void
    {
        // 役割
        Gate::define('admin', fn(User $user) => $user->role_id === User::ADMIN_ROLE_ID);
        Gate::define('committee', fn(User $user) => $user->role_id === User::COMMITTEE_ROLE_ID);
        Gate::define('chief', fn(User $user) => $user->role_id === User::CHIEF_ROLE_ID);

        // 審判員系
        Gate::define('referees.create', function (User $user) {
            return in_array($user->role_id, [
                User::ADMIN_ROLE_ID,
                User::COMMITTEE_ROLE_ID,
                User::CHIEF_ROLE_ID,
            ], true);
        });
        
        Gate::define('referees.delete', fn(User $user) =>
            in_array($user->role_id, [User::ADMIN_ROLE_ID, User::COMMITTEE_ROLE_ID, User::CHIEF_ROLE_ID], true)
        );

        Gate::define('referees.restore', fn(User $user) =>
            $user->role_id === User::ADMIN_ROLE_ID
        );

        Gate::define('referees.viewTrashed', fn(User $user) =>
            in_array($user->role_id, [User::ADMIN_ROLE_ID, User::COMMITTEE_ROLE_ID], true)
        );

        Gate::define('referees.approve', fn(User $user) =>
            $user->role_id === User::ADMIN_ROLE_ID
        );
    }
}