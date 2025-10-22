<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\License;
use App\Models\Referee;
use App\Models\Category;
use App\Models\Prefecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\CodeUnit\FunctionUnit;

class ProfileController extends Controller
{
    private $user;
    private $referee;
    private $prefecture;
    private $license;
    private $category;

    public function __construct(User $user, Referee $referee, Prefecture $prefecture, License $license, Category $category)
    {
        $this->user = $user;
        $this->prefecture = $prefecture;
        $this->license = $license;
        $this->category = $category;
    }

    public function show()
    {
        $user = Auth::user();
        return view('users.profile.show', compact('user'));
    }

    public function edit()
    {
        $user = $this->user->findOrFail(Auth::user()->id);
        $all_prefectures = $this->prefecture->all();
        $all_licenses = $this->license->all();
        $all_categories = $this->category->all();

        $selected_categories = [];
        foreach( $user->referee->categories as $category_referee ){
            $selected_categories[] = $category_referee->id;
        }

        return view('users.profile.edit')
                ->with('user', $user)
                ->with('all_prefectures', $all_prefectures)
                ->with('all_licenses', $all_licenses)
                ->with('all_categories', $all_categories)
                ->with('selected_categories', $selected_categories);
    }

    public function update(Request $request)
    {
        $request->validate([
            'surname_kanji' => ['required', 'string', 'max:255'],
            'name_kanji'    => ['required', 'string', 'max:255'],
            'surname_kana'  => ['required', 'string', 'max:255'],
            'name_kana'     => ['required', 'string', 'max:255'],
            'surname'       => ['required', 'string', 'max:255'],
            'name'          => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:255'],
            'prefecture_id' => ['required'],
            'license_id'    => ['required'],
            'birth_date'    => ['required'],
            'email'         => 'required|email|max:255|unique:users,email,' . Auth::user()->id,
            'remarks'       => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $this->user->findOrFail(Auth::user()->id);

        $user->surname_kana = $request->surname_kana;
        $user->name_kana    = $request->name_kana;
        $user->email        = $request->email;
        $user->save();
        
        $referee = $user->referee;
        $referee->surname_kanji = $request->surname_kanji;
        $referee->name_kanji    = $request->name_kanji;
        $referee->surname_kana  = $request->surname_kana;
        $referee->name_kana     = $request->name_kana;
        $referee->surname       = $request->surname;
        $referee->name          = $request->name;
        $referee->registration_number = $request->registration_number;
        $referee->prefecture_id = $request->prefecture_id;
        $referee->license_id    = $request->license_id;
        $referee->birth_date    = $request->birth_date;
        $referee->mrs_member_id = $request->mrs_member_id;
        $referee->remarks       = $request->remarks;
        $referee->save();

        $user->referee->categories()->detach();
        
        if ($request->filled('category')) {
            $category_referee = [];
            foreach($request->category as $category_id){
                $category_referee[] = ['category_id' => $category_id];
            }
            $referee->categories()->attach($category_referee);
        }

        return redirect()->route('profile.show');
    }
}
