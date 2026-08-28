@extends('layout.master')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-1">Platform settings</h4>
    <p class="text-secondary mb-0">SMS, push, JazzCash / EasyPaisa, cancellation, geofence, referral</p>
  </div>
</div>

<form method="POST" action="{{ route('platform-settings.update') }}">
  @csrf
  @method('PUT')
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">SMS OTP</h6></div>
        <div class="card-body">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="sms_enabled" value="1" id="sms_enabled" {{ old('sms_enabled', $settings->sms_enabled) ? 'checked' : '' }}>
            <label class="form-check-label" for="sms_enabled">Enable SMS API</label>
          </div>
          <label class="form-label">Provider</label>
          <input class="form-control mb-3" name="sms_provider" value="{{ old('sms_provider', $settings->sms_provider) }}" placeholder="jazz / custom">
          <label class="form-label">API URL</label>
          <input class="form-control mb-3" name="sms_api_url" value="{{ old('sms_api_url', $settings->sms_api_url) }}" placeholder="https://...&phone={phone}&message={message}&key={key}">
          <label class="form-label">API key</label>
          <input class="form-control mb-3" name="sms_api_key" placeholder="Leave blank to keep current">
          <label class="form-label">Sender ID</label>
          <input class="form-control" name="sms_sender" value="{{ old('sms_sender', $settings->sms_sender) }}">
        </div>
      </div>
      <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">FCM push</h6></div>
        <div class="card-body">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="fcm_enabled" value="1" id="fcm_enabled" {{ old('fcm_enabled', $settings->fcm_enabled) ? 'checked' : '' }}>
            <label class="form-check-label" for="fcm_enabled">Enable Firebase push</label>
          </div>
          <label class="form-label">Server key</label>
          <input class="form-control" name="fcm_server_key" placeholder="Leave blank to keep current">
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h6 class="mb-0">Ops rules</h6></div>
        <div class="card-body">
          <label class="form-label">Cancel window (hours)</label>
          <input type="number" class="form-control mb-3" name="cancel_hours" value="{{ old('cancel_hours', $settings->cancel_hours) }}" required>
          <label class="form-label">Cancel fee %</label>
          <input type="number" step="0.01" class="form-control mb-3" name="cancel_fee_percent" value="{{ old('cancel_fee_percent', $settings->cancel_fee_percent) }}" required>
          <label class="form-label">Arrival geofence (meters)</label>
          <input type="number" class="form-control mb-3" name="geofence_meters" value="{{ old('geofence_meters', $settings->geofence_meters) }}" required>
          <label class="form-label">Referral bonus (PKR)</label>
          <input type="number" step="0.01" class="form-control mb-3" name="referral_bonus" value="{{ old('referral_bonus', $settings->referral_bonus) }}" required>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="pickup_otp_enabled" value="1" id="pickup_otp" {{ old('pickup_otp_enabled', $settings->pickup_otp_enabled) ? 'checked' : '' }}>
            <label class="form-check-label" for="pickup_otp">Require pickup OTP at stop</label>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">JazzCash</h6></div>
        <div class="card-body">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="jazzcash_enabled" value="1" id="jc" {{ old('jazzcash_enabled', $settings->jazzcash_enabled) ? 'checked' : '' }}>
            <label class="form-check-label" for="jc">Enable JazzCash</label>
          </div>
          <label class="form-label">Merchant ID</label>
          <input class="form-control mb-3" name="jazzcash_merchant_id" value="{{ old('jazzcash_merchant_id', $settings->jazzcash_merchant_id) }}">
          <label class="form-label">Password</label>
          <input class="form-control mb-3" name="jazzcash_password" placeholder="Leave blank to keep current">
          <label class="form-label">Integrity salt</label>
          <input class="form-control mb-3" name="jazzcash_integrity_salt" placeholder="Leave blank to keep current">
          <label class="form-label">Return URL</label>
          <input class="form-control" name="jazzcash_return_url" value="{{ old('jazzcash_return_url', $settings->jazzcash_return_url) }}" placeholder="{{ url('/payments/jazzcash/callback') }}">
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h6 class="mb-0">EasyPaisa</h6></div>
        <div class="card-body">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="easypaisa_enabled" value="1" id="ep" {{ old('easypaisa_enabled', $settings->easypaisa_enabled) ? 'checked' : '' }}>
            <label class="form-check-label" for="ep">Enable EasyPaisa</label>
          </div>
          <label class="form-label">Store ID</label>
          <input class="form-control mb-3" name="easypaisa_store_id" value="{{ old('easypaisa_store_id', $settings->easypaisa_store_id) }}">
          <label class="form-label">Hash key</label>
          <input class="form-control mb-3" name="easypaisa_hash_key" placeholder="Leave blank to keep current">
          <label class="form-label">Return URL</label>
          <input class="form-control" name="easypaisa_return_url" value="{{ old('easypaisa_return_url', $settings->easypaisa_return_url) }}">
        </div>
      </div>
      <button class="btn btn-dark w-100 mt-3" type="submit">Save settings</button>
    </div>
  </div>
</form>
@endsection
