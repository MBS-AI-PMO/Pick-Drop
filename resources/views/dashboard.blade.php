@extends('layout.master')

@section('page-content-class', 'container-fluid dsh-page')

@section('content')
@php
  $rings = $stats['rings'] ?? ['vehicles' => 0, 'users' => 0, 'pending' => 0, 'alerts' => 0];
  $ctx = $kpiContext ?? [];

  $hour = (int) now()->format('G');
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
  $firstName = auth()->user() ? \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ') : 'Admin';

  $pendingCount = (int) ($pendingRequestsCount ?? 0);
  $tripsCount = (int) ($activeTripsCount ?? 0);
  $unreadCount = (int) ($recentAlertsCount ?? 0);

  // One colour per request status, shared by the donut, its legend and the trip pills.
  $statusMeta = [
    'pending' => ['Pending', '#f59e0b'],
    'accepted' => ['Accepted', '#3b82f6'],
    'picked_up' => ['Picked up', '#8b5cf6'],
    'dropped' => ['Dropped', '#06b6d4'],
    'completed' => ['Completed', '#10b981'],
    'cancelled' => ['Cancelled', '#f43f5e'],
  ];
  $tripTone = ['Accepted' => 'blue', 'Picked Up' => 'violet', 'Dropped' => 'cyan'];
  $scheduleTone = ['In Progress' => ['indigo', 'is-live'], 'Completed' => ['emerald', 'is-done'], 'Upcoming' => ['amber', 'is-next']];
  $alertTone = ['success' => 'emerald', 'warning' => 'amber', 'danger' => 'rose', 'info' => 'blue'];

  // Stable avatar colour per name.
  $avatarTones = ['indigo', 'emerald', 'amber', 'rose', 'violet', 'cyan', 'blue'];
  $toneFor = fn ($name) => $avatarTones[abs(crc32((string) $name)) % count($avatarTones)];
  $initial = fn ($name) => strtoupper(mb_substr(trim((string) $name) ?: '?', 0, 1));

  $statusBreakdown = $statusBreakdown ?? [];
  $statusTotal = array_sum($statusBreakdown);

  // Donut segments (pathLength = 100, so lengths are percentages). Label position is worked out
  // in the circle's own pre-rotation space (angle 0 = 3 o'clock, clockwise); the whole <svg> is
  // then rotated -90deg by CSS so segment and label land together at the 12 o'clock start.
  $donut = [];
  $offset = 0;
  foreach ($statusMeta as $key => [$label, $color]) {
    $n = (int) ($statusBreakdown[$key] ?? 0);
    if ($statusTotal <= 0 || $n <= 0) {
      continue;
    }
    $len = $n / $statusTotal * 100;
    $share = round($n / $statusTotal * 100);
    $midAngle = ($offset + $len / 2) / 100 * 2 * M_PI;
    $donut[] = [
      'key' => $key, 'label' => $label, 'color' => $color, 'n' => $n, 'len' => $len, 'offset' => $offset, 'share' => $share,
      'lx' => round(60 + 46 * cos($midAngle), 2), 'ly' => round(60 + 46 * sin($midAngle), 2),
    ];
    $offset += $len;
  }
  $donutGap = count($donut) > 1 ? 1.2 : 0;

  $week = collect($weeklyRequests ?? [])->values();
  $weekSum = (int) $week->sum('total');
  $weekToday = (int) ($week->last()['total'] ?? 0);
  $prevWeek = (int) ($prevWeekTotal ?? 0);
  $delta = $prevWeek > 0 ? (int) round((($weekSum - $prevWeek) / $prevWeek) * 100) : null;

  // Bar chart geometry
  $rawMax = max((int) ($week->max('total') ?? 0), 1);
  $step = max(1, (int) ceil($rawMax / 4));
  $axisMax = $step * 4;
  $cw = 720; $ch = 222; $padL = 32; $padR = 6; $padT = 26; $padB = 26;
  $plotW = $cw - $padL - $padR; $plotH = $ch - $padT - $padB;
  $baseY = $padT + $plotH;
  $bandW = $plotW / max($week->count(), 1);
  $barW = min(40, $bandW * 0.48);
  $lastIndex = $week->count() - 1;
  $weekAvg = $week->count() > 0 ? $weekSum / $week->count() : 0;
  $avgY = $baseY - ($weekAvg / $axisMax) * $plotH;
  $peakTotal = (int) $week->max('total');
  $peakIndex = $peakTotal > 0 ? $week->search(fn ($d) => $d['total'] === $peakTotal) : null;

  // Trend line: one point per day at the true (unclamped) height, smoothed into a Catmull-Rom spline.
  $trendPts = $week->map(function ($day, $i) use ($padL, $bandW, $baseY, $axisMax, $plotH) {
    return [
      'x' => $padL + $bandW * $i + $bandW / 2,
      'y' => $baseY - ($axisMax > 0 ? ($day['total'] / $axisMax) * $plotH : 0),
    ];
  })->values()->all();
  $trendPath = '';
  $n = count($trendPts);
  for ($i = 0; $i < $n - 1; $i++) {
    $p0 = $trendPts[max(0, $i - 1)];
    $p1 = $trendPts[$i];
    $p2 = $trendPts[$i + 1];
    $p3 = $trendPts[min($n - 1, $i + 2)];
    $c1x = $p1['x'] + ($p2['x'] - $p0['x']) / 6;
    $c1y = $p1['y'] + ($p2['y'] - $p0['y']) / 6;
    $c2x = $p2['x'] - ($p3['x'] - $p1['x']) / 6;
    $c2y = $p2['y'] - ($p3['y'] - $p1['y']) / 6;
    $trendPath .= ($i === 0 ? 'M ' . round($p1['x'], 1) . ',' . round($p1['y'], 1) . ' ' : '')
      . 'C ' . round($c1x, 1) . ',' . round($c1y, 1) . ' ' . round($c2x, 1) . ',' . round($c2y, 1) . ' ' . round($p2['x'], 1) . ',' . round($p2['y'], 1) . ' ';
  }
  $trendArea = $n > 0 ? $trendPath . "L {$trendPts[$n - 1]['x']},{$baseY} L {$trendPts[0]['x']},{$baseY} Z" : '';

  $kpis = [
    ['label' => 'Active vehicles', 'tone' => 'indigo', 'value' => (int) ($stats['vehicles'] ?? 0), 'note' => 'of ' . number_format($ctx['vehicles_total'] ?? 0) . ' vehicles in fleet', 'meter' => 'Fleet active', 'pct' => (int) $rings['vehicles'], 'url' => route('vehicles.index')],
    ['label' => 'Total users', 'tone' => 'emerald', 'value' => (int) ($stats['users'] ?? 0), 'note' => number_format($ctx['users_active'] ?? 0) . ' accounts active', 'meter' => 'Active rate', 'pct' => (int) $rings['users'], 'url' => route('users.index')],
    ['label' => 'Pending requests', 'tone' => 'amber', 'value' => (int) ($stats['pending_requests'] ?? 0), 'note' => 'of ' . number_format($ctx['requests_total'] ?? 0) . ' requests overall', 'meter' => 'Share of requests', 'pct' => (int) $rings['pending'], 'url' => route('pickup-requests.index', ['status' => 'pending'])],
    ['label' => 'Alerts today', 'tone' => 'rose', 'value' => (int) ($stats['alerts_today'] ?? 0), 'note' => number_format($ctx['alerts_week'] ?? 0) . ' in the last 7 days', 'meter' => 'Share of this week', 'pct' => (int) $rings['alerts'], 'url' => route('notifications.index')],
  ];

  $weekStats = [
    ['label' => 'This week', 'tone' => 'indigo', 'value' => $weekSum, 'delta' => true],
    ['label' => 'Today', 'tone' => 'amber', 'value' => $weekToday, 'note' => 'new requests'],
    ['label' => 'Daily average', 'tone' => 'violet', 'value' => number_format($weekSum / 7, 1, '.', ''), 'decimals' => 1, 'note' => 'per day'],
    ['label' => 'Active trips', 'tone' => 'emerald', 'value' => $tripsCount, 'note' => 'on the road'],
  ];

  $health = [
    ['label' => 'On-time performance', 'hint' => 'Completed vs cancelled trips', 'tone' => 'emerald', 'pct' => (float) ($metrics['on_time_performance'] ?? 0), 'text' => ($metrics['on_time_performance'] ?? 0) . '%', 'count' => (int) ($metrics['on_time_performance'] ?? 0), 'suffix' => '%'],
    ['label' => 'Fleet utilization', 'hint' => 'Vehicles assigned to drivers', 'tone' => 'indigo', 'pct' => (float) ($metrics['fleet_utilization'] ?? 0), 'text' => ($metrics['fleet_utilization'] ?? 0) . '%', 'count' => (int) ($metrics['fleet_utilization'] ?? 0), 'suffix' => '%'],
    ['label' => 'Parent satisfaction', 'hint' => 'Resolved vs open issues', 'tone' => 'amber', 'pct' => ((float) ($metrics['parent_satisfaction'] ?? 0)) / 5 * 100, 'text' => number_format($metrics['parent_satisfaction'] ?? 0, 1) . ' / 5', 'count' => number_format($metrics['parent_satisfaction'] ?? 0, 1, '.', ''), 'suffix' => ' / 5', 'decimals' => 1],
  ];
