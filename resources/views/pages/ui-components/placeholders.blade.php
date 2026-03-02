@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('build/plugins/prism-themes/prism-coldark-dark.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="row">
  <div class="col-xl-10 main-content ps-xl-4 pe-xl-5">
    <h1 class="page-title">Placeholders</h1>
    <p class="lead">Use loading placeholders for your components or pages to indicate something may still be loading. Read the <a href="https://getbootstrap.com/docs/5.3/components/placeholders/" target="_blank">Official Bootstrap Documentation</a> for a full list of instructions and other options.</p>
    
    <hr>
    
    <h4 id="default">Example</h4>
    <p class="mb-3">In the example below, we take a typical card component and recreate it with placeholders applied to create a “loading card”. Size and proportions are the same between the two.</p>
    <div class="example">
      <div class="d-flex justify-content-around">
        <div class="card w-250px">
          <img src="{{ url('build/images/others/placeholder.jpg') }}" class="card-img-top" alt="...">
        
          <div class="card-body">
            <h5 class="card-title">Card title</h5>
            <p class="card-text mb-3">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
            <a href="#" class="btn btn-primary">Go somewhere</a>
          </div>
        </div>
        <div class="card w-250px" aria-hidden="true">
          <img src="{{ url('build/images/others/placeholder.jpg') }}" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title placeholder-glow">
              <span class="placeholder col-6"></span>
            </h5>
            <p class="card-text placeholder-glow mb-3">
              <span class="placeholder col-7"></span>
              <span class="placeholder col-4"></span>
              <span class="placeholder col-4"></span>
              <span class="placeholder col-6"></span>
              <span class="placeholder col-8"></span>
              <span class="placeholder col-3"></span>
              <span class="placeholder col-8"></span>
            </p>
            <a class="btn btn-primary disabled placeholder col-6" aria-disabled="true"></a>
          </div>
        </div>
      </div>
    </div>
    <figure class="highlight" id="Default">
<pre><code class="language-markup"><script type="script/prism-html-markup"><div class="card">
  <img src="..." class="card-img-top" alt="...">

  <div class="card-body">
    <h5 class="card-title">Card title</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
    <a href="#" class="btn btn-primary">Go somewhere</a>
  </div>
</div>

<div class="card" aria-hidden="true">
  <img src="..." class="card-img-top" alt="...">
  <div class="card-body">
    <h5 class="card-title placeholder-glow">
      <span class="placeholder col-6"></span>
    </h5>
    <p class="card-text placeholder-glow">
      <span class="placeholder col-7"></span>
      <span class="placeholder col-4"></span>
      <span class="placeholder col-4"></span>
      <span class="placeholder col-6"></span>
      <span class="placeholder col-8"></span>
    </p>
    <a class="btn btn-primary disabled placeholder col-6" aria-disabled="true"></a>
  </div>
</div></script></code></pre>
      <button type="button" class="btn btn-clipboard" data-clipboard-target="#Default">copy</button>
    </figure>
    
    <hr>
    
    <h4 id="width">Width</h4>
    <p class="mb-3">You can change the <code>width</code> through grid column classes, width utilities, or inline styles.</p>
    <div class="example">
      <span class="placeholder col-6"></span>
      <span class="placeholder w-75"></span>
      <span class="placeholder" style="width: 25%;"></span>
    </div>
    <figure class="highlight" id="Width">
<pre><code class="language-markup"><script type="script/prism-html-markup"><span class="placeholder col-6"></span>
<span class="placeholder w-75"></span>
<span class="placeholder" style="width: 25%;"></span></script></code></pre>
      <button type="button" class="btn btn-clipboard" data-clipboard-target="#Width">copy</button>
    </figure>

    <hr>
    
    <h4 id="color">Color</h4>
    <p class="mb-3">By default, the <code>placeholder</code> uses <code>currentColor</code>. This can be overridden with a custom color or utility class.</p>
    <div class="example">
      <span class="placeholder col-12"></span>

      <span class="placeholder col-12 bg-primary"></span>
      <span class="placeholder col-12 bg-secondary"></span>
      <span class="placeholder col-12 bg-success"></span>
      <span class="placeholder col-12 bg-danger"></span>
      <span class="placeholder col-12 bg-warning"></span>
      <span class="placeholder col-12 bg-info"></span>
      <span class="placeholder col-12 bg-light"></span>
      <span class="placeholder col-12 bg-dark"></span>
    </div>
    <figure class="highlight" id="Color">
<pre><code class="language-markup"><script type="script/prism-html-markup"><span class="placeholder col-12"></span>

  <span class="placeholder col-12 bg-primary"></span>
  <span class="placeholder col-12 bg-secondary"></span>
  <span class="placeholder col-12 bg-success"></span>
  <span class="placeholder col-12 bg-danger"></span>
  <span class="placeholder col-12 bg-warning"></span>
  <span class="placeholder col-12 bg-info"></span>
  <span class="placeholder col-12 bg-light"></span>
  <span class="placeholder col-12 bg-dark"></span></script></code></pre>
      <button type="button" class="btn btn-clipboard" data-clipboard-target="#Color">copy</button>
    </figure>

    <hr>
    
    <h4 id="sizing">Sizing</h4>
    <p class="mb-3">The size of <code>.placeholder</code>s are based on the typographic style of the parent element. Customize them with sizing modifiers: <code>.placeholder-lg</code>, <code>.placeholder-sm</code>, or <code>.placeholder-xs</code>.</p>
    <div class="example">
      <span class="placeholder col-12 placeholder-lg"></span>
      <span class="placeholder col-12"></span>
      <span class="placeholder col-12 placeholder-sm"></span>
      <span class="placeholder col-12 placeholder-xs"></span>
    </div>
    <figure class="highlight" id="Sizing">
<pre><code class="language-markup"><script type="script/prism-html-markup"><span class="placeholder col-12 placeholder-lg"></span>
<span class="placeholder col-12"></span>
<span class="placeholder col-12 placeholder-sm"></span>
<span class="placeholder col-12 placeholder-xs"></span></script></code></pre>
      <button type="button" class="btn btn-clipboard" data-clipboard-target="#Sizing">copy</button>
    </figure>

    <hr>
    
    <h4 id="animation">Animation</h4>
    <p class="mb-3">The size of <code>.placeholder</code>s are based on the typographic style of the parent element. Customize them with sizing modifiers: <code>.placeholder-lg</code>, <code>.placeholder-sm</code>, or <code>.placeholder-xs</code>.</p>
    <div class="example">
      <p class="placeholder-glow">
        <span class="placeholder col-12"></span>
      </p>
      
      <p class="placeholder-wave">
        <span class="placeholder col-12"></span>
      </p>
    </div>
    <figure class="highlight" id="Animation">
<pre><code class="language-markup"><script type="script/prism-html-markup"><p class="placeholder-glow">
  <span class="placeholder col-12"></span>
</p>

<p class="placeholder-wave">
  <span class="placeholder col-12"></span>
</p></script></code></pre>
      <button type="button" class="btn btn-clipboard" data-clipboard-target="#Animation">copy</button>
    </figure>
    
  </div>
  <div class="col-xl-2 content-nav-wrapper">
    <ul class="nav content-nav d-flex flex-column">
      <li class="nav-item">
        <a href="#default" class="nav-link">Example</a>
      </li>
      <li class="nav-item">
        <a href="#width" class="nav-link">Width</a>
      </li>
      <li class="nav-item">
        <a href="#color" class="nav-link">Color</a>
      </li>
      <li class="nav-item">
        <a href="#sizing" class="nav-link">Sizing</a>
      </li>
      <li class="nav-item">
        <a href="#animation" class="nav-link">Animation</a>
      </li>
    </ul>
  </div>
</div>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('build/plugins/prismjs/prism.js') }}"></script>
  <script src="{{ asset('build/plugins/clipboard/clipboard.min.js') }}"></script>
@endpush