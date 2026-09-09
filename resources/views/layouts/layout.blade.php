<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ config('app.name') }}</title>
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
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js"
        integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="stylesheet" href="{{ asset('main.css') }}">
    <style>
        :root {
            --my-top-space: 57px;
        }

        body,
        html {
            min-height: 100%;
            margin: 0;
            background-color: #e9ecf3 !important;
            box-sizing: border-box;
        }

        .bi {
            display: inline-block;
            width: 1rem;
            height: 1rem;
        }


        /* body > main {
            margin-top: var(--my-top-space);
        } */

        #site-logo,
        #usr-img {
            /* transform: scale(1.5); */
            height: 30px;
        }

        #usr-img {
            aspect-ratio: 1;
        }

        body>nav {
            max-height: 57px;
            height: 57px;
        }

        body>nav.navbar .nav-item .nav-link {
            padding-top: .25rem;
            padding-bottom: .25rem;
            font-size: 20px;
        }

        /* body > nav.navbar .nav-item .nav-link:hover{
            transform: scale(1.3);
        } */

        #profile-tabs {
            width: 100%;
            max-height: calc(100vh - var(--my-top-space));
            flex-wrap: nowrap;
            position: sticky;
            top: calc(var(--my-top-space));
            border-right: 1px solid lightgray;
            overflow: auto;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        #profile-tabs h6 {
            font-size: .9rem;
        }

        #profile-tabs li a {
            font-size: 12px;
            color: black;
        }

        #profile-tabs li a.active {
            font-weight: bold;
            color: var(--bs-primary);
        }

        #profile-tabs li:hover {
            background-color: #d1d1d1;
        }

        /* Adjusting scrollbar thickness */
        #profile-tabs::-webkit-scrollbar {
            width: 7px;
            /* Vertical scrollbar width */
            height: 7px;
            /* Horizontal scrollbar height */
        }

        /* Customize the scrollbar thumb (draggable part) */
        #profile-tabs::-webkit-scrollbar-thumb {
            background: #8b8a8a;
            /* Color of the thumb */
            border-radius: 10px;
            /* Rounded corners for thumb */
        }

        .form-control-plaintext.border-bottom {
            padding-bottom: 1px !important;
        }

        fieldset:disabled select {
            -webkit-appearance: none;
            /* For Safari, Chrome */
            -moz-appearance: none;
            /* For Firefox */
            appearance: none;
            /* For modern browsers */
            /*background: transparent;*/
            /* Make the background transparent if needed */
            border: none;
            /* Optional: remove the border */
        }
    </style>

    <script>
        $(function() {
            $.fn.autoResize = function() {
                this.each(function() {
                    var textarea = $(this);

                    // Set the max height to 50% of the viewport height
                    var maxHeight = $(window).height() * 0.5;

                    // Function to resize the textarea
                    function resize() {
                        // Reset height to auto to allow shrinking
                        // textarea.height('auto');

                        // Set the height based on the scrollHeight (content height)
                        var newHeight = textarea[0].scrollHeight;

                        if (textarea[0].clientHeight < newHeight) {
                            // Apply the height, but don't exceed the maxHeight
                            textarea.height(Math.min(newHeight, maxHeight));
                        }
                    }

                    // Unbind any previous .autoResize namespaced input event before rebinding
                    textarea.off('input.autoResize').on('input.autoResize', resize);
                    // Bind the input event to trigger resize on every input
                    textarea.on('input', resize);

                    // Initial call to resize to set the correct height on page load
                    resize();
                });

                return this; // Return the jQuery object to maintain chainability
            };

            // Store the original fetch function
            const originalFetch = window.fetch;

            // Retrieve the URL prefix from the meta tag
            const urlPrefix = document.querySelector('meta[name="url-prefix"]')?.getAttribute('content') || '';

            // Override the global fetch function
            window.fetch = function(input, init = {}) {
                // If the URL prefix is not empty, and the request URL is relative, prepend the prefix
                if (urlPrefix && !input.startsWith('http') && !input.startsWith(urlPrefix)) {
                    // Convert input to a string if it's a URL object
                    if (typeof input === 'string') {
                        input = urlPrefix + input;
                    } else {
                        input.url = urlPrefix + input.url;
                    }
                }

                // Call the original fetch function with the modified URL
                return originalFetch(input, init);
            };
        });
    </script>
</head>

