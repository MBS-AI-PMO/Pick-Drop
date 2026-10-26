{{-- One clean, single message for server-side validation errors, for every form in the portal.
     Included by both layouts. Replaces per-field messages and long bulleted popups:
       - shows ONE toast: the first error message (+ "(+N more)" when there are several),
       - keeps the red outline on invalid fields but hides the per-field text (only on pages that
         came back with errors, so client-side validation elsewhere is unaffected),
       - focuses the first invalid field and clears a field's outline as soon as it is edited.
     Pages must not add their own error popups. --}}
@php
    $formErrorMessages = collect($errors->getBags())
        ->flatMap(fn ($bag) => $bag->all())
        ->map(fn ($message) => trim((string) $message))
        ->filter()
        ->unique()
        ->values();
@endphp
@if($formErrorMessages->isNotEmpty())
<style>
  html.pd-has-form-errors .invalid-feedback { display: none !important; }
</style>
<script>
(function () {
  var messages = @json($formErrorMessages);
  if (!messages.length) return;
  document.documentElement.classList.add('pd-has-form-errors');

  var text = messages[0] + (messages.length > 1 ? ' (+' + (messages.length - 1) + ' more)' : '');

  function showMessage() {
    if (window.Swal) {
      Swal.fire({
        icon: 'error',
        title: text,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 6000,
        timerProgressBar: true,
        customClass: { popup: 'pd-toast pd-toast-single' },
        didOpen: function (toast) { toast.addEventListener('click', function () { Swal.close(); }); }
      });
      return;
    }
    // Fallback when SweetAlert is not on the page.
    var box = document.createElement('div');
    box.setAttribute('role', 'alert');
    box.textContent = text;
    box.style.cssText = 'position:fixed;top:16px;right:16px;z-index:2000;max-width:380px;padding:12px 16px;' +
      'border-radius:10px;background:#fff;color:#b42318;border:1px solid #f1c0bb;box-shadow:0 8px 24px rgba(15,23,42,.14);font-size:14px;';
    document.body.appendChild(box);
    setTimeout(function () { box.remove(); }, 6000);
  }

  function focusFirstInvalid() {
    var field = document.querySelector('.is-invalid');
    if (!field) return;
    var modal = field.closest('.modal');
    var focus = function () {
      field.scrollIntoView({ block: 'center', behavior: 'smooth' });
      try { field.focus({ preventScroll: true }); } catch (e) {}
    };
    if (modal && !modal.classList.contains('show')) {
      // Modal is (re)opening: focus once it has finished opening.
      modal.addEventListener('shown.bs.modal', focus, { once: true });
    } else {
      focus();
    }
  }

  function clearOnEdit() {
    document.querySelectorAll('.is-invalid').forEach(function (field) {
      var clear = function () { field.classList.remove('is-invalid'); };
      field.addEventListener('input', clear, { once: true });
      field.addEventListener('change', clear, { once: true });
    });
  }

  function start() {
    showMessage();
    focusFirstInvalid();
    clearOnEdit();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
</script>
@endif
