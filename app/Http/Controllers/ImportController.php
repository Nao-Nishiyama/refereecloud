<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class ImportController extends Controller
{
    public function users(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:xlsx,csv,xls'],
        ]);
        $path = $request->file('file')->store('imports');

        Excel::import(new UsersImport, $path);

        return back()->with('success', 'ユーザーをインポートしました');
    }
}
