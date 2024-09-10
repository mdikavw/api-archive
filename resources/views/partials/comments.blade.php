@if ($comments->isNotEmpty())
    @foreach ($comments as $comment)
        @include('partials.comment', ['comment' => $comment])
    @endforeach
@else
    <span id="post{{ $post->id }}NoComments">No comments</span>
@endif
