@extends('layout.master')

@section('content')
<div class="row">
  <div class="col-md-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Peity Charts</h6>
        <p>Peity (sounds like deity) is a jQuery plugin that converts an element's content into a &lt;svg&gt;. Read the <a href="https://benpickles.github.io/peity/" target="_blank"> Official Peity Documentation</a> for a full list of instructions and other options.</p>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Line charts</h6>
        <span data-peity='{"stroke": ["rgb(255,51,102)"], "fill": ["rgba(255,51,102,.2)"],"height": 50, "width": 80 }' class="peity-line">5,3,9,6,5,9,7,3,5,2</span>
        <span data-peity='{"stroke": ["rgb(102,209,209)"], "fill": ["rgba(102,209,209,.3)"],"height": 50, "width": 80 }' class="peity-line">5,3,2,-1,-3,-2,2,3,5,2</span>
        <span data-peity='{"stroke": ["rgb(251,188,6)"], "fill": ["rgba(251,188,6,.3)"],"height": 50, "width": 80 }' class="peity-line">0,-3,-6,-4,-5,-4,-7,-3,-5,-2</span>
      </div>
    </div>
  </div>
  <div class="col-xl-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Bar charts</h6>
        <span data-peity='{"fill": ["rgb(255,51,102)"],"height": 50, "width": 80 }' class="peity-bar">5,3,9,6,5,9,7,3,5,2</span>
        <span data-peity='{"fill": ["rgb(102,209,209)"],"height": 50, "width": 80 }' class="peity-bar">5,3,2,-1,-3,-2,2,3,5,2</span>
        <span data-peity='{"fill": ["rgb(251,188,6)"],"height": 50, "width": 80 }' class="peity-bar">0,-3,-6,-4,-5,-4,-7,-3,-5,-2</span>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Pie charts</h6>
        <span data-peity='{"fill": ["rgb(255,51,102)", "rgba(255,51,102,.2)"],"height": 50, "width": 60 }' class="peity-pie">1/5</span>
        <span data-peity='{"fill": ["rgb(102,209,209)", "rgba(102,209,209,.3)"],"height": 50, "width": 60 }' class="peity-pie">226/360</span>
        <span data-peity='{"fill": ["rgb(251,188,6)", "rgba(251,188,6,.3)"],"height": 50, "width": 60 }' class="peity-pie">0.52/1.561</span>
        <span data-peity='{"fill": ["rgba(101,113,255,.85)", "rgba(101,113,255,.3)"],"height": 50, "width": 60 }' class="peity-pie">1,2,3,2,2</span>
      </div>
    </div>
  </div>
  <div class="col-xl-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Donut chart</h6>
        <span data-peity='{"fill": ["rgb(255,51,102)", "rgba(255,51,102,.2)"],"height": 50, "width": 60 }' class="peity-donut">1/5</span>
        <span data-peity='{"fill": ["rgb(102,209,209)", "rgba(102,209,209,.3)"],"height": 50, "width": 60 }' class="peity-donut">226/360</span>
        <span data-peity='{"fill": ["rgb(251,188,6)", "rgba(251,188,6,.3)"],"height": 50, "width": 60 }' class="peity-donut">0.52/1.561</span>
        <span data-peity='{"fill": ["rgba(101,113,255,.85)", "rgba(101,113,255,.3)"],"height": 50, "width": 60 }' class="peity-donut">1,2,3,2,2</span>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-6 stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Options</h6>
        <p class="peity-custom">
          <span data-peity='{ "fill": ["rgb(255,51,102)", "rgba(255,51,102,.2)"], "innerRadius": 10, "radius": 40 }'>1/7</span>
          <span data-peity='{ "fill": ["rgb(102,209,209)", "rgba(102,209,209,.3)"], "innerRadius": 14, "radius": 36 }'>2/7</span>
          <span data-peity='{ "fill": ["rgb(251,188,6)", "rgba(251,188,6,.3)"], "innerRadius": 16, "radius": 32 }'>3/7</span>
          <span data-peity='{ "fill": ["rgba(101,113,255,.85)", "rgba(101,113,255,.3)"], "innerRadius": 18, "radius": 28 }'>4/7</span>
          <span data-peity='{ "fill": ["rgba(16, 183, 89, .5)", "rgba(16, 183, 89, .2)"],   "innerRadius": 20, "radius": 24 }'>5/7</span>
          <span data-peity='{ "fill": ["rgb(255,51,102)", "rgba(255,51,102,.2)"], "innerRadius": 18, "radius": 20 }'>6/7</span>
          <span data-peity='{ "fill": ["rgba(101,113,255,.85)", "rgba(101,113,255,.3)"], "innerRadius": 15, "radius": 16 }'>7/7</span>
        </p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('build/plugins/peity/jquery.peity.min.js') }}"></script>
@endpush

@push('custom-scripts')
  @vite(['resources/js/pages/peity.js'])
@endpush