<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    function __construct()
    {
           $this->middleware('auth')->except(['index', 'show']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function dashboard() {
         $authUser = Auth::user();
         $myPosts = $authUser->posts()->paginate(4);
         return view('post.dashboard', compact('myPosts'));
     }


    public function index()
    {
            $posts = Post::orderBy('id', 'desc')->paginate(4);
        return view('post.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
              'title' => ['required', 'min:3', 'max:20'],
              'content' => ['required', 'min:5'],
              'file' => ['required','image','mimes:png,jpg','max:1024'],
              //'file' => [ 'required', 'mimes:pdf', 'max:1024']
       ]);

       try {

        //store the image in the storage
        if($request->hasFile('file')) {
          $image = $request->file('file');
          $imageName = $image->getClientOriginalName();
          Storage::disk('public')->putFileAs('images', $image, $imageName);

         Post::create([
             'title' => $request->input('title'),
             'content' => $request->input('content'),
             'user_id' => Auth::id(),
             'file' => $imageName,
         ]);

         return redirect()->route('post.index')->with('msg', 'post has beed created successfully');
        }

         } catch(\Exception $e) {
             return redirect()->back()->with('msg', 'post not added');
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
        $comments = Cache::remember('post-comments', 60 , function () use($id) {
            return  Comment::where('post_id' , $id)->get();
        });



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
            'content' => ['required', 'min:5'],
     ]);

     try {
        $myPost = Post::find($id);

        if($request->hasFile('file')) {
         if(is_file(public_path().'/storage/images/'.$myPost->file)) {
            unlink(public_path().'/storage/images/'.$myPost->file);
          }
            $image = $request->file('file');
            $imageName = $image->getClientOriginalName();
            Storage::disk('public')->putFileAs('images', $image, $imageName);


       $myPost->update([
           'title' => $request->input('title'),
           'content' => $request->input('content'),
           'user_id' => Auth::id(),
           'file' => $imageName,
       ]);

       return redirect()->route('post.index')->with('msg', 'post has beed updated successfully');
    } else {
        $myPost->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'user_id' => Auth::id(),

        ]);
    }
       } catch(\Exception $e) {
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
          if(is_file(public_path().'/storage/images/'.$post->file)) {
            unlink(public_path().'/storage/images/'.$post->file);
                     $post->delete();
                     return redirect()->back()->with('msg', 'post has been deleted successfully');
          }
             } else {
                return redirect()->back()->with('msg', 'it is not your post');
             }

         } catch(\Exception $e) {
            return redirect()->back()->with('msg', 'post not deleted');
         }
    }
}