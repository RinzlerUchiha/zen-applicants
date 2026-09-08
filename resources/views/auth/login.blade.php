<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style type="text/css">
        /* #logo {
            max-width: 100%;
        } */

        .form-floating,
        .form-floating *,
        .form-floating::after,
        .form-floating *::after {
            background-color: transparent !important;
        }

        body {
            background-color: #f4f4f4;
        }

        form > button,
        input.form-control {
            font-size: 15px !important;
        }

        form > .input-group {
            background-color: #f0f0f0;
        }

        input.form-control:focus {
            outline: none;
            box-shadow: none;
        }

        .gap-custom {
            gap: 70px;
        }

        .mt-md-custom {
            margin-top: -3.5rem !important;
            /* margin-top: 50%; */
            /* margin-bottom: 50%; */
        }

        .btn-custom,
        .btn-custom:hover {
            border-color: var(--bs-border-color);
            border-width: 3px;
        }
        
        .btn-custom.active,
        .btn-custom:active {
            border-width: 3px;
            border-color: var(--bs-btn-active-border-color);

            /* border-top: 1px solid transparent !important; */
            /* border-right: 1px solid var(--bs-body-color) !important; */
            /* border-bottom: 1px solid transparent !important; */
            /* border-left: 1px solid var(--bs-body-color) !important; */
        }
    </style>
</head>

<body>
    <div class="container-fluid vh-100">
        <div class="row h-100 my-auto gap-custom align-items-center justify-content-center">
            <div class="col-md-4 mt-md-custom">
                <a href="{{ route('careers.index') }}" class="d-inline-flex align-items-center text-decoration-none text-muted mb-2" style="font-size:14px;">
                    <i class="fa fa-arrow-left me-1"></i> Back to Careers
                </a>
                <h3 class="text-center fw-normal text-muted mb-3">Login</h3>
                <form class="mb-3" method="POST" action="{{ route('login') }}">
                    @if ($errors->any())
                        <div class="alert alert-danger p-1" role="alert">
                            @foreach ($errors->all() as $error)
                                <span class="fw-medium d-block">{{ $error }}</span>
                            @endforeach
                        </div>
                    @endif
                    @csrf
                    
                    <div class="d-flex gap-3 mb-2 align-items-center">
                        <span>Login with:</span>
                        <button type="button" class="btn btn-sm btn-custom btn-login-with {{ !old('mobile') ? 'active' : '' }}" id="btn-email">Email</button>
                        <small>OR</small>
                        <button type="button" class="btn btn-sm btn-custom btn-login-with {{ old('mobile') ? 'active' : '' }}" id="btn-mobile">Mobile No.</button>
                    </div>

                    <div class="input-group input-group-sm mb-3 rounded-pill border" id="input-grp-email" style="{{ old('mobile') ? 'display: none;' : '' }}">
                        <span class="border-0 input-group-text p-2 fs-5 text-body-tertiary rounded-start-pill bg-transparent"><i class="fa fa-at"></i></span>
                        <input type="email" class="border-0 form-control rounded-end-pill bg-transparent" name="email" id="email" value="{{ old('email') }}" placeholder="example@email.com" aria-label="Email">
                    </div>
                    <div class="input-group input-group-sm mb-3 rounded-pill border" id="input-grp-mobile" style="{{ old('email') || (!old('email') && !old('mobile')) ? 'display: none;' : '' }}">
                        <span class="border-0 input-group-text p-2 fs-5 text-body-tertiary rounded-start-pill bg-transparent"><i class="fa fa-mobile-screen"></i></span>
                        <input type="text" class="border-0 form-control rounded-end-pill bg-transparent" name="mobile" id="mobile" value="{{ old('mobile') }}" placeholder="09#########" aria-label="Phone Number">
                    </div>
                    <div class="input-group input-group-sm mb-3 rounded-pill border">
                        <span class="border-0 input-group-text p-2 fs-5 text-body-tertiary rounded-start-pill bg-transparent"><i class="fa fa-lock"></i></span>
                        <input type="password" class="border-0 form-control rounded-end-pill bg-transparent" name="code" id="code" placeholder="Password" aria-label="Password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Login</button>

                    <div class="d-flex">
                        <a href="{{ route('register') }}" class="mx-auto mt-3 text-decoration-none">Apply Now</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const btnEmail = document.getElementById('btn-email');
        const btnMobile = document.getElementById('btn-mobile');
        const inputGrpEmail = document.getElementById('input-grp-email');
        const inputGrpMobile = document.getElementById('input-grp-mobile');
        const inputEmail = document.getElementById('email');
        const inputMobile = document.getElementById('mobile');

        btnEmail.addEventListener('click', function () {
            btnEmail.classList.add('active');
            btnMobile.classList.remove('active');
            if (btnEmail.classList.contains('active')) {
                inputGrpEmail.style.display = 'flex';
                inputGrpMobile.style.display = 'none';
                inputEmail.setAttribute('required', 'true');
                inputMobile.removeAttribute('required');
                inputMobile.value = '';
            }
        });

        btnMobile.addEventListener('click', function () {
            btnEmail.classList.remove('active');
            btnMobile.classList.add('active');
            if (btnMobile.classList.contains('active')) {
                inputGrpEmail.style.display = 'none';
                inputGrpMobile.style.display = 'flex';
                inputMobile.setAttribute('required', 'true');
                inputEmail.removeAttribute('required');
                inputEmail.value = '';
            }
        });


    </script>
</body>

</html>