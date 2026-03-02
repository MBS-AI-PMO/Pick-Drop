@extends('layout.master2')

@section('content')
<div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto">
    <div class="card">
      <div class="row">
        <div class="col-md-4 pe-md-0">
          <div class="auth-side-wrapper" style="background-image: url({{ url('build/images/photos/img6.jpg') }})">

          </div>
        </div>
        <div class="col-md-8 ps-md-0">
          <div class="auth-form-wrapper px-4 py-5">
            <a href="#" class="nobleui-logo d-block mb-2">Noble<span>UI</span></a>
            <h4 class="mb-4">Forgot your password?</h4>
            <p class="mb-4 text-secondary">
              Enter your email address and we&apos;ll send you instructions for resetting your password.
            </p>
            <form class="forms-sample">
              <div class="mb-3">
                <label for="userEmail" class="form-label">Email address</label>
                <input type="email" class="form-control" id="userEmail" placeholder="Email">
              </div>
              <div>
                <a href="{{ url('/') }}" class="btn btn-primary me-2 mb-2 mb-md-0 text-white">Reset
                  Password</a>
                <a href="{{ url('/auth/login') }}" class="btn btn-link">Back to Login</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection