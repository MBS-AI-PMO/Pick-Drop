@extends('layout.master')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Reports</h4>
    <p class="text-secondary mb-0">View and analyze system data</p>
  </div>
</div>

{{-- Tabs + Filter/Export --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <ul class="nav nav-pills gap-1" id="reportTabs">
    <li class="nav-item">
      <a class="nav-link active px-4" href="#" data-period="daily">Daily</a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-4" href="#" data-period="weekly">Weekly</a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-4" href="#" data-period="monthly">Monthly</a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-4" href="#" data-period="custom">Custom</a>
    </li>
  </ul>
  <div class="d-flex gap-2">
    <button class="btn btn-success d-flex align-items-center gap-1">
      <i data-lucide="filter" style="width:15px;height:15px;"></i> Filter
    </button>
    <button class="btn btn-success d-flex align-items-center gap-1">
      <i data-lucide="download" style="width:15px;height:15px;"></i> Export
    </button>
  </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">

  {{-- Trip Statistics --}}
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0">Trip Statistics</h6>
          <i data-lucide="bar-chart-2" class="text-secondary" style="width:18px;height:18px;"></i>
        </div>
        {{-- Chart Placeholder --}}
        <div class="d-flex align-items-end justify-content-center gap-2 mb-4" style="height:140px;">
          @php
            $bars = [40, 65, 50, 80, 55, 70, 45, 90, 60, 75, 85, 58];
            $days = ['M','T','W','T','F','S','S','M','T','W','T','F'];
          @endphp
          @foreach($bars as $i => $h)
            <div class="d-flex flex-column align-items-center gap-1">
              <div class="rounded-top" style="width:18px;height:{{ $h }}px;background:rgba(59,91,219,{{ $loop->index == 7 ? 1 : 0.45 }});"></div>
              <small class="text-secondary" style="font-size:9px;">{{ $days[$i] }}</small>
            </div>
          @endforeach
        </div>
        <p class="text-center text-secondary fs-12px mb-3">Bar Chart: Trips per Day</p>
        <hr class="my-2">
        <div class="row text-center mt-3">
          <div class="col-4">
            <p class="text-secondary fs-12px mb-1">Total Trips</p>
            <h5 class="fw-bold mb-0">458</h5>
          </div>
          <div class="col-4">
            <p class="text-secondary fs-12px mb-1">On-Time</p>
            <h5 class="fw-bold text-success mb-0">92%</h5>
          </div>
          <div class="col-4">
            <p class="text-secondary fs-12px mb-1">Delayed</p>
            <h5 class="fw-bold text-warning mb-0">8%</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Attendance Trends --}}
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0">Attendance Trends</h6>
          <i data-lucide="trending-up" class="text-secondary" style="width:18px;height:18px;"></i>
        </div>
        {{-- Line Chart Placeholder --}}
        <div style="height:140px;position:relative;" class="mb-4">
          <svg width="100%" height="100%" viewBox="0 0 400 140" preserveAspectRatio="none">
            {{-- Grid lines --}}
            <line x1="0" y1="35" x2="400" y2="35" stroke="#e2e8f0" stroke-width="1"/>
            <line x1="0" y1="70" x2="400" y2="70" stroke="#e2e8f0" stroke-width="1"/>
            <line x1="0" y1="105" x2="400" y2="105" stroke="#e2e8f0" stroke-width="1"/>
            {{-- Line --}}
            <polyline
              points="0,100 50,85 100,70 150,80 200,55 250,60 300,40 350,50 400,35"
              fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            {{-- Area fill --}}
            <polygon
              points="0,100 50,85 100,70 150,80 200,55 250,60 300,40 350,50 400,35 400,140 0,140"
              fill="rgba(34,197,94,0.08)"/>
            {{-- Dots --}}
            <circle cx="0"   cy="100" r="4" fill="#22c55e"/>
            <circle cx="100" cy="70"  r="4" fill="#22c55e"/>
            <circle cx="200" cy="55"  r="4" fill="#22c55e"/>
            <circle cx="300" cy="40"  r="4" fill="#22c55e"/>
            <circle cx="400" cy="35"  r="5" fill="#22c55e" stroke="white" stroke-width="2"/>
          </svg>
        </div>
        <p class="text-center text-secondary fs-12px mb-3">Line Chart: Student Attendance</p>
        <hr class="my-2">
        <div class="row text-center mt-3">
          <div class="col-4">
            <p class="text-secondary fs-12px mb-1">Average</p>
            <h5 class="fw-bold mb-0">95%</h5>
          </div>
          <div class="col-4">
            <p class="text-secondary fs-12px mb-1">Highest</p>
            <h5 class="fw-bold text-success mb-0">98%</h5>
          </div>
          <div class="col-4">
            <p class="text-secondary fs-12px mb-1">Lowest</p>
            <h5 class="fw-bold text-warning mb-0">91%</h5>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Route Efficiency Analysis --}}
<div class="card">
  <div class="card-body">
    <h6 class="fw-bold mb-3">Route Efficiency Analysis</h6>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="py-3">Route</th>
            <th class="py-3">Avg. Time</th>
            <th class="py-3">Distance</th>
            <th class="py-3">Fuel Usage</th>
            <th class="py-3" style="min-width:160px;">Efficiency</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="py-3 fw-semibold">Route #R-123</td>
            <td class="py-3 text-secondary">45 mins</td>
            <td class="py-3 text-secondary">8.5 miles</td>
            <td class="py-3 text-secondary">2.1 gal</td>
            <td class="py-3">
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height:6px;">
                  <div class="progress-bar bg-success" style="width:85%;"></div>
                </div>
                <span class="fs-13px fw-semibold">85%</span>
              </div>
            </td>
          </tr>
          <tr>
            <td class="py-3 fw-semibold">Route #R-124</td>
            <td class="py-3 text-secondary">38 mins</td>
            <td class="py-3 text-secondary">7.2 miles</td>
            <td class="py-3 text-secondary">1.8 gal</td>
            <td class="py-3">
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height:6px;">
                  <div class="progress-bar bg-success" style="width:92%;"></div>
                </div>
                <span class="fs-13px fw-semibold">92%</span>
              </div>
            </td>
          </tr>
          <tr>
            <td class="py-3 fw-semibold">Route #R-125</td>
            <td class="py-3 text-secondary">52 mins</td>
            <td class="py-3 text-secondary">10.1 miles</td>
            <td class="py-3 text-secondary">2.5 gal</td>
            <td class="py-3">
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height:6px;">
                  <div class="progress-bar bg-warning" style="width:78%;"></div>
                </div>
                <span class="fs-13px fw-semibold">78%</span>
              </div>
            </td>
          </tr>
          <tr>
            <td class="py-3 fw-semibold">Route #R-126</td>
            <td class="py-3 text-secondary">42 mins</td>
            <td class="py-3 text-secondary">9.0 miles</td>
            <td class="py-3 text-secondary">2.0 gal</td>
            <td class="py-3">
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height:6px;">
                  <div class="progress-bar bg-success" style="width:88%;"></div>
                </div>
                <span class="fs-13px fw-semibold">88%</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection

@push('custom-scripts')
<script>
  document.querySelectorAll('#reportTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelectorAll('#reportTabs .nav-link').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    });
  });
</script>
@endpush
