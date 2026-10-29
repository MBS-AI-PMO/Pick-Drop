<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Calendar</h4>
    <p class="text-secondary mb-0">Holidays, scheduled trips, skips and cover drivers in one calendar.</p>
  </div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#announceOffDayModal">
    <i data-lucide="plus" class="icon-sm me-1"></i> Announce off day
  </button>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
          <div class="d-flex align-items-center gap-2">
            <a href="<?php echo e($prevMonthUrl); ?>" class="btn btn-outline-secondary btn-sm" title="Previous month">
              <i data-lucide="chevron-left" style="width:16px;height:16px;"></i>
            </a>
            <h5 class="mb-0 fw-bold"><?php echo e($cursor->format('F Y')); ?></h5>
            <a href="<?php echo e($nextMonthUrl); ?>" class="btn btn-outline-secondary btn-sm" title="Next month">
              <i data-lucide="chevron-right" style="width:16px;height:16px;"></i>
            </a>
          </div>
          <a href="<?php echo e($todayUrl); ?>" class="btn btn-outline-secondary btn-sm">Today</a>
        </div>

        <div class="pd-cal-weekdays">
          <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </div>
        <div class="pd-cal-grid">
          <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $isPast = \Illuminate\Support\Carbon::parse($day['date'])->lt(now()->startOfDay()); ?>
            <button type="button"
                    class="pd-cal-day <?php echo e($day['in_month'] ? '' : 'is-muted'); ?> <?php echo e($day['is_today'] ? 'is-today' : ''); ?> <?php echo e($day['holidays']->isNotEmpty() ? 'has-off' : ''); ?> <?php echo e($isPast ? 'is-past' : ''); ?>"
                    data-date="<?php echo e($day['date']); ?>"
                    title="<?php echo e($isPast ? 'Past dates cannot be selected' : 'Announce off day on '.\Illuminate\Support\Carbon::parse($day['date'])->format('d M Y')); ?>">
              <span class="pd-cal-num"><?php echo e($day['label']); ?></span>
              
              <?php $__currentLoopData = $day['holidays']->groupBy(fn ($h) => $h->name.'|'.$h->type); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $first = $group->first(); ?>
                <span class="pd-cal-chip pd-cal-chip-<?php echo e($first->type); ?>"
                      title="<?php echo e($first->name); ?> · <?php echo e($group->map(fn ($h) => $h->city?->name ?? 'All cities')->unique()->implode(', ')); ?>"><?php echo e($first->name); ?></span>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php if(($day['trips'] ?? 0) > 0): ?>
                <span class="pd-cal-chip pd-cal-chip-trip"><?php echo e($day['trips']); ?> trips</span>
              <?php endif; ?>
              <?php if(($day['skips'] ?? 0) > 0): ?>
                <span class="pd-cal-chip pd-cal-chip-skip"><?php echo e($day['skips']); ?> skip</span>
              <?php endif; ?>
              <?php if(($day['covers'] ?? 0) > 0): ?>
                <span class="pd-cal-chip pd-cal-chip-cover"><?php echo e($day['covers']); ?> cover</span>
              <?php endif; ?>
            </button>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="d-flex flex-wrap gap-3 mt-3 small text-secondary">
          <span class="d-flex align-items-center gap-1"><span class="pd-cal-dot pd-cal-chip-public"></span> Public holiday</span>
          <span class="d-flex align-items-center gap-1"><span class="pd-cal-dot pd-cal-chip-school"></span> Institution holiday</span>
          <span class="d-flex align-items-center gap-1"><span class="pd-cal-dot pd-cal-chip-custom"></span> Announced off day</span>
          <span class="d-flex align-items-center gap-1"><span class="pd-cal-dot pd-cal-chip-trip"></span> Scheduled trips</span>
          <span class="d-flex align-items-center gap-1"><span class="pd-cal-dot pd-cal-chip-skip"></span> Skips</span>
          <span class="d-flex align-items-center gap-1"><span class="pd-cal-dot pd-cal-chip-cover"></span> Cover drivers</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Announced this month</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td class="ps-3">
                    <div class="fw-semibold"><?php echo e($holiday->name); ?></div>
                    <div class="text-secondary small"><?php echo e($holiday->date?->format('d M Y')); ?> · <?php echo e($holiday->typeLabel()); ?> · <?php echo e($holiday->city?->name ?? 'All cities'); ?></div>
                  </td>
                  <td class="text-end pe-3">
                    <form method="POST" action="<?php echo e(route('holidays.destroy', $holiday)); ?>"
                          onsubmit="confirmAnnouncementRemove(event, this, <?php echo e(Js::from($holiday->name)); ?>, <?php echo e(Js::from(($holiday->city?->name ?? 'All cities').' · '.$holiday->date?->format('d M Y'))); ?>)">
                      <?php echo csrf_field(); ?>
                      <?php echo method_field('DELETE'); ?>
                      <input type="hidden" name="year" value="<?php echo e($cursor->year); ?>">
                      <input type="hidden" name="month" value="<?php echo e($cursor->month); ?>">
                      <button class="action-btn action-btn-delete" type="submit" title="Remove">
                        <i data-lucide="trash-2"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td class="text-center py-5 text-muted">No holidays or off days announced for <?php echo e($cursor->format('F')); ?>.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="announceOffDayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Announce off day</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="<?php echo e(route('holidays.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="modal-body pt-2">
          <p class="text-secondary small">Pickups in the selected cities will be skipped on this date.</p>
          <label class="form-label">Date</label>
          <input type="date" name="date" id="holiday_date" min="<?php echo e(now()->toDateString()); ?>" class="form-control mb-3 <?php echo e($errors->has('date') ? 'is-invalid' : ''); ?>" value="<?php echo e(old('date')); ?>" required>
          <label class="form-label">Title</label>
          <input type="text" name="name" class="form-control mb-3 <?php echo e($errors->has('name') ? 'is-invalid' : ''); ?>" value="<?php echo e(old('name')); ?>" placeholder="e.g. Eid holiday, Summer break" required>
          <label class="form-label">Type</label>
          <select name="type" class="form-select mb-3" required>
            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($value); ?>" <?php echo e(old('type', 'custom') === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <?php
            $hasOld = session()->hasOldInput();
            $allChecked = $hasOld ? (bool) old('all_cities') : true;
            $oldCityIds = array_map('strval', (array) old('city_ids', []));
          ?>
          <label class="form-label">Cities <span class="text-danger">*</span></label>
          
          <div id="cityPicker">
            <button type="button" id="cityPickerBtn"
                    class="form-select text-start <?php echo e($errors->has('city_ids') ? 'is-invalid' : ''); ?>"
                    data-bs-toggle="collapse" data-bs-target="#cityPickerMenu" aria-expanded="false" aria-controls="cityPickerMenu">
              <span id="cityPickerLabel">All cities</span>
            </button>
            <div class="collapse" id="cityPickerMenu">
             <div class="border rounded p-2 mt-1" style="max-height:200px;overflow-y:auto;">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="all_cities" value="1" id="cityAll" <?php echo e($allChecked ? 'checked' : ''); ?>>
                <label class="form-check-label fw-semibold" for="cityAll">All cities</label>
              </div>
              <hr class="my-2">
              <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="form-check">
                  <input class="form-check-input city-option" type="checkbox" name="city_ids[]" value="<?php echo e($city->id); ?>"
                         id="city_opt_<?php echo e($city->id); ?>" data-name="<?php echo e($city->name); ?>"
                         <?php echo e(in_array((string) $city->id, $oldCityIds, true) ? 'checked' : ''); ?>>
                  <label class="form-check-label" for="city_opt_<?php echo e($city->id); ?>"><?php echo e($city->name); ?></label>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             </div>
            </div>
          </div>
          <div class="form-text">Select one or more cities, or all cities.</div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4">Announce</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('style'); ?>
<style>
  .pd-cal-weekdays,
  .pd-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
  }
  .pd-cal-weekdays {
    margin-bottom: 6px;
  }
  .pd-cal-weekdays span {
    font-size: 12px;
    font-weight: 600;
    color: var(--pd-muted, #697586);
    text-align: center;
    padding: 4px 0;
  }
  .pd-cal-grid {
    gap: 6px;
  }
  .pd-cal-day {
    min-height: 92px;
    border: 1px solid var(--pd-border, #e6ebf2);
    border-radius: 10px;
    background: var(--pd-card-bg, #fff);
    padding: 8px;
    text-align: left;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    cursor: pointer;
    transition: border-color .12s ease, box-shadow .12s ease, transform .12s ease;
  }
  .pd-cal-day:hover {
    border-color: #3f6fd9;
    box-shadow: 0 6px 16px rgba(63, 111, 217, .08);
    transform: translateY(-1px);
  }
  .pd-cal-day.is-muted {
    opacity: .38;
  }
  .pd-cal-day.is-past {
    cursor: not-allowed;
  }
  .pd-cal-day.is-past:hover {
    border-color: var(--pd-border, #e6ebf2);
    box-shadow: none;
    transform: none;
  }
  .pd-cal-day.is-today {
    border-color: #3f6fd9;
    box-shadow: inset 0 0 0 1px #3f6fd9;
  }
  .pd-cal-day.has-off {
    background: #fbf8f2;
  }
  .pd-cal-num {
    font-size: 13px;
    font-weight: 700;
    color: var(--pd-text, #1f2937);
  }
  .pd-cal-chip {
    display: block;
    width: 100%;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.25;
    border-radius: 6px;
    padding: 3px 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .pd-cal-chip-public { background: #fde8ee; color: #9f1239; }
  .pd-cal-chip-school { background: #eef4ff; color: #1d4ed8; }
  .pd-cal-chip-custom { background: #fff4db; color: #92400e; }
  .pd-cal-chip-trip { background: #e8f7ef; color: #166534; }
  .pd-cal-chip-skip { background: #f3f4f6; color: #4b5563; }
  .pd-cal-chip-cover { background: #ede9fe; color: #5b21b6; }
  .pd-cal-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    display: inline-block;
  }
  [data-bs-theme="dark"] .pd-cal-day {
    background: var(--pd-theme-card, #1e2129);
    border-color: var(--pd-theme-border, rgba(255,255,255,.08));
  }
  [data-bs-theme="dark"] .pd-cal-day.has-off {
    background: rgba(217, 119, 6, .12);
  }
  [data-bs-theme="dark"] .pd-cal-num {
    color: #f4f7fb;
  }
  @media (max-width: 767.98px) {
    .pd-cal-day { min-height: 72px; padding: 6px; }
    .pd-cal-chip { font-size: 10px; }
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('custom-scripts'); ?>
<script>
  // Styled in-app confirmation (replaces the browser's confirm() popup).
  function confirmAnnouncementRemove(event, form, name, details) {
    event.preventDefault();
    Swal.fire({
      title: 'Remove announcement?',
      html: '<strong></strong><br><span class="text-secondary"></span>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, remove it',
      cancelButtonText: 'Cancel',
      focusCancel: true,
      scrollbarPadding: false,
      customClass: { popup: 'rounded-4' },
      didOpen: function (popup) {
        // textContent keeps titles containing quotes or HTML safe
        popup.querySelector('.swal2-html-container strong').textContent = name;
        popup.querySelector('.swal2-html-container span').textContent = details;
      }
    }).then(function (result) {
      if (result.isConfirmed) form.submit();
    });
  }

  var PAST_DATE_MESSAGE = 'Past dates cannot be selected. Choose today or a later date.';
  var todayStr = <?php echo json_encode(now()->toDateString(), 15, 512) ?>;

  function showPastDateMessage() {
    if (window.Swal) {
      Swal.fire({
        icon: 'error', title: PAST_DATE_MESSAGE, toast: true, position: 'top-end',
        showConfirmButton: false, timer: 4000, timerProgressBar: true,
        customClass: { popup: 'pd-toast pd-toast-single' }
      });
    }
  }

  // Calendar day click: past days cannot be announced.
  document.querySelectorAll('.pd-cal-day').forEach(function (day) {
    day.addEventListener('click', function () {
      var date = this.getAttribute('data-date');
      if (date < todayStr) {
        showPastDateMessage();
        return;
      }
      var dateInput = document.getElementById('holiday_date');
      if (dateInput) {
        dateInput.value = date;
        dateInput.setCustomValidity('');
        dateInput.classList.remove('is-invalid');
      }
      var modal = new bootstrap.Modal(document.getElementById('announceOffDayModal'));
      modal.show();
    });
  });

  // Date input: same message when a past date is typed or picked.
  (function () {
    var dateInput = document.getElementById('holiday_date');
    if (!dateInput) return;
    function check() {
      var past = dateInput.value && dateInput.value < todayStr;
      dateInput.setCustomValidity(past ? PAST_DATE_MESSAGE : '');
      dateInput.classList.toggle('is-invalid', !!past);
    }
    dateInput.addEventListener('input', check);
    dateInput.addEventListener('change', check);
    dateInput.addEventListener('invalid', function () {
      if (dateInput.value && dateInput.value < todayStr) dateInput.classList.add('is-invalid');
    });
  })();

  // Multi-city picker: "All cities" or any number of specific cities.
  (function () {
    var allBox = document.getElementById('cityAll');
    var label = document.getElementById('cityPickerLabel');
    var button = document.getElementById('cityPickerBtn');
    var options = Array.prototype.slice.call(document.querySelectorAll('.city-option'));
    if (!allBox || !label) return;

    function selectedOptions() {
      return options.filter(function (box) { return box.checked; });
    }

    function refreshLabel() {
      var picked = selectedOptions();
      if (allBox.checked) {
        label.textContent = 'All cities';
      } else if (picked.length === 0) {
        label.textContent = 'Select cities';
      } else if (picked.length <= 2) {
        label.textContent = picked.map(function (b) { return b.getAttribute('data-name'); }).join(', ');
      } else {
        label.textContent = picked.length + ' cities selected';
      }
      if (button && (allBox.checked || picked.length)) button.classList.remove('is-invalid');
    }

    allBox.addEventListener('change', function () {
      if (allBox.checked) {
        options.forEach(function (box) { box.checked = false; });
      } else if (selectedOptions().length === 0) {
        // never leave the picker empty: fall back to "All cities"
        allBox.checked = true;
      }
      refreshLabel();
    });

    options.forEach(function (box) {
      box.addEventListener('change', function () {
        if (box.checked) {
          allBox.checked = false;
        } else if (selectedOptions().length === 0) {
          allBox.checked = true;
        }
        refreshLabel();
      });
    });

    refreshLabel();
  })();

  <?php if($errors->any()): ?>
  document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('announceOffDayModal')).show();
  });
  <?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layout.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Pick-Drop\resources\views/pickdrop/holidays/index.blade.php ENDPATH**/ ?>