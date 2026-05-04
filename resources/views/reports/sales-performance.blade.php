<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Performance Report - {{ $platform->name }}</title>
  <style>
    /*
     * Inline CSS retained because DomPDF does not support CSS variables,
     * Tailwind utilities, or external stylesheets. Hex values mirror the
     * La Sentinelle brand tokens defined in resources/css/app.css.
     */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 10px;
      color: #0f2c44; /* --ls-ink */
      line-height: 1.4;
    }

    .page {
      padding: 25px 30px;
    }

    .header {
      display: table;
      width: 100%;
      margin-bottom: 15px;
    }

    .header-left {
      display: table-cell;
      vertical-align: top;
      width: 50%;
    }

    .header-right {
      display: table-cell;
      vertical-align: top;
      width: 50%;
      text-align: right;
    }

    .logo {
      height: 40px;
      margin-bottom: 6px;
    }

    .company-name {
      font-size: 9px;
      color: #5b80a3; /* --ls-slate-dark */
    }

    .doc-title {
      font-size: 18px;
      font-weight: bold;
      color: #1f4a6b; /* --ls-deep-darker */
      margin-bottom: 3px;
    }

    .doc-date {
      font-size: 9px;
      color: #5b80a3; /* --ls-slate-dark */
    }

    .divider {
      border: none;
      border-top: 2px solid #1f4a6b; /* --ls-deep-darker */
      margin: 12px 0;
    }

    .meta {
      font-size: 10px;
      color: #14354f; /* --ls-ink-shade */
      margin-bottom: 15px;
    }

    .meta span {
      margin-right: 20px;
    }

    .salesperson-section {
      margin-bottom: 20px;
      page-break-inside: avoid;
    }

    .salesperson-header {
      display: table;
      width: 100%;
      margin-bottom: 6px;
    }

    .salesperson-name {
      display: table-cell;
      font-size: 11px;
      font-weight: bold;
      color: #1f4a6b; /* --ls-deep-darker */
    }

    .salesperson-achievement {
      display: table-cell;
      text-align: right;
      font-size: 10px;
      font-weight: 600;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      background-color: #1f4a6b; /* --ls-deep-darker */
      color: #ffffff;
      font-size: 8px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      padding: 6px 8px;
      text-align: left;
    }

    th.right {
      text-align: right;
    }

    td {
      padding: 5px 8px;
      font-size: 9px;
      border-bottom: 1px solid #dde6ea; /* --ls-cream-dark */
    }

    td.right {
      text-align: right;
    }

    tr:nth-child(even) {
      background-color: #f1f6f8; /* --ls-cream */
    }

    tr.totals-row {
      background-color: #c8e2ee !important; /* --ls-sky-soft */
      font-weight: 600;
    }

    tr.totals-row td {
      border-top: 2px solid #1f4a6b; /* --ls-deep-darker */
      border-bottom: none;
      padding-top: 6px;
      padding-bottom: 6px;
    }

    .achievement-high { color: #1e5843; } /* --ls-success-text */
    .achievement-mid { color: #7a5318; }  /* --ls-warning-text */
    .achievement-low { color: #5b80a3; }  /* --ls-slate-dark */

    .section-divider {
      border: none;
      border-top: 1px solid #dde6ea; /* --ls-cream-dark */
      margin: 15px 0;
    }

    .footer {
      margin-top: 20px;
      padding-top: 10px;
      border-top: 1px solid #dde6ea; /* --ls-cream-dark */
      font-size: 8px;
      color: #7298bd; /* --ls-slate */
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="header">
      <div class="header-left">
        @if(file_exists($logoPath))
          <img src="{{ $logoPath }}" class="logo" alt="Logo">
        @endif
        <div class="company-name">La Sentinelle Ltd</div>
      </div>
      <div class="header-right">
        <div class="doc-title">Sales Performance Report</div>
        <div class="doc-date">{{ $now->format('d F Y') }}</div>
      </div>
    </div>

    <hr class="divider">

    <div class="meta">
      <span><strong>Platform:</strong> {{ $platform->name }}</span>
      <span><strong>Financial Year:</strong> {{ $financialYearLabel }}</span>
    </div>

    @foreach($data['salespersons'] as $entry)
      @php
        $achievementClass = $entry['totals']['percentage'] >= 100 ? 'achievement-high' : ($entry['totals']['percentage'] >= 75 ? 'achievement-mid' : 'achievement-low');
      @endphp

      @if(! $loop->first)
        <hr class="section-divider">
      @endif

      <div class="salesperson-section">
        <div class="salesperson-header">
          <div class="salesperson-name">{{ $entry['salesperson']->first_name }} {{ $entry['salesperson']->last_name }}</div>
          <div class="salesperson-achievement {{ $achievementClass }}">{{ number_format($entry['totals']['percentage'], 1) }}% Achievement</div>
        </div>

        <table>
          <thead>
            <tr>
              <th>Month</th>
              <th class="right">Target (MUR)</th>
              <th class="right">Sales (MUR)</th>
              <th class="right">Reservations</th>
            </tr>
          </thead>
          <tbody>
            @foreach($data['months'] as $i => $month)
              <tr>
                <td>{{ $month['label'] }}</td>
                <td class="right">{{ number_format($entry['months'][$i]['target'], 2) }}</td>
                <td class="right">{{ number_format($entry['months'][$i]['sales'], 2) }}</td>
                <td class="right">{{ $entry['months'][$i]['reservations'] }}</td>
              </tr>
            @endforeach
            <tr class="totals-row">
              <td>FY Total</td>
              <td class="right">{{ number_format($entry['totals']['target'], 2) }}</td>
              <td class="right">{{ number_format($entry['totals']['sales'], 2) }}</td>
              <td class="right">{{ $entry['totals']['reservations'] }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    @endforeach

    <div class="footer">
      Generated on {{ $now->format('d/m/Y \a\t H:i') }} &middot; Digital Bookings &middot; La Sentinelle Ltd
    </div>
  </div>
</body>
</html>
