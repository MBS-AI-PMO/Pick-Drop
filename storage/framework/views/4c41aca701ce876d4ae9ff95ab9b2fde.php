<?php $__env->startSection('content'); ?>

<?php
  $user = $verification->user;
  $isSelf = $verification->account_type === 'self';
  $commute = $user?->commuteProfile;
  $children = $user?->students ?? collect();
?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div class="d-flex align-items-center gap-3">
    <a href="<?php echo e(route('parent-self-verifications.document', [$verification, 'selfie_photo'])); ?>" target="_blank" class="flex-shrink-0">
      <img src="<?php echo e(route('parent-self-verifications.document', [$verification, 'selfie_photo'])); ?>"
           alt="Profile"
           class="rounded-circle border"
           style="width:64px;height:64px;object-fit:cover;">
    </a>
    <div>
      <h4 class="mb-1">Review <?php echo e($isSelf ? 'Self' : 'Parent'); ?> Verification</h4>
      <p class="text-secondary mb-0"><?php echo e($verification->full_name); ?> · <?php echo e($user?->email); ?></p>
    </div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?php echo e(route('parent-self-verifications.edit', $verification)); ?>" class="btn btn-primary">
      <i data-lucide="edit-2" class="icon-xs"></i>
      Edit / Update
    </a>
    <a href="<?php echo e(route('parent-self-verifications.index')); ?>" class="btn btn-outline-secondary">Back to list</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Personal Details</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="text-muted small">Full Name <span class="badge bg-light text-muted">from registration</span></label>
            <div class="fw-semibold"><?php echo e($user?->name ?? $verification->full_name); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Father Name</label>
            <div class="fw-semibold"><?php echo e($verification->father_name ?: '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Phone <span class="badge bg-light text-muted">from registration</span></label>
            <div class="fw-semibold"><?php echo e($verification->contactPhone() ?? '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Email <span class="badge bg-light text-muted">from registration</span></label>
            <div class="fw-semibold"><?php echo e($user?->email ?? '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Date of Birth</label>
            <div class="fw-semibold"><?php echo e($verification->date_of_birth?->format('d M Y')); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Gender</label>
            <div class="fw-semibold"><?php echo e($verification->gender ? ucfirst($verification->gender) : '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Nationality</label>
            <div class="fw-semibold"><?php echo e($verification->nationality ?: '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Country</label>
            <div class="fw-semibold"><?php echo e($verification->country ?: '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">City</label>
            <div class="fw-semibold"><?php echo e($verification->city?->name ?? $verification->city_name ?? '—'); ?></div>
          </div>
          <div class="col-md-6">
            <label class="text-muted small">Postal Code</label>
            <div class="fw-semibold"><?php echo e($verification->postal_code ?: '—'); ?></div>
          </div>
          <div class="col-12">
            <label class="text-muted small">Address</label>
            <div class="fw-semibold"><?php echo e($verification->address ?: '—'); ?></div>
          </div>
          <div class="col-12">
            <label class="text-muted small">Complete Address</label>
            <div class="fw-semibold"><?php echo e($verification->complete_address ?: '—'); ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Identity Verification (CNIC)</h6>
      </div>
      <div class="card-body">
        <p class="mb-3"><strong>CNIC:</strong> <code><?php echo e($verification->cnic_number); ?></code></p>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">CNIC Front</label>
            <a href="<?php echo e(route('parent-self-verifications.document', [$verification, 'cnic_front'])); ?>" target="_blank">
              <img src="<?php echo e(route('parent-self-verifications.document', [$verification, 'cnic_front'])); ?>"
                   alt="CNIC Front" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">CNIC Back</label>
            <a href="<?php echo e(route('parent-self-verifications.document', [$verification, 'cnic_back'])); ?>" target="_blank">
              <img src="<?php echo e(route('parent-self-verifications.document', [$verification, 'cnic_back'])); ?>"
                   alt="CNIC Back" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
          <div class="col-md-4">
            <label class="text-muted small d-block mb-2">Selfie / Face Photo</label>
            <a href="<?php echo e(route('parent-self-verifications.document', [$verification, 'selfie_photo'])); ?>" target="_blank">
              <img src="<?php echo e(route('parent-self-verifications.document', [$verification, 'selfie_photo'])); ?>"
                   alt="Selfie" class="img-fluid rounded border" style="max-height:180px;object-fit:cover;width:100%;">
            </a>
          </div>
        </div>
      </div>
    </div>

    <?php if($isSelf): ?>
      <div class="card mb-3">
        <div class="card-header">
          <h6 class="mb-0">Pickup, Drop &amp; Office Timing</h6>
        </div>
        <div class="card-body">
          <?php if($commute): ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="text-muted small">City</label>
                <div class="fw-semibold"><?php echo e($commute->city?->name ?? '—'); ?></div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Office Name</label>
                <div class="fw-semibold"><?php echo e($commute->office_name ?: '—'); ?></div>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Pickup</label>
                <div class="fw-semibold"><?php echo e($commute->pickup_point); ?></div>
                <small class="text-muted"><?php echo e($commute->pickupArea?->name); ?> · <?php echo e($commute->pickup_time ? substr((string) $commute->pickup_time, 0, 5) : '—'); ?></small>
              </div>
              <div class="col-md-6">
                <label class="text-muted small">Drop / Office</label>
                <div class="fw-semibold"><?php echo e($commute->drop_point); ?></div>
                <small class="text-muted"><?php echo e($commute->dropArea?->name); ?> · <?php echo e($commute->drop_time ? substr((string) $commute->drop_time, 0, 5) : '—'); ?></small>
              </div>
              <div class="col-12">
                <label class="text-muted small">Days</label>
                <div class="fw-semibold"><?php echo e(collect($commute->days ?? [])->map(fn ($d) => ucfirst($d))->implode(', ') ?: '—'); ?></div>
              </div>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">Not submitted yet. After approval, the self user will add pickup, drop location and office timing in the app.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="card mb-3">
        <div class="card-header">
          <h6 class="mb-0">Children</h6>
        </div>
        <div class="card-body p-0">
          <?php if($children->isNotEmpty()): ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4">Name</th>
                    <th>School</th>
                    <th>Grade</th>
                    <th>Emergency</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $__currentLoopData = $children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                      <td class="ps-4 fw-semibold"><?php echo e($child->name); ?></td>
                      <td><?php echo e($child->school_name ?: '—'); ?></td>
                      <td><?php echo e($child->grade ?: '—'); ?></td>
                      <td>
                        <?php echo e($child->emergency_name ?: '—'); ?>

                        <?php if($child->emergency_phone): ?>
                          <small class="text-muted d-block"><?php echo e($child->emergency_phone); ?></small>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0 p-3">No children added yet. After approval, the parent will add children details in the app.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">Account &amp; Status</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="text-muted small">Account Type</label>
          <div>
            <?php if($isSelf): ?>
              <span class="badge bg-info text-dark">Self</span>
            <?php else: ?>
              <span class="badge bg-primary">Parent</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Account Status</label>
          <div class="fw-semibold"><?php echo e($user?->status ?? '—'); ?></div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">KYC Status</label>
          <div>
            <?php if($verification->status === 'pending'): ?>
              <span class="badge bg-warning text-dark">Pending Verification</span>
            <?php elseif($verification->status === 'approved'): ?>
              <span class="badge rounded-pill px-3 py-1" style="background:#eef4ff;color:#3f6fd9;">Approved</span>
            <?php else: ?>
              <span class="badge bg-danger">Declined</span>
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Email Verified</label>
          <div class="fw-semibold"><?php echo e($user?->email_verified_at ? 'Yes' : 'No'); ?></div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Terms Accepted</label>
          <div class="fw-semibold">
            <?php echo e($verification->terms_accepted ? 'Yes' : 'No'); ?>

            <?php if($verification->terms_accepted_at): ?>
              <small class="text-muted d-block"><?php echo e($verification->terms_accepted_at->format('d M Y, h:i A')); ?></small>
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small">Submitted At</label>
          <div class="fw-semibold"><?php echo e($verification->created_at?->format('d M Y, h:i A')); ?></div>
        </div>
        <?php if($verification->reviewed_at): ?>
          <div class="mb-3">
            <label class="text-muted small">Reviewed At</label>
            <div class="fw-semibold"><?php echo e($verification->reviewed_at->format('d M Y, h:i A')); ?></div>
            <small class="text-muted">By <?php echo e($verification->reviewer?->name ?? 'Admin'); ?></small>
          </div>
        <?php endif; ?>
        <?php if($verification->rejection_reason): ?>
          <div class="alert alert-warning mb-0">
            <strong>Decline reason:</strong><br>
            <?php echo e($verification->rejection_reason); ?>

          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card verification-action-card">
      <div class="card-header">
        <h6 class="mb-0">Admin Actions</h6>
      </div>
      <div class="card-body">
        <a href="<?php echo e(route('parent-self-verifications.edit', $verification)); ?>" class="btn btn-outline-primary w-100 mb-3">
          <i data-lucide="edit-2" class="icon-xs"></i>
          Edit details / documents
        </a>
        <div class="verification-action-grid mb-3">
          <form method="POST" action="<?php echo e(route('parent-self-verifications.status', $verification)); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="status" value="approved">
            <button type="submit" class="btn btn-primary w-100"
                    <?php echo e($verification->status === 'approved' ? 'disabled' : ''); ?>

                    onclick="return confirm('Accept this verification? A verified email will be sent.')">
              <i data-lucide="check" class="icon-xs"></i>
              Accept
            </button>
          </form>

          <form method="POST" action="<?php echo e(route('parent-self-verifications.status', $verification)); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="status" value="pending">
            <button type="submit" class="btn btn-outline-secondary w-100"
                    <?php echo e($verification->status === 'pending' ? 'disabled' : ''); ?>

                    onclick="return confirm('Move this verification back to pending?')">
              <i data-lucide="clock" class="icon-xs"></i>
              Pending
            </button>
          </form>
        </div>

        <form method="POST" action="<?php echo e(route('parent-self-verifications.status', $verification)); ?>">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="status" value="rejected">
          <label class="form-label">Decline Reason</label>
          <textarea name="rejection_reason" class="form-control mb-2" rows="3"
                    <?php echo e($verification->status === 'rejected' ? 'disabled' : 'required'); ?>

                    placeholder="Explain why documents were declined..."><?php echo e(old('rejection_reason')); ?></textarea>
          <?php $__errorArgs = ['rejection_reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="text-danger small mb-2"><?php echo e($message); ?></div>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          <button type="submit" class="btn btn-outline-danger w-100"
                  <?php echo e($verification->status === 'rejected' ? 'disabled' : ''); ?>

                  onclick="return confirm('Decline this verification?')">
            <i data-lucide="x" class="icon-xs"></i>
            Decline
          </button>
        </form>

        <form method="POST" action="<?php echo e(route('parent-self-verifications.destroy', $verification)); ?>" class="mt-3"
              onsubmit="confirmDelete(event, this)">
          <?php echo csrf_field(); ?>
          <?php echo method_field('DELETE'); ?>
          <button type="submit" class="btn btn-danger w-100">
            <i data-lucide="trash-2" class="icon-xs"></i>
            Delete record
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('custom-scripts'); ?>
<script>
  <?php if(session('success')): ?>
  Swal.fire({
    icon: 'success',
    title: 'Updated',
    text: <?php echo json_encode(session('success'), 15, 512) ?>,
    confirmButtonText: 'OK',
    confirmButtonColor: '#3f6fd9',
    customClass: { popup: 'rounded-4' }
  });
  <?php endif; ?>

  <?php if(session('error')): ?>
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: <?php echo json_encode(session('error'), 15, 512) ?>,
    confirmButtonText: 'OK',
    confirmButtonColor: '#e63946',
    customClass: { popup: 'rounded-4' }
  });
  <?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/parent-self-verifications/show.blade.php ENDPATH**/ ?>