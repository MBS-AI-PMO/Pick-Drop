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
                    <i data-lucide="shield-check" style="width: 80px; height: 80px;"></i>
                </div>
                <h3 class="text-white fw-bold mb-2">Secure Recovery</h3>
                <p class="text-white-50">Regain access to track your child's journey.</p>
            </div>
          </div>
        </div>
        <div class="col-md-7 ps-md-0">
          <div class="auth-form-wrapper">
            <div class="micro-anim text-center text-md-start">
                <a href="<?php echo e(route('dashboard')); ?>" class="brand-logo mb-2">Pick<span>Drop</span></a>
                <h4 class="fw-bold mb-3 mt-2">Forgot Password?</h4>
                <p class="mb-4 text-secondary">
                  Enter your registered email address and we&apos;ll send you instructions to reset your password.
                </p>
            </div>
            
            <!-- Sweet Alert Logic -->
            
            <form class="forms-sample" method="POST" action="<?php echo e(route('auth.forgot-password.submit')); ?>">
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
              
              <div class="micro-anim mt-4">
                <button type="submit" class="btn btn-primary text-white w-100 mb-3 fs-6 d-flex justify-content-center align-items-center gap-2">
                    Send Reset Link
                </button>
              </div>
              
              <div class="text-center mt-4 micro-anim">
                <a href="<?php echo e(route('login')); ?>" class="text-secondary text-primary-custom d-inline-flex align-items-center gap-1">
                    <i data-lucide="arrow-left" class="icon-sm"></i> Back to Login
                </a>
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
            title: 'Reset Link Sent!',
            text: '<?php echo session('success'); ?>',
            timer: 3000,
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
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master2', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/pages/auth/forgot-password.blade.php ENDPATH**/ ?>