<nav class="navbar navbar-expand bg-body-tertiary sticky-top" aria-label="Navbar">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
            aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}/">
            <img class="img-fluid" id="site-logo" src="https://teamtngc.com/zen/assets/img/coffi.png" alt="Logo"
                class="d-inline-block align-text-top">
            <span class="fs-5 ms-1" style="color: #5d2502;">{{ config('app.name') }}</span>
        </a>
        <ul class="navbar-nav">
            @auth
                <li class="nav-item dropdown" id="user-dropdown">
                    <a class="nav-link fs-6" href="#" role="button" data-bs-toggle="dropdown"
                        data-bs-auto-close="outside" aria-expanded="false">
                        {{ auth()->user()->first_last_name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Sign
                                    out</button>
                            </form>
                        </li>
                    </ul>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link fs-6" href="{{ route('login') }}">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fs-6" href="{{ route('register') }}">Apply Now</a>
                </li>
            @endauth
        </ul>
    </div>
</nav>