@extends('layout.master')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Advanced UI</a></li>
    <li class="breadcrumb-item active" aria-current="page">Cropper</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">CropperJs</h4>
        <p class="text-secondary">Read the <a href="https://github.com/fengyuanchen/cropperjs" target="_blank"> Official CropperJs Documentation </a>for a full list of instructions and other options.</p>                  
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <div class="mb-3">
              <input class="form-control" type="file" id="cropperImageUpload">
            </div>
            <cropper-canvas background style="height: 300px;">
              <cropper-image id="croppingImage" src="{{ url('build/images/others/placeholder.jpg') }}" alt="Picture"
                rotatable scalable skewable translatable></cropper-image>
              <cropper-shade hidden></cropper-shade>
              <cropper-handle action="select" plain></cropper-handle>
              <cropper-selection id="cropperSelection" initial-coverage="0.5" movable resizable>
                <cropper-grid role="grid" covered></cropper-grid>
                <cropper-crosshair centered></cropper-crosshair>
                <cropper-handle action="move" theme-color="rgba(255, 255, 255, 0.35)"></cropper-handle>
                <cropper-handle action="n-resize"></cropper-handle>
                <cropper-handle action="e-resize"></cropper-handle>
                <cropper-handle action="s-resize"></cropper-handle>
                <cropper-handle action="w-resize"></cropper-handle>
                <cropper-handle action="ne-resize"></cropper-handle>
                <cropper-handle action="nw-resize"></cropper-handle>
                <cropper-handle action="se-resize"></cropper-handle>
                <cropper-handle action="sw-resize"></cropper-handle>
              </cropper-selection>
            </cropper-canvas>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <div class="d-flex align-items-center flex-wrapp me-2 mt-3">
                <label class="w-50 me-3 mb-0 mb-2 mb-md-0 text-nowrap">Width (px) :</label>
                <input type="number" value="300" class="form-control img-w me-2 mb-2 mb-md-0 w-75"
                  placeholder="Image width">
                <button class="btn btn-primary crop mb-2 mb-md-0">Crop</button>
              </div>
              <div class="mb-4 mb-md-0 mt-3">
                <a href="javascript:;" class="btn btn-outline-primary download">Download</a>
              </div>
            </div>
          </div>
          <div class="col-md-4 ms-auto">
            <h6 class="text-secondary mb-3">Cropped Image: </h6>
            <img class="w-100 cropped-img mt-2" src="#" alt="">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('build/plugins/cropperjs/cropper.min.js') }}"></script>
@endpush

@push('custom-scripts')
  @vite(['resources/js/pages/cropper.js'])
@endpush