<body>
    @include('layouts.partials.header')

    <main class="container-fluid">
        <script type="text/javascript">
            $(function() {
                const link_item = $("#profile-tabs .nav-item a.active").parent()[0];
                if (link_item) {
                    // Scroll the item into view horizontally
                    link_item.scrollIntoView({
                        // behavior: 'smooth',  // Smooth scrolling
                        block: 'center' // Scroll the element to the center horizontally
                    });
                }
            });
        </script>

        <div class="row">
            <div class="col-md-2">
                <ul class="nav flex-column" id="profile-tabs">

                    <h6
                        class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Profile</span>
                    </h6>
                    <li class="nav-item"><a href="{{ Route::has('personal.show') ? route('personal.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('personal.show') ? 'active' : '' }}">Personal</a>
                    </li>
                    <li class="nav-item"><a href="{{ Route::has('family.index') ? route('family.index') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('family.index') ? 'active' : '' }}">Family
                            Background</a></li>
                    <li class="nav-item"><a href="{{ Route::has('skill.index') ? route('skill.index') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('skill.index') ? 'active' : '' }}">Special
                            Skills</a></li>
                    <li class="nav-item"><a href="{{ Route::has('education.index') ? route('education.index') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('education.index') ? 'active' : '' }}">Education</a>
                    </li>

                    <hr class="my-3">

                    <h6
                        class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Professional</span>
                    </h6>
                    <li class="nav-item"><a href="{{ Route::has('license.index') ? route('license.index') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('license.index') ? 'active' : '' }}">Eligibility/Licenses</a>
                    </li>
                    <li class="nav-item"><a
                            href="{{ Route::has('certificate.index') ? route('certificate.index') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('certificate.index') ? 'active' : '' }}">Certificate</a>
                    </li>

                    <hr class="my-3">

                    <h6
                        class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Documents</span>
                    </h6>
                    <li class="nav-item"><a
                            href="{{ Route::has('documents.index') ? route('documents.index') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('documents.index') ? 'active' : '' }}">My
                            Documents</a>
                    </li>

                    <hr class="my-3">

                    <h6
                        class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Work</span>
                    </h6>
                    <li class="nav-item"><a
                            href="{{ Route::has('employment.index') ? route('employment.index') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('employment.index') ? 'active' : '' }}">Employment
                            Record</a></li>
                    <li class="nav-item"><a
                            href="{{ Route::has('characterref.index') ? route('characterref.index') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('characterref.index') ? 'active' : '' }}">Character
                            Reference</a></li>

                    <hr class="my-3">

                    <h6
                        class="d-flex justify-content-between align-items-center px-3 mt-2 mb-1 text-body-light text-uppercase">
                        <span>Personality Test</span>
                    </h6>
                    <li class="nav-item"><a href="{{ Route::has('enneagram.show') ? route('enneagram.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('enneagram.show') ? 'active' : '' }}">Enneagram</a>
                    </li>
                    <li class="nav-item"><a href="{{ Route::has('tapt.show') ? route('tapt.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('tapt.show') ? 'active' : '' }}">TAPT</a>
                    </li>
                    <li class="nav-item"><a href="{{ Route::has('disc.show') ? route('disc.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('disc.show') ? 'active' : '' }}">DISC</a>
                    </li>
                    <li class="nav-item"><a href="{{ Route::has('miq.show') ? route('miq.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('miq.show') ? 'active' : '' }}">Multiple
                            Intelligent Quotient</a></li>
                    <li class="nav-item"><a href="{{ Route::has('color.show') ? route('color.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('color.show') ? 'active' : '' }}">What
                            color are you?</a></li>
                    <li class="nav-item"><a href="{{ Route::has('vak.show') ? route('vak.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('vak.show') ? 'active' : '' }}">VAK</a>
                    </li>
                    <li class="nav-item"><a
                            href="{{ Route::has('why_i_work.show') ? route('why_i_work.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('why_i_work.show') ? 'active' : '' }}">Why
                            I Work</a></li>
                    <li class="nav-item"><a
                            href="{{ Route::has('career_anchors.show') ? route('career_anchors.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('career_anchors.show') ? 'active' : '' }}">Career
                            Anchors</a></li>
                    <hr class="my-1 mx-3">
                    <li class="nav-item"><a
                            href="{{ Route::has('abstract_reasoning.show') ? route('abstract_reasoning.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('abstract_reasoning.show') ? 'active' : '' }}">Basic
                            Abstract Reasoning</a></li>
                    <li class="nav-item"><a
                            href="{{ Route::has('basic_math.show') ? route('basic_math.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('basic_math.show') ? 'active' : '' }}">Basic
                            Math</a></li>
                    <li class="nav-item"><a href="{{ Route::has('maya.show') ? route('maya.show') : '#' }}"
                            class="nav-link align-items-center gap-2 {{ Route::is('maya.show') ? 'active' : '' }}">Maya</a>
                    </li>
                </ul>
            </div>

            <div class="col-md-10 pt-3">
                <input type="hidden" name="applicant-id" id="applicant-id" value="{{ auth()->user()->app_id }}">
                @yield('content')
            </div>
        </div>
    </main>

</body>

</html>
