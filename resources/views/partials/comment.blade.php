<li id="comment{{ $comment->id }}">
    <div class="comment">
        <div data-bs-toggle="collapse" data-bs-target="#repliesComment{{ $comment->id }}">
            <p class="m-0"><strong>{{ $comment->user->username }}</strong></p>
            <p class="collapse show" id="repliesComment{{ $comment->id }}">{{ $comment->content }}</p>
        </div>
        @include('partials.reactable_control', [
            'reactable' => $comment,
        ])
        <ul class="collapse" id="comment{{ $comment->id }}RepliesContainer">
            @include('partials.comment_form', [
                'commentable' => $comment,
            ])
        </ul>
    </div>
    </div>
</li>
