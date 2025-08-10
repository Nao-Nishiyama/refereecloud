<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    private $post;
    private $user;
    private $like;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Post $post, User $user, Like $like)
    {
        $this->post = $post;
        $this->user = $user;
        $this->like = $like;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    //  index() - view the homepage
    public function index()
    {
        $all_likes = $this->like->get();
        $home_posts = $this->getHomePosts();
        $suggested_users = $this->getSuggestedUsers();

        return view('users.home')
                ->with('all_likes', $all_likes)
                ->with('home_posts', $home_posts)
                ->with('suggested_users', $suggested_users);
    }

    # get the posts of the Auth user and the posts of the following users
    private function getHomePosts() //private because this is used only in this Class
    {
        $all_posts = $this->post->latest()->get();
        $home_posts = [];

        foreach ($all_posts as $post){
            if ($post->user->isFollowed() || $post->user->id === Auth::user()->id){
                $home_posts[] = $post;
            }
        }

        return $home_posts;
    }

    // getSuggestedUsers() - Get the users that the Auth user is not following
    private function getSuggestedUsers()
    {
        $all_users = $this->user->all()->except(Auth::user()->id);
        $suggested_users = [];

        foreach ($all_users as $user){
            if (!$user->isFollowed()){
                $suggested_users[] = $user;
            }
        }
        return array_slice($suggested_users, 0, 5);
        // array_slice(x, y, z)
        // x -- array
        // y -- offset/starting index
        // z -- length/how many
    }

    // view the suggestions page
    public function suggestions()
    {
        $all_users = $this->user->all()->except(Auth::user()->id);
        $suggested_users = [];

        foreach ($all_users as $user){
            if (!$user->isFollowed()){
                $suggested_users[] = $user;
            }
        }

        return view('users.suggestions')
                    ->with('suggested_users', $suggested_users);
    }


    public function search(Request $request)
    {
        $users = $this->user->where('name', 'like', '%' . $request->search . '%')->get(); // retrieve all users who have the letter in 'search'

        return view('users.search')
                ->with('users', $users)
                ->with('search', $request->search);
    }
}
