{{--
    Base shell for every applicant-facing page.

    Before this, the system had three separate shells — layouts.layout for the
    profile pages, layouts.guest for careers, and raw standalone HTML for login,
    register and My Applications. A person moving between them saw three
    different products, and My Applications had no navigation at all.

    Everything now inherits from here. Child layouts fill @yield('body'):
      layouts.layout — adds the progress rail (19 existing pages, unchanged)
      layouts.guest  — public width (careers pages, unchanged)
      or a view extends this directly for a full-bleed page.
--}}
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', config('app.name'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="url-prefix" content="{{ url('/') }}">
    <link rel="icon" href="https://teamtngc.com/zen/assets/img/coffi.png" type="image/png">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- The design system. Loaded last so it wins over Bootstrap. --}}
    {{-- Versioned by the file's own modification time, so a stylesheet change
         reaches browsers that cached an earlier copy. A fixed ?v= number kept
         serving stale CSS until someone remembered to bump it. --}}
    <link rel="stylesheet" href="{{ asset('main.css') }}?v={{ @filemtime(public_path('main.css')) ?: 2 }}">

    @stack('head')
</head>

<body>
    @include('layouts.partials.header')

    @yield('body')

    <script>
        $(function () {
            // Kept from the previous layout: existing pages call .autoResize()
            // on textareas, and fetch() calls are written as root-relative
            // paths that need the deployment prefix.
            $.fn.autoResize = function () {
                this.each(function () {
                    const textarea = $(this);
                    const maxHeight = $(window).height() * 0.5;

                    function resize() {
                        const newHeight = textarea[0].scrollHeight;
                        if (textarea[0].clientHeight < newHeight) {
                            textarea.height(Math.min(newHeight, maxHeight));
                        }
                    }

                    textarea.off('input.autoResize').on('input.autoResize', resize);
                    resize();
                });

                return this;
            };

            const originalFetch = window.fetch;
            const urlPrefix = document.querySelector('meta[name="url-prefix"]')?.getAttribute('content') || '';

            window.fetch = function (input, init = {}) {
                if (urlPrefix && typeof input === 'string' && !input.startsWith('http') && !input.startsWith(urlPrefix)) {
                    input = urlPrefix + input;
                }
                return originalFetch(input, init);
            };
        });
    </script>

    @stack('scripts')
</body>

</html>
