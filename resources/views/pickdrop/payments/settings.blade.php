@extends('layout.master')
@section('page-content-class', 'container-fluid')

@php
  $selectedBank = old('bank_name', $settings->bank_name);
  $bankOptions = $banks;
  if (filled($selectedBank) && !in_array($selectedBank, $bankOptions, true)) {
      array_unshift($bankOptions, $selectedBank);
  }
@endphp

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Bank account</h4>
    <p class="text-secondary mb-0">Company details and the bank account customers use to pay invoices</p>
  </div>
  <a href="{{ route('payments.index') }}" class="btn btn-outline-dark">Back to invoices</a>
</div>

<form method="POST" action="{{ route('payments.settings.update') }}">
  @csrf
  @method('PUT')

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h6 class="mb-0">Invoice letterhead</h6></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Company name</label>
            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $settings->company_name) }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="company_email" class="form-control" value="{{ old('company_email', $settings->company_email) }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone', $settings->company_phone) }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $settings->company_address) }}</textarea>
          </div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Invoice prefix</label>
              <input type="text" name="invoice_prefix" class="form-control" maxlength="10" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tax %</label>
              <input type="number" step="0.01" min="0" max="100" name="tax_percent" class="form-control" value="{{ old('tax_percent', $settings->tax_percent) }}" required>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header"><h6 class="mb-0">Receiving bank account</h6></div>
        <div class="card-body">
          <p class="text-secondary small">This is the only payment method. These details appear on invoices so customers can transfer payment.</p>
          <div class="mb-3">
            <label class="form-label">Bank</label>
            <select name="bank_name" class="form-select" required>
              <option value="">Select bank</option>
              @foreach($bankOptions as $bank)
                <option value="{{ $bank }}" {{ $selectedBank === $bank ? 'selected' : '' }}>{{ $bank }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Account title</label>
            <input type="text" name="bank_account_title" class="form-control" value="{{ old('bank_account_title', $settings->bank_account_title) }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Account number</label>
            <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $settings->bank_account_number) }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">IBAN</label>
            <input type="text" name="bank_iban" class="form-control" value="{{ old('bank_iban', $settings->bank_iban) }}">
          </div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">SWIFT</label>
              <input type="text" name="bank_swift" class="form-control" value="{{ old('bank_swift', $settings->bank_swift) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Branch</label>
              <input type="text" name="bank_branch" class="form-control" value="{{ old('bank_branch', $settings->bank_branch) }}">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <button type="submit" class="btn btn-dark">Save bank settings</button>
  </div>
</form>

@endsection
