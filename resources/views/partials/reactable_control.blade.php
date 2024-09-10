<div class="reactable-control">
    <div class="reactable-control-chip">
        @php
            $isPreview ??= false;
            $reaction =
                $reactable->reactedByLoggedUser->first() != null
                    ? $reactable->reactedByLoggedUser->first()->type
                    : null;
            $type = get_class($reactable) == 'App\Models\Post' ? 'post' : 'comment';
            $id = $reactable->id;
            $shouldShowCommentForm = true;
        @endphp
        <form class="favor-form" method="POST" action="/reaction">
            @csrf
            <input name="reactable_id" type="hidden" value="{{ $id }}">
            <input name="reactable_type" type="hidden" value="{{ get_class($reactable) }}">
            <input name="type" type="hidden" value="favor">
            <button class="reactable-control-btn"
                id="{{ $type }}-{{ $id }}-reactable-card-stats-btn-react-favor">
                @if ($reaction != null && $reaction == 'favor')
                    <i class="fa-solid fa-circle-up"></i>
                @else
                    <i class="fa-regular fa-circle-up"></i>
                @endif
            </button>
        </form>
        <small
            id="{{ $type }}{{ $id }}ReactableCardStatsReactionCount">{{ $reactable->favors_count - $reactable->opposes_count }}</small>
        <form class="oppose-form" method="POST" action="/reaction">
            @csrf
            <input name="reactable_id" type="hidden" value="{{ $id }}">
            <input name="type" type="hidden" value="oppose">
            <input name="reactable_type" type="hidden" value="{{ get_class($reactable) }}">
            <button class="reactable-control-btn"
                id="{{ $type }}-{{ $id }}-reactable-card-stats-btn-react-oppose">
                @if ($reaction != null && $reaction == 'oppose')
                    <i class="fa-solid fa-circle-down"></i>
                @else
                    <i class="fa-regular fa-circle-down"></i>
                @endif
            </button>
        </form>
    </div>
    <div class="reactable-control-chip">
        <button class="reactable-control-btn comment-btn" data-id="{{ $reactable->id }}" data-bs-toggle="collapse"
            data-bs-target="#comment{{ $id }}RepliesContainer">
            <a>
                <i class="fa-regular fa-message"></i>
            </a>
        </button>
        <small>{{ $reactable->comments_count }}</small>
    </div>

</div>
