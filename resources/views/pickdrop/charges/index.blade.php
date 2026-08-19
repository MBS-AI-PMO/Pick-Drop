@extends('layout.master')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Pick-Drop Charges</h4>
    <p class="text-secondary mb-0">Manage fare settings by distance (per KM).</p>
  </div>
</div>

<div class="row g-4 align-items-stretch">
  <div class="col-xl-6">
    <div class="card charge-settings-card h-100">
      <div class="card-body">
        <h6 class="card-title mb-3">Per KM Pricing</h6>

        <form action="{{ route('charges.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label class="form-label">Per KM Rate <span class="text-danger">*</span></label>
            <input
              type="number"
              step="0.01"
              min="0"
              id="perKmRateInput"
              name="per_km_rate"
              class="form-control @error('per_km_rate') is-invalid @enderror"
              value="{{ old('per_km_rate', $charge->per_km_rate) }}"
              placeholder="e.g. 55.00"
              required>
            @error('per_km_rate')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Currency <span class="text-danger">*</span></label>
            <input
              type="text"
              id="currencyInput"
              name="currency"
              maxlength="10"
              class="form-control @error('currency') is-invalid @enderror"
              value="{{ old('currency', $charge->currency) }}"
              placeholder="PKR"
              required>
            @error('currency')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-check form-switch mb-4">
            <input
              class="form-check-input"
              type="checkbox"
              role="switch"
              id="isActiveSwitch"
              name="is_active"
              value="1"
              {{ old('is_active', $charge->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="isActiveSwitch">Enable distance-based pricing</label>
          </div>

          <button type="submit" class="btn btn-primary">Save Charges</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card charge-preview-card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
          <div>
            <h6 class="card-title mb-1">Fare Preview</h6>
            <p class="text-secondary mb-0">Estimated fare by distance.</p>
          </div>
          <span class="charge-status-pill {{ old('is_active', $charge->is_active) ? 'is-active' : 'is-inactive' }}" id="chargeStatusText">
            {{ old('is_active', $charge->is_active) ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <div id="chargeFareChart" class="charge-fare-chart"></div>

        <div class="charge-preview-grid mt-3">
          <div class="charge-preview-item">
            <span>1 KM</span>
            <strong data-distance-preview="1">0</strong>
          </div>
          <div class="charge-preview-item">
            <span>5 KM</span>
            <strong data-distance-preview="5">0</strong>
          </div>
          <div class="charge-preview-item">
            <span>10 KM</span>
            <strong data-distance-preview="10">0</strong>
          </div>
          <div class="charge-preview-item">
            <span>20 KM</span>
            <strong data-distance-preview="20">0</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('build/plugins/apexcharts/apexcharts.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const chartEl = document.querySelector('#chargeFareChart');
  const rateInput = document.querySelector('#perKmRateInput');
  const currencyInput = document.querySelector('#currencyInput');
  const activeInput = document.querySelector('#isActiveSwitch');
  const statusText = document.querySelector('#chargeStatusText');
  const previewItems = document.querySelectorAll('[data-distance-preview]');
  const distances = [1, 2, 3, 5, 7, 10, 15, 20];

  if (!chartEl || typeof ApexCharts === 'undefined') return;

  const getThemeColors = function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    return {
      text: isDark ? '#c7d0de' : '#64748b',
      heading: isDark ? '#f4f7fb' : '#172033',
      grid: isDark ? 'rgba(255,255,255,0.08)' : '#edf1f7',
      fill: isDark ? 'rgba(69, 123, 157, 0.22)' : 'rgba(69, 123, 157, 0.16)'
    };
  };

  const getRate = function () {
    return Math.max(parseFloat(rateInput.value || 0), 0);
  };

  const getCurrency = function () {
    return (currencyInput.value || 'PKR').trim().toUpperCase();
  };

  const formatFare = function (amount) {
    return `${getCurrency()} ${Number(amount).toLocaleString(undefined, {
      minimumFractionDigits: amount % 1 === 0 ? 0 : 2,
      maximumFractionDigits: 2
    })}`;
  };

  const getFareData = function () {
    const rate = getRate();
    return distances.map(function (distance) {
      return Number((distance * rate).toFixed(2));
    });
  };

  const buildOptions = function () {
    const colors = getThemeColors();

    return {
      chart: {
        type: 'area',
        height: 315,
        toolbar: { show: false },
        zoom: { enabled: false },
        foreColor: colors.text,
        fontFamily: 'Inter, sans-serif'
      },
      series: [{
        name: 'Fare',
        data: getFareData()
      }],
      colors: ['#457b9d'],
      dataLabels: { enabled: false },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.35,
          opacityTo: 0.04,
          stops: [0, 90, 100]
        }
      },
      markers: {
        size: 4,
        strokeWidth: 2,
        strokeColors: '#ffffff',
        hover: { size: 6 }
      },
      grid: {
        borderColor: colors.grid,
        strokeDashArray: 4,
        padding: { left: 8, right: 16 }
      },
      xaxis: {
        categories: distances.map(function (distance) {
          return `${distance} KM`;
        }),
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        labels: {
          formatter: function (value) {
            return formatFare(value);
          }
        }
      },
      tooltip: {
        y: {
          formatter: function (value) {
            return formatFare(value);
          }
        }
      }
    };
  };

  const chart = new ApexCharts(chartEl, buildOptions());
  chart.render();

  const updatePreview = function () {
    const rate = getRate();
    const isActive = activeInput.checked;

    previewItems.forEach(function (item) {
      const distance = parseFloat(item.dataset.distancePreview || 0);
      item.textContent = formatFare(distance * rate);
    });

    statusText.textContent = isActive ? 'Active' : 'Inactive';
    statusText.classList.toggle('is-active', isActive);
    statusText.classList.toggle('is-inactive', !isActive);

    chart.updateOptions(buildOptions(), false, true);
  };

  rateInput.addEventListener('input', updatePreview);
  currencyInput.addEventListener('input', updatePreview);
  activeInput.addEventListener('change', updatePreview);

  new MutationObserver(updatePreview).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['data-bs-theme']
  });

  updatePreview();
});
</script>
@endpush
