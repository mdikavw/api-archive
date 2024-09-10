@extends('layouts.app')

@section('content')
<div class="posts">
    @foreach ($posts as $post)
    @include('partials.post', ['post' => $post, 'isPreview' => true])
    @endforeach
</div>
@endsection