<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <script src="https://kit.fontawesome.com/02225abd0c.js" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
            crossorigin="anonymous"></script>
        <title>Archive</title>
    </head>

    <body>
        @include('partials.navbar')

        <div class="container">
            <div class="row">
                <div class="col-2"></div>
                <div class="col-8">
                    @yield('content')
                </div>
                <div class="col-2">
                    @yield('right-bar')
                </div>
            </div>
        </div>
    </body>
    <script>
        $(document).ready(function() {
            $(document).on('submit', '.comment-form', function(e) {
                e.preventDefault();
                let form = $(this)
                let formData = form.serialize()
                let id = form.find('input[name="id"]').val()
                let type = form.find('input[name="type"]').val()

                makeComment(id, type, form, formData)
            });

            $(document).on('click', '.comment-btn', function() {
                let id = $(this).data('id')

                if ($(`#comment${id}RepliesContainer`).children().length == 1) {
                    getReplies(id)
                }
            });

            $(document).on('submit', '.favor-form, .oppose-form', function(e) {
                e.preventDefault()
                let form = $(this)
                let id = form.find('input[name="reactable_id"]').val()
                let reactableType = form.find('input[name="reactable_type"]').val()
                let formData = form.serialize()

                makeReaction(id, reactableType, formData)
            });

            function getReplies(id) {
                $.ajax({
                    type: "GET",
                    url: `/comments/${id}/replies`,
                    success: function(response) {
                        $(`#comment${id}RepliesContainer`).append(response.view);
                    }
                });
            }

            function makeComment(id, type, form, formData) {
                $.ajax({
                    type: "POST",
                    url: "/comment",
                    data: formData,
                    success: function(response) {
                        $(`#${type}${id}CommentsContainer`).append(response.view)
                        form[0].reset()
                    }
                });
            }

            function makeReaction(id, reactableType, formData) {
                let type = reactableType == 'App\\Models\\Post' ? 'post' : 'comment'
                $.ajax({
                    type: "POST",
                    url: "/reaction",
                    data: formData,
                    headers: {
                        'X-CSRF-Token': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(type, id);
                        $(`#${type}${id}ReactableCardStatsReactionCount`).text(response.reactionCount);
                        if (response.type == 'favor') {
                            $(`#${type}-${id}-reactable-card-stats-btn-react-favor`).children()
                                .removeClass(
                                    'fa-regular')
                            $(`#${type}-${id}-reactable-card-stats-btn-react-favor`).children()
                                .addClass(
                                    'fa-solid')

                            $(`#${type}-${id}-reactable-card-stats-btn-react-oppose`).children()
                                .removeClass(
                                    'fa-solid')
                            $(`#${type}-${id}-reactable-card-stats-btn-react-oppose`).children()
                                .addClass(
                                    'fa-regular')
                        } else if (response.type == 'oppose') {
                            $(`#${type}-${id}-reactable-card-stats-btn-react-favor`).children()
                                .removeClass(
                                    'fa-solid')
                            $(`#${type}-${id}-reactable-card-stats-btn-react-favor`).children()
                                .addClass(
                                    'fa-regular')

                            $(`#${type}-${id}-reactable-card-stats-btn-react-oppose`).children()
                                .removeClass(
                                    'fa-regular')
                            $(`#${type}-${id}-reactable-card-stats-btn-react-oppose`).children()
                                .addClass(
                                    'fa-solid')
                        } else {
                            $(`#${type}-${id}-reactable-card-stats-btn-react-favor`).children()
                                .removeClass(
                                    'fa-solid')
                            $(`#${type}-${id}-reactable-card-stats-btn-react-favor`).children()
                                .addClass(
                                    'fa-regular')

                            $(`#${type}-${id}-reactable-card-stats-btn-react-oppose`).children()
                                .removeClass(
                                    'fa-solid')
                            $(`#${type}-${id}-reactable-card-stats-btn-react-oppose`).children()
                                .addClass(
                                    'fa-regular')
                        }
                    },
                });
            }
        });
    </script>
    @stack('scripts')

</html>
