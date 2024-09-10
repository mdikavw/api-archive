<div class="post">
    @php
        $isPreview ??= false;
    @endphp
    <div class="post-card">
        <div class="post-card-username mb-2">
            @if ($post->type == 'profile')
                <span>u|{{ $post->user->username }}</span>
            @else
                <span>d|{{ $post->drawer->name }}</span><br>
                <span class="ms-2"><small>u|{{ $post->user->username }}</small></span>
            @endif
        </div>
        <div class="post-card-title">
            <strong><a href="/posts/{{ $post->slug }}">{{ $post->title }}</a></strong>
        </div>
        <div class="post-card-content">
            <p>{{ $isPreview ? Str::limit($post->content, 150, '...') : $post->content }}</p>
        </div>
    </div>
    @include('partials.reactable_control', [
        'reactable' => $post,
        'isPreview' => $isPreview,
    ])
</div>
