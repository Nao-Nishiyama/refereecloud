<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Referee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    private $user;
    private $referee;

    public function __construct(User $user, Referee $referee)
    {
        $this->user = $user;
        $this->referee = $referee;
    }

    public function makeAdmin($id)
    {
        $user = $this->user->findOrFail($id);
        $user->role_id = User::ADMIN_ROLE_ID;
        $user->update();
        return back()->with('status', '更新しました');
    }

    public function makeCommittee($id)
    {
        $user = $this->user->findOrFail($id);
        $user->role_id = User::COMMITTEE_ROLE_ID;
        $user->update();
        return back()->with('status', '更新しました');
    }

    public function makeChief($id)
    {
        $user = $this->user->findOrFail($id);
        $user->role_id = User::CHIEF_ROLE_ID;
        $user->update();
        return back()->with('status', '更新しました');
    }

    public function makeUser($id)
    {
        $user = $this->user->findOrFail($id);
        $user->role_id = User::USER_ROLE_ID;
        $user->update();
        return back()->with('status', '更新しました');
    }


}
