@php
    $name = $name ?? '';
    $label = $label ?? '';
    $previewUrl = $previewUrl ?? null;
@endphp

<div class="col-md-4">
    <label class="form-label fw-semibold">{{ $label }}</label>
    @if($previewUrl)
        <div class="mb-2">
            <a href="{{ $previewUrl }}" target="_blank">
                <img src="{{ $previewUrl }}" alt="{{ $label }}"
                     class="img-fluid rounded border"
                     style="max-height:120px;object-fit:cover;width:100%;">
            </a>
        </div>
    @endif
    <input type="file" name="{{ $name }}" class="form-control" accept="image/*">
    <small class="text-muted">Leave empty to keep current file.</small>
    @error($name)
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
