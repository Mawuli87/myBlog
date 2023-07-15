@extends('layouts.app')
@section('content')

    @if (session('msg'))
    <div class="alert alert-success">
      {{ session('msg') }}
    </div> 
    @endif

    <div class="container">
      <a href="{{ route('post.create') }}" class="btn btn-danger text-center m-3">Add new post</a>
     </div>
<div class="container">
  <div class="row">

    @foreach ($posts as $post)
    <div class="card">
      <div class="card-header">
    {{ $post->title }} <div>{{ $post->user->name }}</div> <span>{{ $post->created_at->diffForHumans() }}<span>
      </div>
      <div class="card-body">
        <p class="card-text">  {{ substr($post->content, 0, 100) }} </p>
        <a href="{{ route('post.show', $post->id) }}" class="btn btn-primary">show the post</a>
      </div>
    </div>
    @endforeach
    <div>{{ $posts->links() }}</div>
  </div>

</div>

@endsection