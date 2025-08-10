<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    # index() - view the Admin: Users Page
    public function index()
    {
        // withTrashed() - include the soft deleted records in a query's result
        $all_users = $this->user->withTrashed()->latest()->paginate(5);

        return view('admin.users.index')
                ->with('all_users',$all_users);
    }

    # deactivate() - to soft delete the user
    public function deactivate($id)
    {
        $this->user->destroy($id);

        return redirect()->back();
    }

    # activate() - to undelete SoftDeletes column(deleted_at) back to NULL
    public function activate($id)
    {
        // onlyTrashed() - retrieves soft deleted records only
        // restore() - "un-delete" a soft deleted model / set the "deleted_at" column to null
        $this->user->onlyTrashed()->findOrFail($id)->restore();

        return redirect()->back();
    }

    public function search(Request $request)
    {
        $users = $this->user->where('name', 'like', '%' . $request->search . '%')->withTrashed()->latest()->paginate(5); 

        return view('admin.users.search')
                ->with('users', $users)
                ->with('search', $request->search);
    }
}
