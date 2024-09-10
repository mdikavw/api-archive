@extends('layouts.app')

@section('content')
    @include('partials.post', ['post' => $post, 'isPreview' => false])
    @include('partials.comment_form', [
        'commentable' => $post,
        'isHidden' => false,
    ])
    <h3>Comments</h3>
    <div id="post{{ $post->id }}Comments">
        <div class="text-center" id="post{{ $post->id }}CommentsSpinner">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <ul id="post{{ $post->id }}CommentsContainer">
            {{-- partials.comments --}}
        </ul>
    </div>
@endsection

@section('right-bar')
    @if ($post->drawer)
        <div class="right-bar">
            <div class="right-bar-drawer-name">
                <span><strong>d|{{ $post->drawer->name }}</strong></span>
            </div>
            <div class="right-bar-drawer-description">
                <p>{{ $post->drawer->description }}</p>
            </div>
            <div class="right-bar-drawer-stats">
                <div class="right-bar-drawer-subs-count">
                    <span>{{ $post->drawer->subsribers()->count() }}</span><br>
                    <span>Subs</span>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var id = "{{ $post->id }}"
            var slug = "{{ $post->slug }}"
            $(`#post${id}CommentsSpinner`).hide()
            getComments(slug)

            function getComments(slug) {
                $(`#post${id}CommentsSpinner`).show()
                $.ajax({
                    type: "GET",
                    url: `/posts/${slug}/comments`,
                    success: function(response) {
                        $(`#post${id}CommentsContainer`).append(response.view);
                    },
                    complete: function() {
                        $(`#post${id}CommentsSpinner`).hide();
                    }
                });
            }
        });
    </script>
@endpush
