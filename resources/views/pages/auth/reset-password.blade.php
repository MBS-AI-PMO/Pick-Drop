@extends('layout.master2')
@push('style')
<style>
.auth-card {
    border: none;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,.08);
    background: #fff;
}

.auth-form-wrapper{
    padding:3.5rem;
}

.auth-side-wrapper{
    width:100%;
    min-height:500px;
    position:relative;
    border-radius:20px 0 0 20px;
}

.brand-logo{
    font-size:28px;
    font-weight:800;
    color:#1d3557;
    text-decoration:none;
}

.brand-logo span{
    color:#e63946;
}

.form-control{
    border-radius:12px;
    padding:.8rem 1rem;
}

.btn-primary{
    border-radius:12px;
    padding:.8rem;
}
</style>
@endpush
@section('content')
<div class="row w-100 mx-0 auth-page justify-content-center">
    <div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
        <div class="auth-card">
            <div class="row g-0">

                <div class="col-md-5 d-none d-md-block">
                    <div class="auth-side-wrapper bg-primary overflow-hidden">
                        <div class="position-absolute rounded-circle"
                            style="width:300px;height:300px;background:rgba(255,255,255,.1);top:-50px;right:-50px;z-index:1;">
                        </div>

                        <div class="position-absolute rounded-circle"
                            style="width:200px;height:200px;background:rgba(255,255,255,.05);bottom:-20px;left:-20px;z-index:1;">
                        </div>

                        <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-4"
                            style="z-index:2;">
                            <div class="mb-4 text-white opacity-75 d-flex justify-content-center">
                                <i data-lucide="shield-check" style="width:80px;height:80px;"></i>
                            </div>

                            <h3 class="text-white fw-bold mb-2">
                                Reset Password
                            </h3>

                            <p class="text-white-50">
                                Enter your new password.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-7 ps-md-0">
                    <div class="auth-form-wrapper">

                        <div class="text-center text-md-start">
                            <a href="{{ route('dashboard') }}" class="brand-logo mb-2">
                                Pick<span>Drop</span>
                            </a>

                            <h4 class="fw-bold mb-3 mt-2">
                                Reset Password
                            </h4>
                        </div>

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="mb-3">
                                <label class="form-label">
                                    New Password
                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    Confirm Password
                                </label>

                                <input
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control"
                                    required>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary text-white w-100">
                                Reset Password
                            </button>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection