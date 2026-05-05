<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reservation Order - {{ $reservation->product }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 11px;
      color: #1a1a1a;
      line-height: 1.4;
    }

    .page {
      padding: 30px 40px;
    }

    /* Header */
    .header {
      display: table;
      width: 100%;
      margin-bottom: 20px;
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
      height: 50px;
      margin-bottom: 8px;
    }

    .company-name {
      font-size: 10px;
      color: #555;
    }

    .doc-title {
      font-size: 20px;
      font-weight: bold;
      color: #1a365d;
      margin-bottom: 4px;
    }

    .doc-date {
      font-size: 10px;
      color: #666;
    }

    /* Divider */
    .divider {
      border: none;
      border-top: 2px solid #1a365d;
      margin: 15px 0;
    }

    .divider-light {
      border: none;
      border-top: 1px solid #ddd;
      margin: 12px 0;
    }

    /* Info sections */
    .info-row {
      display: table;
      width: 100%;
      margin-bottom: 15px;
    }

    .info-col {
      display: table-cell;
      vertical-align: top;
      width: 50%;
      padding-right: 20px;
    }

    .info-col:last-child {
      padding-right: 0;
      padding-left: 20px;
    }

    .section-label {
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #1a365d;
      margin-bottom: 6px;
      padding-bottom: 3px;
      border-bottom: 1px solid #e2e8f0;
    }

    .field-row {
      display: table;
      width: 100%;
      margin-bottom: 3px;
    }

    .field-label {
      display: table-cell;
      width: 40%;
      font-size: 10px;
      color: #666;
      padding: 2px 0;
    }

    .field-value {
      display: table-cell;
      width: 60%;
      font-size: 10px;
      font-weight: 600;
      color: #1a1a1a;
      padding: 2px 0;
    }

    /* Dates */
    .dates-container {
      margin-top: 4px;
    }

    .date-badge {
      display: inline-block;
      background: #edf2f7;
      color: #2d3748;
      font-size: 9px;
      padding: 3px 8px;
      border-radius: 3px;
      margin: 2px 3px 2px 0;
    }

    /* Financial table */
    .financial-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
    }

    .financial-table th {
      background: #1a365d;
      color: #fff;
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 8px 12px;
      text-align: left;
    }

    .financial-table th:last-child {
      text-align: right;
    }

    .financial-table td {
      padding: 7px 12px;
      font-size: 10px;
      border-bottom: 1px solid #edf2f7;
    }

    .financial-table td:last-child {
      text-align: right;
      font-weight: 600;
    }

    .financial-table tr:nth-child(even) {
      background: #f7fafc;
    }

    .financial-table .total-row td {
      background: #1a365d;
      color: #fff;
      font-weight: bold;
      font-size: 11px;
      border-bottom: none;
    }

    /* Remark */
    .remark-box {
      background: #f7fafc;
      border: 1px solid #e2e8f0;
      border-radius: 4px;
      padding: 10px 14px;
      margin-top: 6px;
      font-size: 10px;
      color: #4a5568;
      white-space: pre-wrap;
    }

    /* Read & Approved Section */
    .approval-section {
      margin-top: 25px;
    }

    .approval-title {
      font-size: 13px;
      font-weight: bold;
      color: #1a365d;
      margin-bottom: 2px;
    }

    .approval-text {
      font-size: 10px;
      color: #333;
      margin-bottom: 1px;
    }

    .approval-instruction {
      font-size: 11px;
      font-weight: bold;
      color: #1a365d;
      margin-bottom: 8px;
    }

    .approval-fields {
      margin-top: 4px;
    }

    .approval-field {
      display: table;
      width: 60%;
      margin-bottom: 10px;
    }

    .approval-field-label {
      display: table-cell;
      width: 25%;
      font-size: 10px;
      color: #333;
      vertical-align: bottom;
      padding-bottom: 2px;
    }

    .approval-field-line {
      display: table-cell;
      width: 75%;
      border-bottom: 1px dotted #666;
      vertical-align: bottom;
      padding-bottom: 2px;
    }

    .approval-date-box {
      display: table;
      margin-top: 10px;
    }

    .approval-date-label {
      display: table-cell;
      font-size: 10px;
      color: #333;
      vertical-align: middle;
      padding-right: 6px;
    }

    .approval-date-input {
      display: table-cell;
      border: 1px solid #333;
      width: 100px;
      height: 20px;
      vertical-align: middle;
    }

    .seal-container {
      position: absolute;
      right: 40px;
      width: 180px;
      height: 120px;
      border: 2px solid #1a365d;
      text-align: center;
      line-height: 120px;
      font-size: 12px;
      font-weight: bold;
      color: #1a365d;
    }

    /* For Office Use Section */
    .office-section {
      margin-top: 30px;
    }

    .office-title {
      font-size: 13px;
      font-weight: bold;
      color: #1a365d;
      margin-bottom: 8px;
    }

    .office-row {
      display: table;
      width: 100%;
      margin-bottom: 0;
    }

    .office-col {
      display: table-cell;
      width: 50%;
      vertical-align: top;
    }

    .office-field {
      display: table;
      margin-bottom: 4px;
    }

    .office-field-label {
      display: table-cell;
      background: #1a365d;
      color: #fff;
      font-size: 10px;
      font-weight: bold;
      padding: 5px 10px;
      white-space: nowrap;
      vertical-align: middle;
    }

    .office-field-value {
      display: table-cell;
      border: 1px solid #ccc;
      border-left: none;
      font-size: 10px;
      padding: 5px 10px;
      min-width: 140px;
      vertical-align: middle;
      color: #333;
    }

    /* Disclaimer */
    .disclaimer {
      margin-top: 20px;
      text-align: center;
      font-size: 10px;
      font-style: italic;
      font-weight: bold;
      color: #1a365d;
    }

    /* Footer wave */
    .footer-wave {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 50px;
      overflow: hidden;
    }

    .footer-wave svg {
      width: 100%;
      height: 100%;
    }
  </style>
