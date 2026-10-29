<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Platform settings</h4>
    <p class="text-secondary mb-0">SMS, push, JazzCash / EasyPaisa, cancellation, geofence, referral</p>
  </div>
</div>

<form method="POST" action="<?php echo e(route('platform-settings.update')); ?>">
  <?php echo csrf_field(); ?>
  <?php echo method_field('PUT'); ?>
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">SMS OTP</h6></div>
        <div class="card-body">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="sms_enabled" value="1" id="sms_enabled" <?php echo e(old('sms_enabled', $settings->sms_enabled) ? 'checked' : ''); ?>>
            <label class="form-check-label" for="sms_enabled">Enable SMS API</label>
          </div>
          <label class="form-label">Provider</label>
          <input class="form-control mb-3" name="sms_provider" value="<?php echo e(old('sms_provider', $settings->sms_provider)); ?>" placeholder="jazz / custom">
          <label class="form-label">API URL</label>
          <input class="form-control mb-3" name="sms_api_url" value="<?php echo e(old('sms_api_url', $settings->sms_api_url)); ?>" placeholder="https://...&phone={phone}&message={message}&key={key}">
          <label class="form-label">API key</label>
          <input class="form-control mb-3" name="sms_api_key" placeholder="Leave blank to keep current">
          <label class="form-label">Sender ID</label>
          <input class="form-control" name="sms_sender" value="<?php echo e(old('sms_sender', $settings->sms_sender)); ?>">
        </div>
      </div>
      <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">FCM push</h6></div>
        <div class="card-body">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="fcm_enabled" value="1" id="fcm_enabled" <?php echo e(old('fcm_enabled', $settings->fcm_enabled) ? 'checked' : ''); ?>>
            <label class="form-check-label" for="fcm_enabled">Enable Firebase push</label>
          </div>
          <label class="form-label">Server key</label>
          <input class="form-control" name="fcm_server_key" placeholder="Leave blank to keep current">
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h6 class="mb-0">Ops rules</h6></div>
        <div class="card-body">
          <label class="form-label">Cancel window (hours)</label>
          <input type="number" class="form-control mb-3" name="cancel_hours" value="<?php echo e(old('cancel_hours', $settings->cancel_hours)); ?>" required>
          <label class="form-label">Cancel fee %</label>
          <input type="number" step="0.01" class="form-control mb-3" name="cancel_fee_percent" value="<?php echo e(old('cancel_fee_percent', $settings->cancel_fee_percent)); ?>" required>
          <label class="form-label">Arrival geofence (meters)</label>
          <input type="number" class="form-control mb-3" name="geofence_meters" value="<?php echo e(old('geofence_meters', $settings->geofence_meters)); ?>" required>
          <label class="form-label">Referral bonus (PKR)</label>
          <input type="number" step="0.01" class="form-control mb-3" name="referral_bonus" value="<?php echo e(old('referral_bonus', $settings->referral_bonus)); ?>" required>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="pickup_otp_enabled" value="1" id="pickup_otp" <?php echo e(old('pickup_otp_enabled', $settings->pickup_otp_enabled) ? 'checked' : ''); ?>>
            <label class="form-check-label" for="pickup_otp">Require pickup OTP at stop</label>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">JazzCash</h6></div>
        <div class="card-body">
          <p class="text-secondary small mb-3">Users pay in the app with their JazzCash number and CNIC. The invoice is marked paid only after JazzCash confirms — not from a fake callback.</p>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="jazzcash_enabled" value="1" id="jc" <?php echo e(old('jazzcash_enabled', $settings->jazzcash_enabled) ? 'checked' : ''); ?>>
            <label class="form-check-label" for="jc">Enable JazzCash</label>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="jazzcash_sandbox" value="1" id="jc_sandbox" <?php echo e(old('jazzcash_sandbox', $settings->jazzcash_sandbox ?? true) ? 'checked' : ''); ?>>
            <label class="form-check-label" for="jc_sandbox">Use JazzCash sandbox (keep on until merchant is live)</label>
          </div>
          <label class="form-label">Merchant ID</label>
          <input class="form-control mb-3" name="jazzcash_merchant_id" value="<?php echo e(old('jazzcash_merchant_id', $settings->jazzcash_merchant_id)); ?>">
          <label class="form-label">Password</label>
          <input class="form-control mb-3" name="jazzcash_password" placeholder="Leave blank to keep current">
          <label class="form-label">Integrity salt</label>
          <input class="form-control mb-3" name="jazzcash_integrity_salt" placeholder="Leave blank to keep current">
          <label class="form-label">Return URL</label>
          <input class="form-control" name="jazzcash_return_url" value="<?php echo e(old('jazzcash_return_url', $settings->jazzcash_return_url)); ?>" placeholder="<?php echo e(url('/payments/jazzcash/callback')); ?>">
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h6 class="mb-0">EasyPaisa</h6></div>
        <div class="card-body">
          <p class="text-secondary small mb-3">Users pay in the app with their EasyPaisa number. The invoice is marked paid only after EasyPaisa confirms.</p>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="easypaisa_enabled" value="1" id="ep" <?php echo e(old('easypaisa_enabled', $settings->easypaisa_enabled) ? 'checked' : ''); ?>>
            <label class="form-check-label" for="ep">Enable EasyPaisa</label>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="easypaisa_sandbox" value="1" id="ep_sandbox" <?php echo e(old('easypaisa_sandbox', $settings->easypaisa_sandbox ?? true) ? 'checked' : ''); ?>>
            <label class="form-check-label" for="ep_sandbox">Use EasyPaisa sandbox (keep on until store is live)</label>
          </div>
          <label class="form-label">Store ID</label>
          <input class="form-control mb-3" name="easypaisa_store_id" value="<?php echo e(old('easypaisa_store_id', $settings->easypaisa_store_id)); ?>">
          <label class="form-label">Hash key</label>
          <input class="form-control mb-3" name="easypaisa_hash_key" placeholder="Leave blank to keep current">
          <label class="form-label">Return URL</label>
          <input class="form-control" name="easypaisa_return_url" value="<?php echo e(old('easypaisa_return_url', $settings->easypaisa_return_url)); ?>">
        </div>
      </div>
      <button class="btn btn-dark w-100 mt-3" type="submit">Save settings</button>
    </div>
  </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/settings/platform.blade.php ENDPATH**/ ?>