<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Referee;

class EnsureUserIsChief
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // role_id=3 が chief
        if ((int)($user->role_id ?? 0) !== 3) {
            abort(403, 'この操作は審判長のみ可能です。');
        }

        // 自分の Referee レコード経由で所属団体が取れるか？
        $ref = Referee::where('user_id', $user->id)->first();
        if (!$ref || !$ref->organization_id) {
            abort(403, '審判長の所属が未設定です。');
        }

        return $next($request);
    }
}
