<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PostController extends Controller
{
   
    public function __construct()
    {
        $this->middleware('auth')->except(['index','show']);
    }

    public function dashboard() {
        $authUser = Auth::user();
         $myPosts = $authUser->posts;
         return view('post.dashboard', compact('myPosts'));
    }


    public function index()
    {
        //
        $posts = Post::orderBy('id','desc')->paginate(4);
        return view('post.index',compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('post.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $request->validate([
        'title'=> ['required','min:3','max:20'],
        'content' => ['required','min:5','max:200']
       ]);

    
       try {

        Post::create([
            'title'=> $request->input('title'),
            'content'=> $request -> input('content'),
            'user_id'=>Auth::id(),
          ]);
            return redirect()->route('post.index')->with('msg','Post has been Created Successfuly');
       }catch(Exception $e){
          return redirect()->back()->with('msg','Post not saved');
       }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        $comments = Comment::where('post_id' , $id)->get();
        //return view('post.show', compact('post'));
        return view('post.show', compact(['post', 'comments']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = Post::find($id);
        return view('post.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => ['required', 'min:3', 'max:20'],
            'content' => ['required', 'min:5']
     ]);

     try {

        $myPost = Post::find($id);

       $myPost->update([
           'title' => $request->input('title'),
           'content' => $request->input('content'),
           'user_id' => Auth::id(),
       ]);

       return redirect()->route('post.index')->with('msg', 'post has beed updated successfully');

       } catch(Exception $e) {
           return redirect()->back()->with('msg', 'post not updated');
       }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        try {
            $owner = $post->user->id;
            $authUser = Auth::id();

            if($owner == $authUser) {
                    $post->delete();
                    return redirect()->back()->with('msg', 'post has been deleted successfully');
            } else {
               return redirect()->back()->with('msg', 'it is not your post');
            }

        } catch(\Exception $e) {
           return redirect()->back()->with('msg', 'post not deleted');
        }
    }
}
