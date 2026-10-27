<?php $__env->startPush('style'); ?>
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
    color: #e63946; /* Vibrant Red */
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

.form-check-input:checked {
    background-color: #e63946;
    border-color: #e63946;
}

.password-field.input-group > .form-control {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
.password-field.input-group > .password-toggle {
    margin-left: -1px;
    border: 1px solid #dee2e6;
    background: #f8f9fa;
    color: #6c757d;
    border-radius: 0 12px 12px 0;
    padding: 0 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.password-field.input-group > .password-toggle svg {
    width: 18px;
    height: 18px;
}
.password-field.input-group > .password-toggle:hover,
.password-field.input-group > .password-toggle:focus {
    background: #f8f9fa;
    color: #1d3557;
    border-color: #dee2e6;
    box-shadow: none;
    outline: none;
}
.password-field.input-group > .form-control:focus {
    box-shadow: none;
    z-index: 3;
}
.password-field.input-group:focus-within {
    border-radius: 12px;
    box-shadow: 0 0 0 4px rgba(230, 57, 70, 0.15);
}
.password-field.input-group:focus-within > .form-control,
.password-field.input-group:focus-within > .password-toggle {
    border-color: #e63946;
    background: #fff;
}
[data-bs-theme="dark"] .password-field.input-group > .password-toggle {
    background: #252833;
    border-color: #323644;
    color: #adb5bd;
}
[data-bs-theme="dark"] .password-field.input-group > .password-toggle:hover,
[data-bs-theme="dark"] .password-field.input-group > .password-toggle:focus {
    background: #252833;
    border-color: #323644;
    color: #f1faee;
}
[data-bs-theme="dark"] .password-field.input-group:focus-within > .form-control,
[data-bs-theme="dark"] .password-field.input-group:focus-within > .password-toggle {
    background: #252833;
    border-color: #ff4d6d;
}
[data-bs-theme="dark"] .password-field.input-group:focus-within {
    box-shadow: 0 0 0 4px rgba(255, 77, 109, 0.15);
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

@media (max-width: 575.98px) {
    .auth-options-row {
        align-items: flex-start !important;
        flex-direction: column;
        gap: 0.85rem;
    }
}

@keyframes fadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
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
                    <i data-lucide="bus" style="width: 80px; height: 80px;"></i>
                </div>
                <h3 class="text-white fw-bold mb-2">Safe & Secure</h3>
                <p class="text-white-50">Student transport solutions tailored for peace of mind.</p>
            </div>
          </div>
        </div>
        <div class="col-md-7 ps-md-0">
          <div class="auth-form-wrapper">
            <div class="micro-anim text-center text-md-start">
                <a href="<?php echo e(route('dashboard')); ?>" class="brand-logo mb-2">Pick<span>Drop</span></a>
                <h5 class="text-secondary fw-normal mb-4">Welcome back! Log in to your account.</h5>
            </div>
            
            <!-- Sweet Alert Logic -->
            
            <form class="forms-sample" method="POST" action="<?php echo e(route('auth.login.submit')); ?>">
                <?php echo csrf_field(); ?>
              <div class="mb-4 micro-anim">
                <label for="userEmail" class="form-label fw-medium">Email address</label>
                <input
                    type="email"
                    class="form-control"
                    id="userEmail"
                    name="email"
                    value="<?php echo e(old('email')); ?>"
                    placeholder="name@company.com"
                    required
                    autocomplete="email"
                    autofocus
                >
              </div>
              <div class="mb-4 micro-anim">
                  <label for="userPassword" class="form-label fw-medium">Password</label>
              
                  <div class="input-group password-field">
                      <input
                          type="password"
                          class="form-control"
                          id="userPassword"
                          name="password"
                          autocomplete="current-password"
                          placeholder="••••••••"
                          required
                      >
              
                      <button
                          class="password-toggle"
                          type="button"
                          id="togglePassword"
                          aria-label="Show password"
                      >
                          <i data-lucide="eye" id="passwordIcon"></i>
                      </button>
                  </div>
              </div>
              <div class="mb-4 d-flex justify-content-between align-items-center micro-anim auth-options-row">
                <div class="form-check">
                  <input
                      type="checkbox"
                      class="form-check-input"
                      id="authCheck"
                      name="remember"
                      <?php echo e(old('remember') ? 'checked' : ''); ?>

                  >
                  <label class="form-check-label" for="authCheck">
                    Remember me
                  </label>
                </div>
                <a href="<?php echo e(route('auth.forgot-password')); ?>" class="text-primary-custom text-sm">Forgot password?</a>
              </div>
             
              <div class="micro-anim mt-2">
                <button type="submit" class="btn btn-primary text-white w-100 mb-3 fs-6 d-flex justify-content-center align-items-center gap-2">
                    Log In
                </button>
              </div>
              
              
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('plugin-scripts'); ?>
  <script src="<?php echo e(asset('build/plugins/sweetalert2/sweetalert2.min.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('plugin-styles'); ?>
  <link href="<?php echo e(asset('build/plugins/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" />
<?php $__env->stopPush(); ?>

<?php $__env->startPush('custom-scripts'); ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php if(session('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Welcome Back!',
            text: '<?php echo session('success'); ?>',
            timer: 2000,
            showConfirmButton: false,
            scrollbarPadding: false,
            customClass: { popup: 'rounded-4' }
        });
    <?php endif; ?>

    <?php if(session('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo e(session('error')); ?>',
            timer: 3000,
            showConfirmButton: false,
            scrollbarPadding: false,
            customClass: { popup: 'rounded-4' }
        });
    <?php endif; ?>

    <?php if($errors->any()): ?>
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: `
                <ul class="text-start mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            `,
            timer: 4000,
            showConfirmButton: true,
            confirmButtonText: 'Ok',
            confirmButtonColor: '#e63946',
            scrollbarPadding: false,
            customClass: { popup: 'rounded-4' }
        });
    <?php endif; ?>
    const passwordField = document.getElementById('userPassword');
const togglePassword = document.getElementById('togglePassword');
const passwordIcon = document.getElementById('passwordIcon');

togglePassword.addEventListener('click', function () {

    const type = passwordField.getAttribute('type') === 'password'
        ? 'text'
        : 'password';

    passwordField.setAttribute('type', type);

    passwordIcon.setAttribute(
        'data-lucide',
        type === 'password' ? 'eye' : 'eye-off'
    );

    lucide.createIcons();
});
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master2', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pages/auth/login.blade.php ENDPATH**/ ?>