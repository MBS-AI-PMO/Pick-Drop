
<style>
  .pd-pw-wrap {
    position: relative;
    display: block;
    width: 100%;
  }
  .input-group > .pd-pw-wrap {
    flex: 1 1 auto;
    width: 1%;
    min-width: 0;
  }
  .pd-pw-wrap > input.pd-pw-input {
    padding-right: 2.75rem !important;
    width: 100%;
  }
  .pd-pw-wrap > input.pd-pw-input::-ms-reveal,
  .pd-pw-wrap > input.pd-pw-input::-ms-clear {
    display: none;
  }
  /* Bootstrap shows .invalid-feedback via `.is-invalid ~ .invalid-feedback`; the wrapper keeps that working. */
  .pd-pw-wrap.is-invalid ~ .invalid-feedback,
  .pd-pw-wrap.is-invalid ~ .text-danger {
    display: block;
  }
  .pd-pw-toggle {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 5;
    width: 42px;
    margin: 0;
    padding: 0;
    border: 0;
    border-radius: 0 8px 8px 0;
    background: transparent;
    color: var(--bs-secondary-color, #6c757d);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    line-height: 1;
    transform: none;
  }
  .pd-pw-toggle:hover,
  .pd-pw-toggle:focus-visible {
    color: var(--bs-body-color, #212529);
    outline: none;
    box-shadow: none;
    transform: none;
  }
  .pd-pw-toggle:focus-visible {
    box-shadow: inset 0 0 0 2px rgba(63, 111, 217, 0.55);
  }
  .pd-pw-toggle svg {
    width: 18px;
    height: 18px;
    display: block;
    pointer-events: none;
  }
  .pd-pw-toggle .pd-pw-eye-off { display: none; }
  .pd-pw-toggle.is-visible .pd-pw-eye { display: none; }
  .pd-pw-toggle.is-visible .pd-pw-eye-off { display: block; }
</style>
<script>
(function () {
  var EYE = '<svg class="pd-pw-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>';
  var EYE_OFF = '<svg class="pd-pw-eye-off" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>';

  function alreadyHasToggle(input) {
    var host = input.closest('.password-field');
    return !!(host && host.querySelector('.password-toggle'));
  }

  function setVisible(input, button, visible) {
    var start = null, end = null;
    try { start = input.selectionStart; end = input.selectionEnd; } catch (e) {}
    input.type = visible ? 'text' : 'password';
    button.classList.toggle('is-visible', visible);
    button.setAttribute('aria-pressed', visible ? 'true' : 'false');
    button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
    button.setAttribute('title', visible ? 'Hide password' : 'Show password');
    if (start !== null) {
      try { input.setSelectionRange(start, end); } catch (e) {}
    }
  }

  function enhance(input) {
    if (!input || input.dataset.pwToggle === '1' || input.hasAttribute('data-no-pw-toggle')) return;
    if (input.type !== 'password' || alreadyHasToggle(input)) return;
    input.dataset.pwToggle = '1';

    var wrap = document.createElement('div');
    wrap.className = 'pd-pw-wrap' + (input.classList.contains('is-invalid') ? ' is-invalid' : '');
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    input.classList.add('pd-pw-input');

    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'pd-pw-toggle';
    button.setAttribute('aria-label', 'Show password');
    button.setAttribute('aria-pressed', 'false');
    button.setAttribute('title', 'Show password');
    button.setAttribute('tabindex', '0');
    button.innerHTML = EYE + EYE_OFF;
    wrap.appendChild(button);

    // Keep the caret in the field while toggling.
    button.addEventListener('mousedown', function (e) { e.preventDefault(); });
    button.addEventListener('click', function () {
      setVisible(input, button, input.type === 'password');
      input.focus();
    });

    // Never leave a password visible after submit / back-forward navigation.
    if (input.form) {
      input.form.addEventListener('submit', function () { setVisible(input, button, false); });
      input.form.addEventListener('reset', function () { setVisible(input, button, false); });
    }
    // Bootstrap modals: hide again when the modal closes.
    var modal = input.closest('.modal');
    if (modal) {
      modal.addEventListener('hidden.bs.modal', function () { setVisible(input, button, false); });
    }
  }

  function enhanceAll(root) {
    var scope = root && root.querySelectorAll ? root : document;
    if (scope.matches && scope.matches('input[type="password"]')) enhance(scope);
    scope.querySelectorAll('input[type="password"]').forEach(enhance);
  }

  window.addEventListener('pageshow', function () {
    document.querySelectorAll('input[data-pw-toggle="1"]').forEach(function (input) {
      var btn = input.parentNode && input.parentNode.querySelector('.pd-pw-toggle');
      if (btn && input.type !== 'password') setVisible(input, btn, false);
    });
  });

  function start() {
    enhanceAll(document);
    // Forms/modals added after load.
    new MutationObserver(function (mutations) {
      mutations.forEach(function (m) {
        m.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) enhanceAll(node);
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
</script>
<?php /**PATH D:\xampp\htdocs\Pick-Drop\resources\views/layout/partials/password-toggle.blade.php ENDPATH**/ ?>