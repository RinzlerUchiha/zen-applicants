@extends('layouts.app')

@section('title', 'Sign in')

@section('body')
<div class="zn-auth-wrap">
    <div class="zn-auth">

        <aside class="zn-auth-aside">
            <img src="https://teamtngc.com/zen/assets/img/coffi.png" alt="">
            <h2>Welcome back</h2>
            <p>Pick up where you left off — your progress is saved automatically.</p>
            <ul class="zn-auth-steps">
                <li class="zn-auth-step"><i>&check;</i><span>Check where your application stands</span></li>
                <li class="zn-auth-step"><i>&check;</i><span>Finish your application form</span></li>
                <li class="zn-auth-step"><i>&check;</i><span>Send any remaining documents</span></li>
            </ul>
        </aside>

        <div class="zn-auth-form">
            <h2>Sign in</h2>
            <p class="zn-page-sub" style="margin-bottom:18px">Use the email or mobile number you applied with.</p>

            @if ($errors->any())
                <div class="zn-toast error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Sign in with either identifier. The toggle swaps which field
                     is submitted and required; the other is cleared so the
                     controller only ever receives one. --}}
                <div class="zn-fld">
                    <label>Sign in with</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="zn-btn zn-btn-sm {{ old('mobile') ? 'zn-btn-out' : '' }}" id="btn-email">Email</button>
                        <button type="button" class="zn-btn zn-btn-sm {{ old('mobile') ? '' : 'zn-btn-out' }}" id="btn-mobile">Mobile number</button>
                    </div>
                </div>

                <div class="zn-fld" id="input-grp-email" style="{{ old('mobile') ? 'display:none;' : '' }}">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           placeholder="example@email.com" autocomplete="email">
                </div>

                <div class="zn-fld" id="input-grp-mobile" style="{{ old('mobile') ? '' : 'display:none;' }}">
                    <label for="mobile">Mobile number</label>
                    <input type="text" name="mobile" id="mobile" value="{{ old('mobile') }}"
                           placeholder="09#########" autocomplete="tel">
                </div>

                <div class="zn-fld">
                    <label for="code">Password</label>
                    <input type="password" name="code" id="code" required autocomplete="current-password">
                </div>

                <button type="submit" class="zn-btn zn-btn-block" style="margin-top:6px">Sign in</button>

                <p class="zn-auth-alt">
                    Don't have an account yet? <a class="zn-link" href="{{ route('register') }}">Create one</a>
                </p>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const btnEmail  = document.getElementById('btn-email');
        const btnMobile = document.getElementById('btn-mobile');
        const grpEmail  = document.getElementById('input-grp-email');
        const grpMobile = document.getElementById('input-grp-mobile');
        const email     = document.getElementById('email');
        const mobile    = document.getElementById('mobile');

        function use(mode) {
            const usingEmail = mode === 'email';

            grpEmail.style.display  = usingEmail ? '' : 'none';
            grpMobile.style.display = usingEmail ? 'none' : '';

            btnEmail.classList.toggle('zn-btn-out', !usingEmail);
            btnMobile.classList.toggle('zn-btn-out', usingEmail);

            // Only the active field is required, and the inactive one is
            // cleared so a stale value can never be submitted alongside it.
            (usingEmail ? email : mobile).setAttribute('required', 'required');
            (usingEmail ? mobile : email).removeAttribute('required');
            (usingEmail ? mobile : email).value = '';
        }

        btnEmail.addEventListener('click', () => use('email'));
        btnMobile.addEventListener('click', () => use('mobile'));

        use(@json(old('mobile') ? 'mobile' : 'email'));
    })();
</script>
@endpush
