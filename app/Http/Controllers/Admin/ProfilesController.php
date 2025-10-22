<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Referee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\License;
use App\Models\Organization;
use App\Models\Prefecture;

class ProfilesController extends Controller
{
    private $user;
    private $referee;
    private $organization;
    private $prefecture;
    private $license;
    private $category;

    public function __construct(User $user, Referee $referee, Organization $organization, Prefecture $prefecture, License $license, Category $category)
    {
        $this->user = $user;
        $this->referee = $referee;
        $this->organization = $organization;
        $this->prefecture = $prefecture;
        $this->license = $license;
        $this->category = $category;
    }

    public function edit($id)
    {
        $ref = $this->referee->findOrFail($id);

        $organizations = $this->organization->all();
        $prefectures = $this->prefecture->all();
        $licenses = $this->license->all();
        $categories = $this->category->all();

        $selected_categories = [];
        foreach( $ref->categories as $category_referee ){
            $selected_categories[] = $category_referee->id;
        }

        return view('admin.referees.profiles.edit', compact('ref', 'organizations', 'prefectures', 'licenses', 'categories', 'selected_categories'));
    }

    public function update(Request $request, $id)
    {
        $ref = $this->referee->findOrFail($id);

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
            'remarks'       => ['nullable', 'string', 'max:1000'],
        ]);

        if($ref->user)
        {
            $request->validate([
                'email' => 'required|email|max:255|unique:users,email,' . $ref->user->id,
            ]
            );
            $user = $this->user->findOrFail($ref->user->id);

            $user->surname_kana = $request->surname_kana;
            $user->name_kana    = $request->name_kana;
            $user->email        = $request->email;
            $user->save();

        }
        
        $ref->surname_kanji = $request->surname_kanji;
        $ref->name_kanji    = $request->name_kanji;
        $ref->surname_kana  = $request->surname_kana;
        $ref->name_kana     = $request->name_kana;
        $ref->surname       = $request->surname;
        $ref->name          = $request->name;
        $ref->registration_number = $request->registration_number;
        $ref->prefecture_id = $request->prefecture_id;
        $ref->license_id    = $request->license_id;
        $ref->birth_date    = $request->birth_date;
        $ref->mrs_member_id = $request->mrs_member_id;
        $ref->remarks       = $request->remarks;
        $ref->save();

        $ref->categories()->detach();
        
        if ($request->filled('category')) {
            $category_referee = [];
            foreach($request->category as $category_id){
                $category_referee[] = ['category_id' => $category_id];
            }
            $ref->categories()->attach($category_referee);
        }

        return redirect()->route('admin.profiles.edit', $ref->id);
    }

}
