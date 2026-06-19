<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\License;
use App\Models\Referee;
use App\Models\Category;
use App\Models\Organization;
use App\Models\Prefecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\CodeUnit\FunctionUnit;

class ProfileController extends Controller
{
    private $user;
    private $referee;
    private $prefecture;
    private $organization;
    private $license;
    private $category;

    public function __construct(User $user, Referee $referee, Prefecture $prefecture, Organization $organization, License $license, Category $category)
    {
        $this->user = $user;
        $this->prefecture = $prefecture;
        $this->organization = $organization;
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
        $all_organizations = $this->organization->all();
        $all_licenses = $this->license->all();
        $all_categories = $this->category->all();

        $selected_categories = [];
        foreach( $user->referee->categories as $category_referee ){
            $selected_categories[] = $category_referee->id;
        }

        return view('users.profile.edit', compact('user', 'all_prefectures', 'all_organizations', 'all_licenses', 'all_categories', 'selected_categories'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'surname_kanji' => ['required', 'string', 'max:255'],
            'name_kanji'    => ['required', 'string', 'max:255'],
            'surname_kana'  => ['required', 'string', 'max:255'],
            'name_kana'     => ['required', 'string', 'max:255'],
            'surname'       => ['required', 'string', 'max:255'],
            'name'          => ['required', 'string', 'max:255'],

            'registration_number' => ['required', 'string', 'max:255'],
            'birth_date'          => ['required', 'date'],
            'gender'              => ['nullable', 'integer', 'in:1,2'],

            'prefecture_id' => ['required', 'integer', 'exists:prefectures,id'],
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'license_id'    => ['required', 'integer', 'exists:licenses,id'],

            'category'   => ['nullable', 'array'],
            'category.*' => ['integer', 'exists:categories,id'],

            'mrs_member_id' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $this->user->findOrFail(Auth::id());

        $user->surname_kana = $validated['surname_kana'];
        $user->name_kana    = $validated['name_kana'];
        $user->email        = $validated['email'];
        $user->save();

        $referee = $user->referee;

        $referee->surname_kanji = $validated['surname_kanji'];
        $referee->name_kanji    = $validated['name_kanji'];
        $referee->surname_kana  = $validated['surname_kana'];
        $referee->name_kana     = $validated['name_kana'];
        $referee->surname       = $validated['surname'];
        $referee->name          = $validated['name'];

        $referee->registration_number = $validated['registration_number'];
        $referee->birth_date          = $validated['birth_date'];
        $referee->gender              = $validated['gender'] ?? null;

        $referee->prefecture_id   = $validated['prefecture_id'];
        $referee->organization_id = $validated['organization_id'];
        $referee->license_id      = $validated['license_id'];

        $referee->mrs_member_id = $validated['mrs_member_id'] ?? null;
        $referee->remarks       = $validated['remarks'] ?? null;

        $referee->save();

        $referee->categories()->sync($validated['category'] ?? []);

        return redirect()
            ->route('profile.show')
            ->with('status', '登録情報を更新しました');
    }
}