</head>
<body>
  <div class="page">
    {{-- Header --}}
    <div class="header">
      <div class="header-left">
        <img src="{{ $logoPath }}" class="logo" alt="La Sentinelle">
      </div>
      <div class="header-right">
        <div class="doc-title">Reservation Order</div>
      </div>
    </div>

    <div style="display: table; width: 100%; margin-bottom: 10px;">
      <div style="display: table-cell; vertical-align: top; width: 50%;">
        <div style="font-size: 10px; font-weight: bold; color: #1a365d;">VAT No. : VAT20080941 | BRN No. : C07001312</div>
        <div style="font-size: 9px; color: #666; margin-top: 2px;">Rue des Oursins, Tombeau Bay, 2173 Tel: 206-8200</div>
      </div>
      <div style="display: table-cell; vertical-align: top; width: 50%; text-align: right;">
        <div class="doc-date">Date: {{ $reservation->created_at->format('d/m/Y') }}</div>
        <div class="doc-date">Ref: {{ $reservation->id }}</div>
        @if($reservation->purchase_order_no)
          <div class="doc-date">PO #: {{ $reservation->purchase_order_no }}</div>
        @endif
        @if($reservation->invoice_no)
          <div class="doc-date">Invoice #: {{ $reservation->invoice_no }}</div>
        @endif
        @if($reservation->is_cash)
          <div class="doc-date" style="font-weight: bold; color: #1a365d;">Payment: Cash</div>
        @endif
      </div>
    </div>

    <hr class="divider">

    {{-- Client & Agency Info --}}
    <div class="info-row">
      <div class="info-col">
        <div class="section-label">Client Information</div>
        <div class="field-row">
          <div class="field-label">Company</div>
          <div class="field-value">{{ $reservation->client->company_name }}</div>
        </div>
        @if($reservation->client->brn)
          <div class="field-row">
            <div class="field-label">BRN</div>
            <div class="field-value">{{ $reservation->client->brn }}</div>
          </div>
        @endif
        @if($reservation->client->vat_number)
          <div class="field-row">
            <div class="field-label">VAT No.</div>
            <div class="field-value">{{ $reservation->client->vat_number }}</div>
          </div>
        @endif
        @if($reservation->client->contact_person_name)
          <div class="field-row">
            <div class="field-label">Contact Person</div>
            <div class="field-value">{{ $reservation->client->contact_person_name }}</div>
          </div>
        @endif
        @if($reservation->client->phone)
          <div class="field-row">
            <div class="field-label">Phone</div>
            <div class="field-value">{{ $reservation->client->phone }}</div>
          </div>
        @endif
      </div>
      <div class="info-col">
        @if($reservation->representedClient)
          <div class="section-label">Representing</div>
          <div class="field-row">
            <div class="field-label">Company</div>
            <div class="field-value">{{ $reservation->representedClient->company_name }}</div>
          </div>
          @if($reservation->representedClient->brn)
            <div class="field-row">
              <div class="field-label">BRN</div>
              <div class="field-value">{{ $reservation->representedClient->brn }}</div>
            </div>
          @endif
          @if($reservation->representedClient->contact_person_name)
            <div class="field-row">
              <div class="field-label">Contact Person</div>
              <div class="field-value">{{ $reservation->representedClient->contact_person_name }}</div>
            </div>
          @endif
        @endif
      </div>
    </div>

    <hr class="divider-light">

    {{-- Product Details --}}
    <div class="info-row">
      <div class="info-col">
        <div class="section-label">Product Details</div>
        <div class="field-row">
          <div class="field-label">Product</div>
          <div class="field-value">{{ $reservation->product }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Platform</div>
          <div class="field-value">{{ $reservation->platform?->name ?? '—' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Placement</div>
          <div class="field-value">{{ $reservation->placement->name }}</div>
        </div>
      </div>
      <div class="info-col">
        <div class="section-label">Campaign Details</div>
        <div class="field-row">
          <div class="field-label">Channel</div>
          <div class="field-value">{{ $reservation->channel }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Scope</div>
          <div class="field-value">{{ $reservation->scope }}</div>
        </div>
      </div>
    </div>

    <hr class="divider-light">

    {{-- Reservation Dates --}}
    <div style="margin-bottom: 15px;">
      <div class="section-label">Reservation Dates</div>
      <div class="dates-container">
        @foreach($reservation->formattedDateRanges() as $range)
          <span class="date-badge">{{ $range }}</span>
        @endforeach
      </div>
      @if($reservation->bill_at_end_of_campaign)
        <div style="margin-top: 6px; font-size: 10px; font-weight: bold; color: #1a365d;">
          Billing: at end of campaign
        </div>
      @endif
    </div>

    <hr class="divider-light">

    {{-- Financials --}}
    <div class="section-label">Financial Summary</div>
    <table class="financial-table">
      <thead>
        <tr>
          <th>Description</th>
          <th>Amount (MUR)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Gross Amount</td>
          <td>{{ number_format($reservation->gross_amount, 2) }}</td>
        </tr>
        @if($reservation->discount > 0)
          <tr>
            <td>Discount</td>
            <td>- {{ number_format($reservation->discount, 2) }}</td>
          </tr>
        @endif
        @if($reservation->commission > 0)
          <tr>
            <td>Commission</td>
            <td>- {{ number_format($reservation->commission, 2) }}</td>
          </tr>
        @endif
        <tr>
          <td>VAT (15%) {{ $reservation->vat_exempt ? '— Exempt' : '' }}</td>
          <td>{{ number_format($reservation->vat, 2) }}</td>
        </tr>
        <tr class="total-row">
          <td>Total Amount to Pay</td>
          <td>MUR {{ number_format($reservation->total_amount_to_pay, 2) }}</td>
        </tr>
        @if($reservation->is_foreign_currency && $reservation->foreign_currency_code)
          <tr>
            <td>Foreign Currency Equivalent</td>
            <td>{{ $reservation->foreign_currency_code->value }} {{ number_format((float) $reservation->foreign_currency_amount, 2) }}</td>
          </tr>
        @endif
      </tbody>
    </table>

    @if($reservation->parent)
      <div style="margin-top: 12px; font-size: 10px; color: #1a365d;">
        <strong>Linked to parent reservation:</strong>
        <span style="font-family: 'Courier New', monospace;">{{ $reservation->parent->id }}</span>
        — {{ $reservation->parent->product }}
      </div>
    @endif

    {{-- Remark --}}
    @if($reservation->remark)
      <div style="margin-top: 15px;">
        <div class="section-label">Remarks</div>
        <div class="remark-box">{{ $reservation->remark }}</div>
      </div>
    @endif

    {{-- Read & Approved --}}
    <div class="approval-section" style="position: relative;">
      <div class="approval-title">Read &amp; approved (Include company's seal)</div>
      <div class="approval-text">I acknowledge having read, understood and accept the conditions regarding the sales.</div>
      <div class="approval-instruction">Full name &amp; designation of person authorising this reservation :</div>

      <div style="display: table; width: 100%;">
        <div style="display: table-cell; width: 62%; vertical-align: top;">
          <div class="approval-fields">
            <div class="approval-field">
              <div class="approval-field-label">Name :</div>
              <div class="approval-field-line"></div>
            </div>
            <div class="approval-field">
              <div class="approval-field-label">Job title :</div>
              <div class="approval-field-line"></div>
            </div>
            <div class="approval-field">
              <div class="approval-field-label" style="white-space: nowrap;">Please write "Read &amp; approved" :</div>
              <div class="approval-field-line"></div>
            </div>
            <div class="approval-field">
              <div class="approval-field-label">Signature :</div>
              <div class="approval-field-line"></div>
            </div>
            <div class="approval-date-box">
              <div class="approval-date-label">Date :</div>
              <div class="approval-date-input"></div>
            </div>
          </div>
        </div>
        <div style="display: table-cell; width: 38%; vertical-align: top; text-align: right;">
          <div style="border: 2px solid #1a365d; width: 170px; height: 110px; margin-left: auto; text-align: center; line-height: 110px;">
            <span style="font-size: 13px; font-weight: bold; color: #1a365d;">Company's Seal</span>
          </div>
        </div>
      </div>
    </div>

    {{-- For Office Use --}}
    <div class="office-section">
      <div class="office-title">For office use</div>
      <div class="office-row">
        <div class="office-col">
          <div class="office-field">
            <div class="office-field-label">Booked by :</div>
            <div class="office-field-value">
              @if($reservation->salesperson)
                {{ $reservation->salesperson->first_name }} {{ $reservation->salesperson->last_name }}
              @endif
            </div>
          </div>
          <div class="office-field">
            <div class="office-field-label">Date :</div>
            <div class="office-field-value">{{ $reservation->created_at->format('d-M-Y') }}</div>
          </div>
        </div>
        <div class="office-col">
          <div class="office-field">
            <div class="office-field-label">Checked by :</div>
            <div class="office-field-value">&nbsp;</div>
          </div>
          <div class="office-field">
            <div class="office-field-label">Date :</div>
            <div class="office-field-value">&nbsp;</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Disclaimer --}}
    <div class="disclaimer">
      Reservations are accepted subject to availability and to the terms and conditions of La Sentinelle Ltd.
    </div>
  </div>

  {{-- Footer blue wave design --}}
  <div class="footer-wave">
    <svg viewBox="0 0 800 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,60 L0,25 Q150,0 350,20 Q550,42 800,10 L800,60 Z" fill="#1a365d"/>
      <path d="M0,60 L0,40 Q200,15 450,35 Q650,50 800,25 L800,60 Z" fill="#2c5282"/>
    </svg>
  </div>
</body>
</html>