@endphp

<div class="dsh">

  {{-- Page header: plain text on the page, no banner card --}}
  <header class="dsh-head">
    <div>
      <span class="dsh-head__date">{{ now()->format('l, d M Y') }}</span>
      <h1>{{ $greeting }}, {{ $firstName }}</h1>
      <p>Here's what's happening across your fleet today.</p>
    </div>
    <div class="dsh-head__actions">
      <a href="{{ route('pickup-requests.index', ['status' => 'pending']) }}" class="dsh-btn">
        Pending queue <span class="dsh-btn__count">{{ number_format($pendingCount) }}</span>
      </a>
      <a href="{{ route('reports.index') }}" class="dsh-btn dsh-btn--primary">View reports</a>
    </div>
  </header>

  {{-- KPI cards --}}
  <div class="dsh-kpis">
    @foreach($kpis as $i => $k)
      @php $kp = max(0, min(100, $k['pct'])); @endphp
      <a href="{{ $k['url'] }}" class="dsh-card dsh-kpi dsh-tone-{{ $k['tone'] }}" style="--d:{{ 60 + $i * 60 }}ms;">
        <div class="dsh-kpi__label">{{ $k['label'] }}</div>
        <div class="dsh-kpi__value" data-count="{{ $k['value'] }}">{{ number_format($k['value']) }}</div>
        <div class="dsh-kpi__note">{{ $k['note'] }}</div>
        <div class="dsh-kpi__meter">
          <div class="dsh-kpi__meter-head"><span>{{ $k['meter'] }}</span><b>{{ $k['pct'] }}%</b></div>
          <div class="dsh-track"><span style="--w:{{ $kp }}%;"></span></div>
        </div>
      </a>
    @endforeach
  </div>

  {{-- Requests chart + status mix --}}
  <div class="dsh-grid dsh-grid--8-4">
    <section class="dsh-card" style="--d:300ms;">
      <div class="dsh-card__head">
        <div class="dsh-card__title">
          <div>
            <h2>Pickup requests</h2>
            <p>Volume &amp; trend over the last 7 days</p>
          </div>
        </div>
        <div class="dsh-legend">
          <span><i class="is-prev"></i>Previous days</span>
          <span><i class="is-today"></i>Today</span>
          <span><i class="is-trend"></i>Trend</span>
        </div>
      </div>

      <div class="dsh-insights">
        <span>Peak <b>{{ $peakIndex !== null ? ($peakIndex === $lastIndex ? 'Today' : $week[$peakIndex]['label']) : '—' }}</b> <em>{{ $peakTotal }} {{ $peakTotal === 1 ? 'req' : 'reqs' }}</em></span>
        <span>Average <b>{{ number_format($weekAvg, 1) }}</b> <em>per day</em></span>
        <span>Week trend <b class="{{ $delta !== null && $delta < 0 ? 'is-down' : '' }}">{{ $delta !== null ? ($delta >= 0 ? '+' : '') . $delta . '%' : 'n/a' }}</b> <em>vs last week</em></span>
      </div>

      <div class="dsh-chart">
        <svg class="dsh-bars" viewBox="0 0 {{ $cw }} {{ $ch }}" role="img" aria-label="Pickup requests per day for the last 7 days, with a 7-day trend line and daily average">
          <defs>
            <clipPath id="dshPlot"><rect x="0" y="0" width="{{ $cw }}" height="{{ $baseY }}"/></clipPath>
          </defs>

          @for($g = 0; $g <= 4; $g++)
            @php $gy = $padT + ($plotH / 4) * $g; @endphp
            <line class="dsh-bars__grid {{ $g === 4 ? 'is-base' : '' }}" x1="{{ $padL }}" x2="{{ $cw - $padR }}" y1="{{ $gy }}" y2="{{ $gy }}"/>
            <text class="dsh-bars__axis" x="{{ $padL - 10 }}" y="{{ $gy + 4 }}" text-anchor="end">{{ $axisMax - $step * $g }}</text>
          @endfor

          {{-- Soft area under the trend line, then the average reference line, both behind the bars --}}
          @if($trendArea !== '')
            <path class="dsh-bars__area" d="{{ $trendArea }}" clip-path="url(#dshPlot)"/>
          @endif
          <line class="dsh-bars__avg" x1="{{ $padL }}" x2="{{ $cw - $padR }}" y1="{{ round($avgY, 1) }}" y2="{{ round($avgY, 1) }}"/>
          <text class="dsh-bars__avg-label" x="{{ $cw - $padR }}" y="{{ round($avgY, 1) - 7 }}" text-anchor="end">Avg {{ number_format($weekAvg, 1) }}</text>

          @foreach($week as $i => $day)
            @php
              $isToday = $i === $lastIndex;
              $isPeak = $peakIndex === $i && $peakTotal > 0;
              $cx = $padL + $bandW * $i + $bandW / 2;
              $h = $day['total'] > 0 ? max(4, ($day['total'] / $axisMax) * $plotH) : 0;
              $top = $baseY - $h;
              $diffAvg = $day['total'] - $weekAvg;
              $diffText = ($diffAvg >= 0 ? '+' : '') . number_format($diffAvg, 1);
              $tipW = 160; $tipH = 112;
              $tipX = min(max($cx - $tipW / 2, $padL - 4), $cw - $padR - $tipW + 4);
              $tipY = min($top, $trendPts[$i]['y']) - $tipH - 16;
            @endphp
            <g class="dsh-bars__col {{ $isToday ? 'is-today' : '' }}" style="--i:{{ $i }};">
              <rect class="dsh-bars__hit" x="{{ $padL + $bandW * $i + 4 }}" y="{{ $padT - 22 }}" width="{{ $bandW - 8 }}" height="{{ $plotH + 22 }}" rx="6"/>
              @if($h > 0)
                <rect class="dsh-bars__bar" x="{{ $cx - $barW / 2 }}" y="{{ $top }}" width="{{ $barW }}" height="{{ $h + 10 }}" rx="3" clip-path="url(#dshPlot)"/>
              @else
                <rect class="dsh-bars__zero" x="{{ $cx - $barW / 2 }}" y="{{ $baseY - 3 }}" width="{{ $barW }}" height="3" rx="1.5"/>
              @endif
              @if($isPeak)
                <text class="dsh-bars__peak" x="{{ $cx }}" y="{{ ($h > 0 ? $top : $baseY - 3) - 20 }}" text-anchor="middle">Peak</text>
              @endif
              <text class="dsh-bars__val" x="{{ $cx }}" y="{{ ($h > 0 ? $top : $baseY - 3) - 8 }}" text-anchor="middle">{{ $day['total'] }}</text>
              <text class="dsh-bars__axis {{ $isToday ? 'is-today' : '' }}" x="{{ $cx }}" y="{{ $ch - 8 }}" text-anchor="middle">{{ $isToday ? 'Today' : $day['label'] }}</text>

              {{-- Rich hover tooltip: date, total, completed/cancelled split, and the gap to the weekly average.
                   Position is set on this outer <g> via the transform ATTRIBUTE; the inner <g> carries the
                   CSS transform/opacity for the hover animation, since a CSS transform on an SVG element
                   replaces its transform attribute rather than composing with it. --}}
              <g transform="translate({{ round($tipX, 1) }},{{ round($tipY, 1) }})">
                <g class="dsh-bars__tip">
                  <rect class="dsh-bars__tip-bg" width="{{ $tipW }}" height="{{ $tipH }}" rx="8"/>
                  <text class="dsh-bars__tip-title" x="14" y="23">{{ $day['label'] }} · {{ $day['date'] }}{{ $day['weekend'] ? ' · Weekend' : '' }}</text>
                  <line class="dsh-bars__tip-sep" x1="14" x2="{{ $tipW - 14 }}" y1="31" y2="31"/>
                  <text class="dsh-bars__tip-row" x="14" y="50">Requests<tspan x="{{ $tipW - 14 }}" text-anchor="end" class="is-strong">{{ $day['total'] }}</tspan></text>
                  <text class="dsh-bars__tip-row" x="14" y="70">Completed<tspan x="{{ $tipW - 14 }}" text-anchor="end" class="is-emerald">{{ $day['completed'] }}</tspan></text>
                  <text class="dsh-bars__tip-row" x="14" y="90">Cancelled<tspan x="{{ $tipW - 14 }}" text-anchor="end" class="is-rose">{{ $day['cancelled'] }}</tspan></text>
                  <text class="dsh-bars__tip-row" x="14" y="{{ $tipH - 8 }}">Vs. average<tspan x="{{ $tipW - 14 }}" text-anchor="end" class="{{ $diffAvg >= 0 ? 'is-emerald' : 'is-rose' }}">{{ $diffText }}</tspan></text>
                </g>
              </g>
            </g>
          @endforeach

          {{-- Trend line drawn last so it reads clearly over the bars --}}
          @if($trendPath !== '')
            <path class="dsh-bars__trend" d="{{ $trendPath }}"/>
          @endif
          @foreach($trendPts as $i => $pt)
            <circle class="dsh-bars__dot {{ $i === $lastIndex ? 'is-today' : '' }}" cx="{{ round($pt['x'], 1) }}" cy="{{ round($pt['y'], 1) }}" r="{{ $i === $lastIndex ? 4.5 : 3 }}"/>
          @endforeach
        </svg>
      </div>

      <div class="dsh-stats">
        @foreach($weekStats as $s)
          <div class="dsh-stat">
            <span class="dsh-stat__label">{{ $s['label'] }}</span>
            <span class="dsh-stat__value" data-count="{{ $s['value'] }}" @isset($s['decimals']) data-decimals="{{ $s['decimals'] }}" @endisset>{{ $s['value'] }}</span>
            @if(!empty($s['delta']))
              @if($delta !== null)
                <span class="dsh-delta {{ $delta >= 0 ? 'is-up' : 'is-down' }}">{{ $delta >= 0 ? '+' : '' }}{{ $delta }}% vs last week</span>
              @else
                <span class="dsh-stat__note">No data last week</span>
              @endif
            @else
              <span class="dsh-stat__note">{{ $s['note'] }}</span>
            @endif
          </div>
        @endforeach
      </div>
    </section>

    <section class="dsh-card" style="--d:360ms;">
      <div class="dsh-card__head">
        <div class="dsh-card__title">
          <div>
            <h2>Status mix</h2>
            <p>All requests by current status</p>
          </div>
        </div>
      </div>
      <div class="dsh-mix" id="dshStatusMix">
        <div class="dsh-donut">
          <svg viewBox="0 0 120 120" role="img" aria-label="Requests by status">
            <circle class="dsh-donut__track" cx="60" cy="60" r="46" pathLength="100"/>
            @foreach($donut as $i => $seg)
              @php $segLen = max($seg['len'] - $donutGap, 0.01); @endphp
              <circle class="dsh-donut__seg" data-key="{{ $seg['key'] }}" cx="60" cy="60" r="46" pathLength="100"
                stroke="{{ $seg['color'] }}"
                stroke-dasharray="{{ round($segLen, 3) }} {{ round(100 - $segLen, 3) }}"
                stroke-dashoffset="{{ round(-$seg['offset'], 3) }}"
                data-label="{{ $seg['label'] }}" data-n="{{ $seg['n'] }}" data-share="{{ $seg['share'] }}">
                <title>{{ $seg['label'] }}: {{ $seg['n'] }} ({{ $seg['share'] }}%)</title>
              </circle>
            @endforeach
            @foreach($donut as $seg)
              @if($seg['share'] >= 8)
                <g transform="rotate(90 {{ $seg['lx'] }} {{ $seg['ly'] }})">
                  <text class="dsh-donut__label" x="{{ $seg['lx'] }}" y="{{ $seg['ly'] }}" text-anchor="middle">{{ $seg['share'] }}%</text>
                </g>
              @endif
            @endforeach
          </svg>
          <div class="dsh-donut__center">
            <b data-count="{{ $statusTotal }}" data-default-count="{{ $statusTotal }}">{{ number_format($statusTotal) }}</b>
            <span data-default-label="total requests">total requests</span>
          </div>
        </div>
        <ul class="dsh-legend-list">
          @foreach($statusMeta as $key => [$label, $color])
            @php
              $count = (int) ($statusBreakdown[$key] ?? 0);
              $share = $statusTotal > 0 ? round(($count / $statusTotal) * 100) : 0;
            @endphp
            <li class="{{ $count === 0 ? 'is-zero' : '' }}" data-key="{{ $key }}">
              <span class="dsh-legend-list__swatch" style="background:{{ $color }};"></span>
              <span class="dsh-legend-list__label">{{ $label }}</span>
              <span class="dsh-legend-list__count">{{ number_format($count) }}</span>
              <span class="dsh-legend-list__share">{{ $share }}%</span>
              <span class="dsh-legend-list__bar"><span style="--w:{{ $share }}%;background:{{ $color }};"></span></span>
            </li>
          @endforeach
        </ul>
      </div>
    </section>
  </div>

  {{-- Trips + schedule --}}
  <div class="dsh-grid dsh-grid--8-4">
    <section class="dsh-card" style="--d:420ms;">
      <div class="dsh-card__head">
        <div class="dsh-card__title">
          <div>
            <h2>Active trips</h2>
            <p>Trips that are accepted, picked up or dropped</p>
          </div>
        </div>
        <a href="{{ route('pickup-requests.index') }}" class="dsh-link">View all</a>
      </div>
      <div class="dsh-table-wrap">
        <table class="dsh-table">
          <thead>
            <tr>
              <th>Requested by</th>
              <th>Driver</th>
              <th>Vehicle</th>
              <th>Status</th>
              <th class="text-end">Progress</th>
            </tr>
          </thead>
          <tbody>
            @forelse($activeTrips as $trip)
              @php $tone = $tripTone[$trip['status_label']] ?? 'indigo'; @endphp
              <tr class="is-link" onclick="window.location='{{ $trip['url'] }}'">
                <td>
                  <div class="dsh-person">
                    <span class="dsh-avatar dsh-tone-{{ $toneFor($trip['route_title']) }}">{{ $initial($trip['route_title']) }}</span>
                    <div class="dsh-person__text">
                      <a href="{{ $trip['url'] }}" class="dsh-person__name">{{ $trip['route_title'] }}</a>
                      <span class="dsh-person__sub">{{ $trip['route_subtitle'] }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="dsh-cell-main">{{ $trip['driver_name'] }}</div>
                  <div class="dsh-cell-sub">{{ $trip['driver_id'] }}</div>
                </td>
                <td>
                  <div class="dsh-cell-main">{{ $trip['vehicle_name'] }}</div>
                  <div class="dsh-cell-sub">{{ $trip['vehicle_meta'] }}</div>
                </td>
                <td>
                  <span class="dsh-pill dsh-tone-{{ $tone }}"><i></i>{{ $trip['status_label'] }}</span>
                </td>
                <td class="text-end">
                  <div class="dsh-progress dsh-tone-{{ $tone }}">
                    <div class="dsh-track"><span style="--w:{{ $trip['progress'] }}%;"></span></div>
                    <b>{{ $trip['progress'] }}%</b>
                  </div>
                </td>
              </tr>
            @empty
              <tr class="dsh-table__blank">
                <td colspan="5">
                  <div class="dsh-empty">
                    <i data-lucide="navigation"></i>
                    <b>No trips on the road</b>
                    <span>When a driver accepts a request, the trip shows up here with live progress.</span>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <section class="dsh-card" style="--d:480ms;">
      <div class="dsh-card__head">
        <div class="dsh-card__title">
          <div>
            <h2>Today's schedule</h2>
            <p>Active school routes</p>
          </div>
        </div>
      </div>
      <ol class="dsh-sched">
        @forelse($todaySchedule as $schedule)
          @php
            [$sTone, $sState] = $scheduleTone[$schedule['status']] ?? ['amber', 'is-next'];
            $startAt = \Illuminate\Support\Str::before($schedule['time'], ' - ');
            $endAt = \Illuminate\Support\Str::after($schedule['time'], ' - ');
          @endphp
          <li class="dsh-sched__item dsh-tone-{{ $sTone }} {{ $sState }}">
            <span class="dsh-sched__time">{{ $startAt }}</span>
            <span class="dsh-sched__body">
              <span class="dsh-sched__title">{{ $schedule['title'] }}</span>
              <span class="dsh-sched__until">until {{ $endAt }}</span>
            </span>
            <span class="dsh-pill dsh-tone-{{ $sTone }}"><i></i>{{ $schedule['status'] }}</span>
          </li>
        @empty
          <li class="dsh-empty">
            <i data-lucide="calendar-x"></i>
            <b>No routes today</b>
            <span>Active school routes will be listed here.</span>
          </li>
        @endforelse
      </ol>
      <a href="{{ route('routes.index') }}" class="dsh-card__foot">View all routes</a>
    </section>
  </div>

  {{-- Queue, health, alerts --}}
  <div class="dsh-grid dsh-grid--3">
    <section class="dsh-card" style="--d:540ms;">
      <div class="dsh-card__head">
        <div class="dsh-card__title">
          <div>
            <h2>Waiting for driver</h2>
            <p>{{ number_format($pendingCount) }} {{ $pendingCount === 1 ? 'request needs' : 'requests need' }} a driver</p>
          </div>
        </div>
      </div>
      <ul class="dsh-list">
        @forelse($pendingRequests as $pending)
          <li>
            <a href="{{ route('pickup-requests.show', $pending) }}" class="dsh-list__row">
              <span class="dsh-avatar dsh-tone-{{ $toneFor($pending->requesterName()) }}">{{ $initial($pending->requesterName()) }}</span>
              <span class="dsh-list__main">
                <span class="dsh-list__name">{{ $pending->requesterName() }}</span>
                <span class="dsh-list__meta">{{ $pending->typeLabel() }} · #{{ $pending->id }}@if($pending->area?->name) · {{ $pending->area->name }}@endif</span>
              </span>
              <span class="dsh-list__side">
                @if($pending->estimated_amount)
                  <span class="dsh-amount">PKR {{ number_format((float) $pending->estimated_amount) }}</span>
                @endif
                <span class="dsh-age">{{ $pending->created_at?->diffForHumans(null, true) }}</span>
              </span>
            </a>
          </li>
        @empty
          <li class="dsh-empty">
            <i data-lucide="check-circle-2"></i>
            <b>All caught up</b>
            <span>Every request has a driver.</span>
          </li>
        @endforelse
      </ul>
      <a href="{{ route('pickup-requests.index', ['status' => 'pending']) }}" class="dsh-card__foot">Open pending queue</a>
    </section>

    <section class="dsh-card" style="--d:600ms;">
      <div class="dsh-card__head">
        <div class="dsh-card__title">
          <div>
            <h2>Service health</h2>
            <p>Quality indicators across all trips</p>
          </div>
        </div>
      </div>
      <div class="dsh-health">
        @foreach($health as $h)
          @php $hp = max(0, min(100, $h['pct'])); @endphp
          <div class="dsh-health__item">
            <div class="dsh-health__top">
              <span class="dsh-health__label">{{ $h['label'] }}</span>
              <span class="dsh-health__value" data-count="{{ $h['count'] }}" data-suffix="{{ $h['suffix'] }}" @isset($h['decimals']) data-decimals="{{ $h['decimals'] }}" @endisset>{{ $h['text'] }}</span>
            </div>
            <div class="dsh-track dsh-track--lg dsh-tone-{{ $h['tone'] }}"><span style="--w:{{ $hp }}%;"></span></div>
            <span class="dsh-health__hint">{{ $h['hint'] }}</span>
          </div>
        @endforeach
      </div>
    </section>

    <section class="dsh-card" style="--d:660ms;">
      <div class="dsh-card__head">
        <div class="dsh-card__title">
          <div>
            <h2>Recent alerts</h2>
            <p>{{ number_format($unreadCount) }} unread {{ $unreadCount === 1 ? 'notification' : 'notifications' }}</p>
          </div>
        </div>
      </div>
      <ul class="dsh-list">
        @forelse($recentAlerts as $alert)
          <li>
            <div class="dsh-list__row">
              <span class="dsh-avatar dsh-avatar--icon dsh-tone-{{ $alertTone[$alert['color']] ?? 'blue' }}"><i data-lucide="{{ $alert['icon'] }}"></i></span>
              <span class="dsh-list__main">
                <span class="dsh-list__name">{{ $alert['title'] }}</span>
                <span class="dsh-list__meta">{{ \Illuminate\Support\Str::limit($alert['message'], 70) }}</span>
              </span>
              <span class="dsh-list__side">
                <span class="dsh-age">{{ $alert['time'] }}</span>
              </span>
            </div>
          </li>
        @empty
          <li class="dsh-empty">
            <i data-lucide="bell-off"></i>
            <b>No recent alerts</b>
            <span>New notifications will appear here.</span>
          </li>
        @endforelse
      </ul>
      <a href="{{ route('notifications.index') }}" class="dsh-card__foot">View all alerts</a>
    </section>
  </div>

</div>

<style>
  /* Plain, flat page background behind the dashboard -- no gradient from the global layout. */
  html:not([data-bs-theme="dark"]) body:has(.dsh-page) { background: #f7f7f5 !important; }
  [data-bs-theme="dark"] body:has(.dsh-page) { background: #15171c !important; }

  .dsh {
    --surface: #ffffff;
    --subtle: #f4f4f2;
    --line: #e4e4e1;
    --line-strong: #d3d3cf;
    --text: #15171c;
    --text-2: #42454c;
    --muted: #6d7177;
    --faint: #9b9ea3;
    --link: #5a3fa8;
    --radius: 8px;
    --gap: 16px;
    font-family: 'Inter', system-ui, sans-serif;
    font-size: 12.5px;
    color: var(--text);
    padding: 2px 0 10px;
  }
  [data-bs-theme="dark"] .dsh {
    --surface: var(--pd-theme-card, #1b1e24);
    --subtle: rgba(255, 255, 255, .035);
    --line: var(--pd-theme-border, rgba(255, 255, 255, .09));
    --line-strong: rgba(255, 255, 255, .18);
    --text: #f1f2f4;
    --text-2: #c4c7cc;
    --muted: #92969d;
    --faint: #686c73;
    --link: #b9a6e8;
  }

  /* Colour tones: used only as flat accents -- progress bars, avatars, pills, donut. No gradients. */
  .dsh-tone-indigo  { --c: #6366f1; --c-rgb: 99, 102, 241; }
  .dsh-tone-emerald { --c: #10b981; --c-rgb: 16, 185, 129; }
  .dsh-tone-amber   { --c: #d97706; --c-rgb: 217, 119, 6; }
  .dsh-tone-rose    { --c: #e11d48; --c-rgb: 225, 29, 72; }
  .dsh-tone-violet  { --c: #8b5cf6; --c-rgb: 139, 92, 246; }
  .dsh-tone-cyan    { --c: #0e7490; --c-rgb: 14, 116, 144; }
  .dsh-tone-blue    { --c: #2563eb; --c-rgb: 37, 99, 235; }
  [data-bs-theme="dark"] .dsh .dsh-tone-indigo  { --c: #a5b4fc; }
  [data-bs-theme="dark"] .dsh .dsh-tone-emerald { --c: #6ee7b7; }
  [data-bs-theme="dark"] .dsh .dsh-tone-amber   { --c: #fcd34d; }
  [data-bs-theme="dark"] .dsh .dsh-tone-rose    { --c: #fda4af; }
  [data-bs-theme="dark"] .dsh .dsh-tone-violet  { --c: #c4b5fd; }
  [data-bs-theme="dark"] .dsh .dsh-tone-cyan    { --c: #67e8f9; }
  [data-bs-theme="dark"] .dsh .dsh-tone-blue    { --c: #93c5fd; }

  .dsh h1, .dsh h2 { font-family: 'Outfit', 'Inter', sans-serif; }
  .dsh a { text-decoration: none; color: inherit; }
  .dsh ul, .dsh ol { list-style: none; margin: 0; }
  .dsh [data-lucide] { width: 15px; height: 15px; stroke-width: 1.8; flex-shrink: 0; }

  /* Page header -- plain text, no card, no gradient */
  .dsh-head {
    display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 14px;
    margin-bottom: var(--gap); animation: dshIn .5s ease backwards;
  }
  .dsh-head__date { font-size: 12px; color: var(--muted); }
  .dsh-head h1 { margin: 4px 0 2px; font-size: 24px; font-weight: 700; letter-spacing: -.02em; color: var(--text); }
  .dsh-head p { margin: 0; font-size: 13px; color: var(--muted); }
  .dsh-head__actions { display: flex; gap: 8px; flex-wrap: wrap; }

  /* Buttons -- flat, thin border, no gradient/shadow */
  .dsh-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 34px; padding: 0 14px;
    font-size: 12.5px; font-weight: 600; border-radius: 6px; border: 1px solid var(--line-strong); white-space: nowrap;
    background: var(--surface); color: var(--text) !important;
    transition: border-color .15s ease, background-color .15s ease;
  }
  .dsh-btn:hover { background: var(--subtle); }
  .dsh-btn--primary { background: var(--text); border-color: var(--text); color: var(--surface) !important; }
  [data-bs-theme="dark"] .dsh-btn--primary { color: #15171c !important; }
  .dsh-btn--primary:hover { opacity: .88; background: var(--text); }
  .dsh-btn__count {
    min-width: 18px; height: 18px; padding: 0 5px; display: inline-flex; align-items: center; justify-content: center;
    font-size: 10.5px; font-weight: 700; color: var(--text); background: var(--subtle); border: 1px solid var(--line-strong); border-radius: 999px;
  }
  .dsh-link { font-size: 12.5px; font-weight: 600; color: var(--link) !important; transition: opacity .15s ease; }
  .dsh-link:hover { opacity: .7; text-decoration: underline; }

  /* Cards -- flat white, thin border, no shadow, no lift */
  .dsh-card {
    position: relative; display: flex; flex-direction: column; min-width: 0;
    background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius);
    transition: border-color .15s ease;
    animation: dshIn .5s ease backwards; animation-delay: var(--d, 0ms);
  }
  .dsh-card:hover { border-color: var(--line-strong); }
  .dsh-card__head {
    display: flex; justify-content: space-between; align-items: center; gap: 10px;
    padding: 14px 18px; border-bottom: 1px solid var(--line);
  }
  .dsh-card__title { display: flex; align-items: center; gap: 10px; min-width: 0; }
  .dsh-card__head h2 { margin: 0; font-size: 14px; font-weight: 600; letter-spacing: -.01em; line-height: 1.25; color: var(--text); }
  .dsh-card__head p { margin: 1px 0 0; font-size: 12px; line-height: 1.4; color: var(--muted); }
  .dsh-card__foot {
    display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: auto;
    padding: 11px 18px; font-size: 12.5px; font-weight: 600; color: var(--link) !important;
    border-top: 1px solid var(--line); border-radius: 0 0 var(--radius) var(--radius);
    transition: background-color .15s ease;
  }
  .dsh-card__foot:hover { background: var(--subtle); }

  /* Grid */
  .dsh-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: var(--gap); margin-bottom: var(--gap); }
  .dsh-grid { display: grid; gap: var(--gap); margin-bottom: var(--gap); align-items: start; }
  .dsh-grid--8-4 { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
  .dsh-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: stretch; }

  /* KPI cards -- plain number, no icon, thin flat bar */
  .dsh-kpi { padding: 18px 20px; }
  .dsh-kpi:hover { border-color: var(--c); }
  .dsh-kpi__label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
  .dsh-kpi__value { margin-top: 10px; font-family: 'Outfit', 'Inter', sans-serif; font-size: 30px; font-weight: 700; line-height: 1.05; letter-spacing: -.02em; color: var(--text); font-variant-numeric: tabular-nums; }
  .dsh-kpi__note { margin-top: 6px; font-size: 12px; color: var(--muted); }
  .dsh-kpi__meter { margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--line); }
  .dsh-kpi__meter-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px; font-size: 11.5px; color: var(--muted); }
  .dsh-kpi__meter-head b { font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; }

  /* Progress tracks -- flat single colour, no gradient */
  .dsh-track { height: 5px; border-radius: 999px; background: var(--subtle); overflow: hidden; }
  .dsh-track--lg { height: 6px; }
  .dsh-track span {
    display: block; height: 100%; width: var(--w); border-radius: inherit; background: var(--c, var(--link));
    animation: dshFill 1s ease both .3s;
  }

  /* Legend */
  .dsh-legend { display: flex; gap: 14px; font-size: 11.5px; font-weight: 500; color: var(--muted); }
  .dsh-legend span { display: inline-flex; align-items: center; gap: 6px; }
  .dsh-legend i { width: 9px; height: 9px; border-radius: 2px; }
  .dsh-legend i.is-prev { background: #6366f1; }
  .dsh-legend i.is-today { background: #d97706; }
  .dsh-legend i.is-trend { width: 11px; height: 2px; border-radius: 1px; background: #10b981; }

  /* Quick-read insight strip above the chart -- plain text, no icons, no pills */
  .dsh-insights { display: flex; flex-wrap: wrap; gap: 4px 18px; padding: 13px 18px 0; font-size: 12px; color: var(--muted); }
  .dsh-insights b { font-weight: 700; color: var(--text); }
  .dsh-insights b.is-down { color: #be123c; }
  [data-bs-theme="dark"] .dsh-insights b.is-down { color: #fda4af; }
  .dsh-insights em { font-style: normal; color: var(--muted); }

  /* Bar chart -- flat fills, no gradients or glow */
  .dsh-chart { padding: 10px 18px 4px; }
  .dsh-bars { display: block; width: 100%; height: auto; overflow: visible; }
  .dsh-bars__grid { stroke: var(--line); stroke-dasharray: 3 4; }
  .dsh-bars__grid.is-base { stroke: var(--line-strong); stroke-dasharray: none; }
  .dsh-bars__axis { fill: var(--muted); font-size: 11.5px; font-family: 'Inter', system-ui, sans-serif; font-variant-numeric: tabular-nums; }
  .dsh-bars__axis.is-today { fill: #d97706; font-weight: 700; }
  [data-bs-theme="dark"] .dsh-bars__axis.is-today { fill: #fbbf24; }
  .dsh-bars__val { fill: var(--text-2); font-size: 11px; font-weight: 700; font-family: 'Inter', system-ui, sans-serif; animation: dshFade .4s ease backwards; animation-delay: calc(.6s + var(--i) * 60ms); }
  .dsh-bars__col.is-today .dsh-bars__val { fill: #d97706; }
  [data-bs-theme="dark"] .dsh-bars__col.is-today .dsh-bars__val { fill: #fbbf24; }
  .dsh-bars__peak { fill: var(--muted); font-size: 10px; font-weight: 600; font-family: 'Inter', system-ui, sans-serif; animation: dshFade .4s ease backwards; animation-delay: calc(.65s + var(--i) * 60ms); }
  .dsh-bars__hit { fill: transparent; transition: fill .15s ease; }
  .dsh-bars__col:hover .dsh-bars__hit { fill: var(--subtle); }
  .dsh-bars__bar { fill: #6366f1; transform-box: fill-box; transform-origin: 50% 100%; animation: dshGrow .7s ease both; animation-delay: calc(.2s + var(--i) * 60ms); }
  .dsh-bars__col.is-today .dsh-bars__bar { fill: #d97706; }
  .dsh-bars__zero { fill: var(--line-strong); }

  /* Trend overlay: average reference line, soft flat area, smoothed line + dots */
  .dsh-bars__area { fill: #10b981; opacity: .08; animation: dshFade .8s ease backwards .2s; }
  .dsh-bars__avg { stroke: var(--faint); stroke-width: 1.25; stroke-dasharray: 3 3; }
  .dsh-bars__avg-label { fill: var(--muted); font-size: 10px; font-weight: 600; font-family: 'Inter', system-ui, sans-serif; }
  .dsh-bars__trend {
    fill: none; stroke: #059669; stroke-width: 1.75; stroke-linecap: round; stroke-linejoin: round;
    stroke-dasharray: 900; stroke-dashoffset: 900; animation: dshDraw 1.1s ease both .35s;
  }
  .dsh-bars__dot { fill: #fff; stroke: #059669; stroke-width: 2; animation: dshFade .4s ease backwards; animation-delay: calc(.9s + var(--i, 0) * 35ms); }
  .dsh-bars__dot.is-today { stroke: #d97706; }
  [data-bs-theme="dark"] .dsh-bars__dot { fill: #1b1e24; }

  /* Hover tooltip on each bar column */
  .dsh-bars__tip { opacity: 0; pointer-events: none; transition: opacity .15s ease; }
  .dsh-bars__col:hover .dsh-bars__tip { opacity: 1; }
  .dsh-bars__tip-bg { fill: #15171c; }
  [data-bs-theme="dark"] .dsh-bars__tip-bg { fill: #2a2d33; }
  .dsh-bars__tip-title { fill: #fff; font-size: 11.5px; font-weight: 700; font-family: 'Inter', system-ui, sans-serif; }
  .dsh-bars__tip-sep { stroke: rgba(255, 255, 255, .14); }
  .dsh-bars__tip-row { fill: rgba(255, 255, 255, .68); font-size: 11.5px; font-weight: 500; font-family: 'Inter', system-ui, sans-serif; }
  .dsh-bars__tip-row tspan.is-strong { fill: #fff; font-weight: 700; }
  .dsh-bars__tip-row tspan.is-emerald { fill: #6ee7b7; font-weight: 700; }
  .dsh-bars__tip-row tspan.is-rose { fill: #fda4af; font-weight: 700; }

  /* Weekly stat tiles under the chart -- plain, no icon, no tint */
  .dsh-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0; margin-top: auto; border-top: 1px solid var(--line); }
  .dsh-stat { display: flex; flex-direction: column; gap: 3px; padding: 14px 18px; border-left: 1px solid var(--line); }
  .dsh-stat:first-child { border-left: 0; }
  .dsh-stat__label { font-size: 11.5px; font-weight: 600; color: var(--muted); }
  .dsh-stat__value { font-family: 'Outfit', 'Inter', sans-serif; font-size: 18px; font-weight: 700; line-height: 1.15; color: var(--text); font-variant-numeric: tabular-nums; }
  .dsh-stat__note { font-size: 11.5px; color: var(--muted); }
  .dsh-delta { font-size: 11.5px; font-weight: 600; }
  .dsh-delta.is-up { color: #047857; }
  .dsh-delta.is-down { color: #be123c; }
  [data-bs-theme="dark"] .dsh-delta.is-up { color: #6ee7b7; }
  [data-bs-theme="dark"] .dsh-delta.is-down { color: #fda4af; }

  /* Status donut -- flat strokes, plain legend */
  .dsh-mix { display: flex; flex-direction: column; gap: 14px; flex: 1; padding: 18px; }
  .dsh-donut { position: relative; width: 148px; height: 148px; margin: 0 auto; }
  .dsh-donut svg { display: block; width: 100%; height: 100%; overflow: visible; animation: dshFade .6s ease backwards .2s; transform: rotate(-90deg); }
  .dsh-donut__track { fill: none; stroke: var(--subtle); stroke-width: 13; }
  .dsh-donut__seg { fill: none; stroke-width: 13; cursor: pointer; transition: stroke-width .15s ease, opacity .15s ease; }
  .dsh-donut__seg:hover, .dsh-donut__seg.is-hot { stroke-width: 16; }
  .dsh-donut.has-hot .dsh-donut__seg:not(.is-hot) { opacity: .3; }
  .dsh-donut__label {
    fill: #fff; font-size: 9px; font-weight: 700; font-family: 'Inter', system-ui, sans-serif;
    paint-order: stroke; stroke: rgba(0, 0, 0, .3); stroke-width: 2.5px; stroke-linejoin: round;
    pointer-events: none;
  }
  .dsh-donut__center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; }
  .dsh-donut__center b { font-family: 'Outfit', 'Inter', sans-serif; font-size: 24px; font-weight: 700; line-height: 1; color: var(--text); font-variant-numeric: tabular-nums; }
  .dsh-donut__center span { margin-top: 4px; font-size: 11px; color: var(--muted); }
  .dsh-legend-list { padding: 0; display: flex; flex-direction: column; gap: 1px; margin-top: auto; }
  .dsh-legend-list li {
    display: grid; grid-template-columns: 9px minmax(0, 1fr) auto 34px; grid-template-rows: auto auto;
    grid-template-areas: "swatch label count share" "bar bar bar bar";
    align-items: center; column-gap: 8px; row-gap: 5px;
    padding: 7px 4px; border-top: 1px solid var(--line); font-size: 12.5px;
  }
  .dsh-legend-list li:first-child { border-top: 0; }
  .dsh-legend-list__swatch { grid-area: swatch; width: 9px; height: 9px; border-radius: 2px; }
  .dsh-legend-list__label { grid-area: label; color: var(--text-2); font-weight: 500; }
  .dsh-legend-list__count { grid-area: count; font-weight: 700; color: var(--text); text-align: right; font-variant-numeric: tabular-nums; }
  .dsh-legend-list__share { grid-area: share; font-size: 11.5px; color: var(--muted); text-align: right; font-variant-numeric: tabular-nums; }
  .dsh-legend-list__bar { grid-area: bar; display: block; height: 3px; border-radius: 999px; background: var(--subtle); overflow: hidden; }
  .dsh-legend-list__bar span { display: block; height: 100%; width: var(--w); border-radius: inherit; animation: dshFill 1s ease both .4s; }
  .dsh-legend-list li.is-zero .dsh-legend-list__label,
  .dsh-legend-list li.is-zero .dsh-legend-list__count { color: var(--faint); font-weight: 500; }
  .dsh-legend-list li.is-zero .dsh-legend-list__swatch { opacity: .35; }
  .dsh-legend-list li.is-zero .dsh-legend-list__bar { display: none; }

  /* Table */
  .dsh-table-wrap { flex: 1; overflow-x: auto; }
  .dsh-table { width: 100%; min-width: 640px; border-collapse: collapse; }
  .dsh-table th {
    padding: 9px 18px; text-align: left; white-space: nowrap;
    font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--muted);
    background: var(--subtle); border-bottom: 1px solid var(--line);
  }
  .dsh-table th.text-end { text-align: right; }
  .dsh-table td { padding: 10px 18px; font-size: 12.5px; color: var(--text-2); border-bottom: 1px solid var(--line); vertical-align: middle; }
  .dsh-table tbody tr:last-child td { border-bottom: 0; }
  .dsh-table tr.is-link { cursor: pointer; transition: background-color .15s ease; }
  .dsh-table tr.is-link:hover { background: var(--subtle); }
  .dsh-table tr.is-link:hover .dsh-person__name { color: var(--link) !important; }
  .dsh-table__blank td { padding: 0; }
  .dsh-person { display: flex; align-items: center; gap: 10px; min-width: 0; }
  .dsh-person__text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
  .dsh-person__name { font-weight: 600; color: var(--text) !important; }
  .dsh-person__sub, .dsh-cell-sub { font-size: 11.5px; color: var(--muted); }
  .dsh-cell-main { font-weight: 500; color: var(--text-2); }

  /* Avatars & pills -- flat tint, no glow, no gradient */
  .dsh-avatar {
    width: 30px; height: 30px; flex-shrink: 0; border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    font-family: 'Outfit', 'Inter', sans-serif; font-size: 12.5px; font-weight: 700;
    color: var(--c); background: rgba(var(--c-rgb), .13);
  }
  .dsh-avatar--icon [data-lucide] { width: 14px; height: 14px; }
  .dsh-pill {
    display: inline-flex; align-items: center; gap: 5px; height: 21px; padding: 0 9px; white-space: nowrap;
    font-size: 11.5px; font-weight: 600; color: var(--c, var(--text-2)); background: rgba(var(--c-rgb, 107, 114, 128), .12); border-radius: 999px;
  }
  .dsh-pill > i { width: 5px; height: 5px; border-radius: 50%; background: var(--c, var(--muted)); }
  .dsh-progress { display: inline-flex; align-items: center; gap: 8px; }
  .dsh-progress .dsh-track { width: 76px; }
  .dsh-progress b { min-width: 30px; font-size: 11.5px; font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; }

  /* Schedule -- plain, flat left-border accent only */
  .dsh-sched { padding: 8px; display: flex; flex-direction: column; gap: 1px; }
  .dsh-sched__item {
    display: grid; grid-template-columns: 56px minmax(0, 1fr) auto; align-items: center; gap: 10px;
    padding: 12px 10px 12px 8px; border-top: 1px solid var(--line); border-left: 2px solid var(--c, var(--line-strong));
  }
  .dsh-sched__item:first-child { border-top: 0; }
  .dsh-sched__time { font-family: 'Outfit', 'Inter', sans-serif; font-size: 13px; font-weight: 700; color: var(--text); }
  .dsh-sched__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
  .dsh-sched__title { font-size: 12.5px; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dsh-sched__until { font-size: 11.5px; color: var(--muted); }

  /* Lists: pending queue + alerts */
  .dsh-list { padding: 6px; display: flex; flex-direction: column; gap: 0; }
  .dsh-list li + li { border-top: 1px solid var(--line); }
  .dsh-list__row { display: flex; align-items: center; gap: 10px; padding: 10px 8px; transition: background-color .15s ease; }
  .dsh-list__row:hover { background: var(--subtle); }
  .dsh-list__main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
  .dsh-list__name { font-size: 12.5px; font-weight: 600; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dsh-list__meta { font-size: 11.5px; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dsh-list__side { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; flex-shrink: 0; }
  .dsh-amount { padding: 2px 7px; font-size: 11.5px; font-weight: 700; color: #047857; background: rgba(16, 185, 129, .1); border-radius: 999px; font-variant-numeric: tabular-nums; }
  [data-bs-theme="dark"] .dsh-amount { color: #6ee7b7; }
  .dsh-age { font-size: 11px; color: var(--muted); white-space: nowrap; }

  /* Service health -- plain rows, flat bar */
  .dsh-health { display: flex; flex-direction: column; gap: 0; padding: 6px 18px; }
  .dsh-health__item { padding: 13px 0; border-top: 1px solid var(--line); }
  .dsh-health__item:first-child { border-top: 0; }
  .dsh-health__top { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
  .dsh-health__label { font-size: 12.5px; font-weight: 600; color: var(--text); }
  .dsh-health__value { font-family: 'Outfit', 'Inter', sans-serif; font-size: 15px; font-weight: 700; color: var(--text); white-space: nowrap; font-variant-numeric: tabular-nums; }
  .dsh-health__hint { display: block; margin-top: 7px; font-size: 11.5px; color: var(--muted); }

  /* Empty states -- plain icon, no tint tile */
  .dsh-empty { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 28px 20px; text-align: center; color: var(--muted); }
  .dsh-empty [data-lucide] { width: 22px; height: 22px; margin-bottom: 4px; color: var(--faint); }
  .dsh-empty b { font-size: 13px; font-weight: 600; color: var(--text); }
  .dsh-empty > span:not([data-lucide]) { max-width: 300px; font-size: 12px; color: var(--muted); }

  /* Motion -- subtle entrance only, no lift/scale/glow */
  @keyframes dshIn { from { opacity: 0; transform: translateY(8px); } }
  @keyframes dshFade { from { opacity: 0; } }
  @keyframes dshFill { from { width: 0; } }
  @keyframes dshGrow { from { transform: scaleY(0); } }
  @keyframes dshDraw { to { stroke-dashoffset: 0; } }

  /* Responsive */
  @media (max-width: 1399px) {
    .dsh-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dsh-stat:nth-child(3) { border-left: 0; }
    .dsh-stat:nth-child(n+3) { border-top: 1px solid var(--line); }
  }
  @media (max-width: 1199px) {
    .dsh-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dsh-grid--3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dsh-grid--3 > :last-child { grid-column: 1 / -1; }
  }
  @media (max-width: 991px) {
    .dsh-grid--8-4, .dsh-grid--3 { grid-template-columns: minmax(0, 1fr); }
    .dsh-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .dsh-stat:nth-child(3) { border-left: 1px solid var(--line); }
    .dsh-stat:nth-child(n+3) { border-top: 0; }
  }
  @media (max-width: 767px) {
    .dsh-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dsh-stat:nth-child(3) { border-left: 0; }
    .dsh-stat:nth-child(n+3) { border-top: 1px solid var(--line); }
  }
  @media (max-width: 575px) {
    .dsh { --gap: 12px; }
    .dsh-kpis { grid-template-columns: minmax(0, 1fr); }
    .dsh-head__actions, .dsh-head__actions .dsh-btn { width: 100%; }
    .dsh-card__head, .dsh-chart, .dsh-insights { padding-left: 14px; padding-right: 14px; }
    .dsh-legend { display: none; }
  }
  @media (prefers-reduced-motion: reduce) {
    .dsh *, .dsh *::before, .dsh *::after { animation: none !important; transition: none !important; }
    .dsh-donut svg { transform: rotate(-90deg); }
  }
</style>

@push('custom-scripts')
<script>
  (function () {
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.querySelectorAll('.dsh [data-count]').forEach(function (el) {
      var target = parseFloat(el.getAttribute('data-count')) || 0;
      var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
      var suffix = el.getAttribute('data-suffix') || '';
      var fmt = function (v) {
        return v.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + suffix;
      };
      if (reduce || target === 0) { el.textContent = fmt(target); return; }
      var start = null, duration = 1100;
      el.textContent = fmt(0);
      function step(ts) {
        if (start === null) start = ts;
        var p = Math.min((ts - start) / duration, 1);
        el.textContent = fmt(target * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    });
  })();

  // Status mix: hovering a donut wedge or its legend row highlights both and swaps the
  // centre stat to that status; leaving either restores the running total.
  (function () {
    var mix = document.getElementById('dshStatusMix');
    if (!mix) return;
    var donut = mix.querySelector('.dsh-donut');
    var segs = mix.querySelectorAll('.dsh-donut__seg');
    var rows = mix.querySelectorAll('.dsh-legend-list li');
    var centerVal = mix.querySelector('.dsh-donut__center b');
    var centerLabel = mix.querySelector('.dsh-donut__center span');

    function setHot(key) {
      segs.forEach(function (seg) { seg.classList.toggle('is-hot', seg.dataset.key === key); });
      rows.forEach(function (row) { row.classList.toggle('is-hot', row.dataset.key === key); });
      donut.classList.toggle('has-hot', !!key);
      if (key) {
        var seg = mix.querySelector('.dsh-donut__seg[data-key="' + key + '"]');
        if (seg && centerVal && centerLabel) {
          centerVal.textContent = Number(seg.dataset.n).toLocaleString('en-US');
          centerLabel.textContent = seg.dataset.label + ' · ' + seg.dataset.share + '%';
        }
      } else if (centerVal && centerLabel) {
        centerVal.textContent = Number(centerVal.getAttribute('data-default-count')).toLocaleString('en-US');
        centerLabel.textContent = centerLabel.getAttribute('data-default-label');
      }
    }

    segs.forEach(function (seg) {
      seg.addEventListener('mouseenter', function () { setHot(seg.dataset.key); });
    });
    rows.forEach(function (row) {
      if (!row.dataset.key) return;
      row.addEventListener('mouseenter', function () { setHot(row.dataset.key); });
      row.addEventListener('mouseleave', function () { setHot(null); });
    });
    donut.addEventListener('mouseleave', function () { setHot(null); });
  })();
</script>
@endpush
@endsection
