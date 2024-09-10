@php
    $type = get_class($commentable) == 'App\Models\Post' ? 'post' : 'comment';
    $id = $commentable->id;
    $post_id = $type == 'post' ? $commentable->id : $commentable->post->id;
    $parent_id = $type == 'post' ? null : $commentable->id;
    $isHidden ??= true;
@endphp
<div>
    <form class="comment-form" id="{{ $type }}{{ $id }}CommentForm" action="/comment" method="POST">
        @csrf
        <input class="input" name="content" type="text" placeholder="Add a Comment...">
        <input name="post_id" type="hidden" value="{{ $post_id }}">
        <input name="parent_id" type="hidden" value="{{ $parent_id }}">
        <input name="id" type="hidden" value="{{ $id }}">
        <input name="type" type="hidden" value="{{ $type }}">
        <button class="comment-form-btn">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </form>
</div>
