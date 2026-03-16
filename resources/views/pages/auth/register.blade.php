@extends('layout.master2')

@push('style')
<style>
/* Custom Styles for Pick-Drop Auth Pages */
body {
    background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
    font-family: 'Outfit', 'Inter', sans-serif;
}
[data-bs-theme="dark"] body {
    background: linear-gradient(135deg, #1a1c23 0%, #121317 100%);
}

.auth-card {
    border: none;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease;
    background: #ffffff;
}
[data-bs-theme="dark"] .auth-card {
    background: #1e2129;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.auth-side-wrapper {
    width: 100%;
    height: 100%;
    min-height: 400px;
    background-size: cover;
    background-position: center;
    position: relative;
    border-radius: 20px 0 0 20px;
}
.auth-side-wrapper::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, rgba(230,57,70,0.8), rgba(29,53,87,0.7));
}

.auth-form-wrapper {
    padding: 3.5rem;
}

.brand-logo {
    font-size: 28px;
    font-weight: 800;
    color: #1d3557;
    text-decoration: none;
    display: inline-block;
}
.brand-logo span {
    color: #e63946;
}
[data-bs-theme="dark"] .brand-logo {
    color: #f1faee;
}
[data-bs-theme="dark"] .brand-logo span {
    color: #ff4d6d;
}

.form-control {
    border-radius: 12px;
    padding: 0.8rem 1.2rem;
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    transition: all 0.2s;
}
.form-control:focus {
    box-shadow: 0 0 0 4px rgba(230, 57, 70, 0.15);
    border-color: #e63946;
    background: #fff;
}
[data-bs-theme="dark"] .form-control {
    background: #252833;
    border-color: #323644;
    color: #fff;
}
[data-bs-theme="dark"] .form-control:focus {
    background: #252833;
    border-color: #ff4d6d;
    box-shadow: 0 0 0 4px rgba(255, 77, 109, 0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%);
    border: none;
    border-radius: 12px;
    padding: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s;
}
.btn-primary:hover {
    background: linear-gradient(135deg, #d90429 0%, #b3001b 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(230, 57, 70, 0.3);
}

.btn-google {
    border-radius: 12px;
    padding: 0.8rem;
    font-weight: 500;
    transition: all 0.3s;
    background-color: #fff;
    border: 1px solid #dee2e6;
    color: #4b5563;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-google:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}
[data-bs-theme="dark"] .btn-google {
    background-color: #252833;
    border-color: #323644;
    color: #e2e8f0;
}
[data-bs-theme="dark"] .btn-google:hover {
    background-color: #2a2d3b;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.form-check-input:checked {
    background-color: #e63946;
    border-color: #e63946;
}

.text-primary-custom {
    color: #e63946 !important;
    text-decoration: none;
    font-weight: 600;
}
.text-primary-custom:hover {
    text-decoration: underline;
}

.micro-anim {
    animation: fadeIn 0.6s ease-out forwards;
    opacity: 0;
    transform: translateY(10px);
}
.micro-anim:nth-child(2) { animation-delay: 0.1s; }
.micro-anim:nth-child(3) { animation-delay: 0.2s; }
.micro-anim:nth-child(4) { animation-delay: 0.3s; }
.micro-anim:nth-child(5) { animation-delay: 0.4s; }

@keyframes fadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
            <!-- Decorative CSS Shapes -->
            <div class="position-absolute rounded-circle" style="width: 300px; height: 300px; background: rgba(255,255,255,0.1); top: -50px; right: -50px; z-index: 1;"></div>
            <div class="position-absolute rounded-circle" style="width: 200px; height: 200px; background: rgba(255,255,255,0.05); bottom: -20px; left: -20px; z-index: 1;"></div>
            
            <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-4" style="z-index: 2;">
                <div class="mb-4 text-white opacity-75 d-flex justify-content-center">
                    <i data-lucide="users" style="width: 80px; height: 80px;"></i>
                </div>
                <h3 class="text-white fw-bold mb-2">Join Pick-Drop</h3>
                <p class="text-white-50">Ensure a safe journey for every student.</p>
            </div>
          </div>
        </div>
        <div class="col-md-7 ps-md-0">
          <div class="auth-form-wrapper">
            <div class="micro-anim text-center text-md-start">
                <a href="{{ route('dashboard') }}" class="brand-logo mb-2">Pick<span>Drop</span></a>
                <h5 class="text-secondary fw-normal mb-4">Create a free account.</h5>
            </div>
            
            <!-- Sweet Alert Logic -->
            
            <form class="forms-sample" method="POST" action="{{ route('auth.register.submit') }}">
                @csrf
              <div class="mb-3 micro-anim">
                <label for="exampleInputUsername1" class="form-label fw-medium">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i data-lucide="user" class="icon-sm"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control border-start-0 ps-0"
                        id="exampleInputUsername1"
                        name="name"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        placeholder="johndoe"
                        required
                    >
                </div>
              </div>
              <div class="mb-3 micro-anim">
                <label for="userEmail" class="form-label fw-medium">Email address</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i data-lucide="mail" class="icon-sm"></i>
                    </span>
                    <input
                        type="email"
                        class="form-control border-start-0 ps-0"
                        id="userEmail"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="name@company.com"
                        required
                        autocomplete="email"
                    >
                </div>
              </div>
              <div class="mb-3 micro-anim">
                <label for="userPassword" class="form-label fw-medium">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i data-lucide="lock" class="icon-sm"></i>
                    </span>
                    <input
                        type="password"
                        class="form-control border-start-0 ps-0"
                        id="userPassword"
                        name="password"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        required
                    >
                </div>
              </div>
              <div class="mb-3 micro-anim">
                <label for="userPasswordConfirmation" class="form-label fw-medium">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i data-lucide="lock" class="icon-sm"></i>
                    </span>
                    <input
                        type="password"
                        class="form-control border-start-0 ps-0"
                        id="userPasswordConfirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        required
                    >
                </div>
              </div>
              <div class="form-check mb-4 micro-anim">
                <input
                    type="checkbox"
                    class="form-check-input"
                    id="authCheck"
                    name="remember"
                    {{ old('remember') ? 'checked' : '' }}
                >
                <label class="form-check-label" for="authCheck">
                  Remember me
                </label>
              </div>
              
              <div class="micro-anim mt-2 d-grid gap-3">
                <button type="submit" class="btn btn-primary text-white mb-0 d-flex justify-content-center align-items-center gap-2">
                    Sign Up <i data-lucide="user-plus" class="icon-sm"></i>
                </button>
              </div>
              
              <p class="mt-4 text-center text-secondary micro-anim">Already have an account? <a href="{{ route('auth.login') }}" class="text-primary-custom">Sign in</a></p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('plugin-styles')
  <link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@push('custom-scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Account Created!',
            text: '{!! session('success') !!}',
            timer: 2000,
            showConfirmButton: false,
            scrollbarPadding: false,
            customClass: { popup: 'rounded-4' }
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}',
            timer: 3000,
            showConfirmButton: false,
            scrollbarPadding: false,
            customClass: { popup: 'rounded-4' }
        });
    @endif

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: `
                <ul class="text-start mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            `,
            timer: 4000,
            showConfirmButton: true,
            confirmButtonText: 'Ok',
            confirmButtonColor: '#e63946',
            scrollbarPadding: false,
            customClass: { popup: 'rounded-4' }
        });
    @endif
});
</script>
@endpush