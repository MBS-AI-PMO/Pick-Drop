@php
    $name = $name ?? '';
    $label = $label ?? '';
    $previewUrl = $previewUrl ?? null;
@endphp

@once
@push('style')
<style>
  .kyc-doc-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
  }
  .kyc-doc-grid--pair {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .kyc-doc {
    width: 100%;
    border: 1px solid #e6ebf2;
    border-radius: 12px;
    padding: 14px;
    background: #fff;
  }
  .kyc-doc__label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
  }
  .kyc-doc__frame {
    display: flex;
    align-items: center;
    justify-content: center;
    aspect-ratio: 16 / 10;
    height: auto;
    margin-bottom: 10px;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f5f8;
    border: 1px solid #e8edf3;
  }
  @media (max-width: 767.98px) {
    .kyc-doc-grid,
    .kyc-doc-grid--pair {
      grid-template-columns: 1fr;
    }
  }
  .kyc-doc__frame img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
  }
  .kyc-doc__note {
    margin: 6px 0 0;
    font-size: 12px;
    color: #6b7280;
  }
</style>
@endpush
@endonce

<div class="kyc-doc">
    <label class="kyc-doc__label">{{ $label }}</label>
    @if($previewUrl)
        <a href="{{ $previewUrl }}" target="_blank" class="kyc-doc__frame">
            <img src="{{ $previewUrl }}" alt="{{ $label }}">
        </a>
    @endif
    <input type="file" name="{{ $name }}" class="form-control form-control-sm" accept="image/*">
    <p class="kyc-doc__note">Leave empty to keep the current file.</p>
    @error($name)
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
