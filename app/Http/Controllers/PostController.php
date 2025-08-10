<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    private $post;
    private $category;
    private $like;

    public function __construct(Post $post, Category $category, Like $like)
    {
        $this->post = $post;
        $this->category = $category;
        $this->like = $like;
    }

    public function create()
    {
        $all_categories = $this->category->all();

        return view('users.posts.create')->with('all_categories', $all_categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category'      => 'required|array|between:1,3',
            'description'   => 'required|min:1|max:1000',
            'image'         => 'required|mimes:jpg,jpeg,png,gif|max:1048'
        ]);

        # save the post
        $this->post->user_id     =  Auth::user()->id;
        $this->post->image       = 'data:image/' . $request->image->extension() . ';base64,' . base64_encode(file_get_contents($request->image));
        $this->post->description = $request->description;
        $this->post->save();

        # save the categories to the category_post pivot table
        foreach ($request->category as $category_id){
            $category_post[] = ['category_id' => $category_id]; // category_id = key
            /*
                $category_post = [
                    ['category_id' => 1],
                    ['category_id' => 2],
                    ['category_id' => 3]
                ];
            */ 
        }

        $this->post->categoryPost()->createMany($category_post); //create() for 2D associative array

            /*
                category_post table:

                    ['post_id' => 1, 'category_id' => 1],
                    ['post_id' => 1, 'category_id' => 2],
                    ['post_id' => 1, 'category_id' => 3]
            */ 

        # Go back to homepage
        return redirect()->route('index');
    }

    public function show($id)
    {
        $all_likes = $this->like->get();
        $post = $this->post->findOrFail($id);

        return view('users.posts.show')
                ->with('all_likes', $all_likes)
                ->with('post', $post);
    }

    // edit() - view Edit Page
    public function edit($id)
    {
        $post = $this->post->findOrFail($id);

        # IF THE AUTH user is not the owner is not the owner of the POST, redirect to index
        if (Auth::user()->id != $post->user->id){
            return redirect()->route('index');
        }

        $all_categories = $this->category->all();

        # GET all the category IDs of this POST. Then save it in an ARRAY.
        $selected_categories = [];
        foreach( $post->categoryPost as $category_post ){
            $selected_categories[] = $category_post->category_id;
        }

        return view('users.posts.edit')
                ->with('post', $post)
                ->with('all_categories', $all_categories)
                ->with('selected_categories', $selected_categories);
    }

    public function update(Request $request, $id)
    {
        # 1. VALIDATE THE DATA FROM THE FORM
        $request->validate([
            'category'      => 'required|array|between:1,3',
            'description'   => 'required|min:1|max:1000',
            'image'         => 'mimes:jpg,jpeg,png,gif|max:1048'
        ]);

        # 2. UPDATE THE POST
        $post               = $this->post->findOrFail($id);
        $post->description  = $request->description;

        # IF there is a new IMAGE
        if($request->image){
            $post->image = 'data:image/' . $request->image->extension() . ';base64,' . base64_encode(file_get_contents($request->image));
        }

        $post->save();

        # 3. DELETE ALL RECORDS from the category_post table related to this POST
        $post->categoryPost()->delete();
        // USE the relationship POST::categoryPost() to select the records related to the post
        //  DELETE FROM category_post WHERE post_id = $id;

        # 4. SAVE the new categories to the category_post table
        foreach($request->category as $category_id){
            $category_post[] = ['category_id' => $category_id];
        }
        $post->categoryPost()->createMany($category_post);

        # 5. REDIRECT to Show Post page
        return redirect()->route('post.show', $id);
    }
    
    public function destroy($id)
    {
        $this->post->findOrFail($id)->forceDelete();

        return redirect()->route('index');
    }
}