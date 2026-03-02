@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('build/plugins/prism-themes/prism-coldark-dark.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="row">
  <div class="col-xl-10 main-content ps-xl-4 pe-xl-5">
    <h1 class="page-title">Toasts</h1>
    <p class="lead">Push notifications to your visitors with a toast, a lightweight and easily customizable alert message. Read the <a href="https://getbootstrap.com/docs/5.3/components/toasts/" target="_blank">Official Bootstrap Documentation</a> for a full list of instructions and other options.</p>
    
    <hr>
    
    <h4 id="default">Live Example</h4>
    <p class="mb-3">Click the button below to show a toast (positioned with our utilities in the lower right corner) that has been hidden by default.</p>
    <div class="example">
      <button type="button" class="btn btn-primary" id="liveToastBtn">Show live toast</button>

      <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-header">
            <svg class="bd-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMid slice"><rect width="100%" height="100%" fill="#007aff"></rect></svg>
            <strong class="me-auto">Bootstrap</strong>
            <small>11 mins ago</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">
            Hello, world! This is a toast message.
          </div>
        </div>
      </div>

    </div>
    <figure class="highlight" id="Default">
<pre><code class="language-markup"><script type="script/prism-html-markup"><button type="button" class="btn btn-primary" id="liveToastBtn">Show live toast</button>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <img src="..." class="rounded me-2" alt="...">
      <strong class="me-auto">Bootstrap</strong>
      <small>11 mins ago</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      Hello, world! This is a toast message.
    </div>
  </div>
</div></script></code></pre>
      <button type="button" class="btn btn-clipboard" data-clipboard-target="#Default">copy</button>
    </figure>

    <p class="mb-3">We use the following JavaScript to trigger our live toast demo:</p>

    <figure class="highlight" id="DefaultJs">
<pre><code class="language-javascript"><script type="script/prism-html-markup">const toastTrigger = document.getElementById('liveToastBtn')
const toastLiveExample = document.getElementById('liveToast')

if (toastTrigger) {
  const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
  toastTrigger.addEventListener('click', () => {
    toastBootstrap.show()
  })
}</script></code></pre>
      <button type="button" class="btn btn-clipboard" data-clipboard-target="#DefaultJs">copy</button>
    </figure>
    
  </div>
  <div class="col-xl-2 content-nav-wrapper">
    <ul class="nav content-nav d-flex flex-column">
      <li class="nav-item">
        <a href="#default" class="nav-link">Example</a>
      </li>
      
    </ul>
  </div>
</div>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('build/plugins/prismjs/prism.js') }}"></script>
  <script src="{{ asset('build/plugins/clipboard/clipboard.min.js') }}"></script>
@endpush

@push('custom-scripts')
  <script>
    const toastTrigger = document.getElementById('liveToastBtn')
    const toastLiveExample = document.getElementById('liveToast')

    if (toastTrigger) {
      const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
      toastTrigger.addEventListener('click', () => {
        toastBootstrap.show()
      })
    }
  </script>
@endpush