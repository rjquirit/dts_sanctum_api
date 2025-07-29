@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Profile</div>
                <div class="card-body">
                    <h5>Two-Factor Authentication (2FA)</h5>
                    <div id="twofa-section">
                        <div id="twofa-status" class="mb-3"></div>
                        <div id="twofa-actions"></div>
                        <div id="twofa-qr" class="my-3"></div>
                        <div id="twofa-recovery" class="my-3"></div>
                        <div id="twofa-confirm" class="my-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
import {
  get2FAStatus, enable2FA, confirm2FA, disable2FA, get2FAQrAndCodes, get2FARecoveryCodes
} from '/js/modules/auth.js';

const section = document.getElementById('twofa-section');
const statusDiv = document.getElementById('twofa-status');
const actionsDiv = document.getElementById('twofa-actions');
const qrDiv = document.getElementById('twofa-qr');
const recoveryDiv = document.getElementById('twofa-recovery');
const confirmDiv = document.getElementById('twofa-confirm');

async function render2FA() {
  statusDiv.innerHTML = 'Loading...';
  actionsDiv.innerHTML = '';
  qrDiv.innerHTML = '';
  recoveryDiv.innerHTML = '';
  confirmDiv.innerHTML = '';
  try {
    const status = await get2FAStatus();
    if (!status.two_factor_enabled) {
      statusDiv.innerHTML = '<span class="badge bg-secondary">2FA Disabled</span>';
      actionsDiv.innerHTML = `<button class="btn btn-primary" id="enable2faBtn">Enable 2FA</button>`;
      document.getElementById('enable2faBtn').onclick = enable2FAFlow;
    } else if (status.two_factor_enabled && !status.two_factor_confirmed) {
      statusDiv.innerHTML = '<span class="badge bg-warning text-dark">2FA Pending Confirmation</span>';
      await showQrAndRecovery();
      confirmDiv.innerHTML = `
        <form id="confirm2faForm" class="row g-2">
          <div class="col-auto">
            <input type="text" class="form-control" id="confirm2faCode" placeholder="Enter 2FA code">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-success">Confirm 2FA</button>
          </div>
        </form>
        <div id="confirm2faMsg" class="mt-2"></div>
      `;
      document.getElementById('confirm2faForm').onsubmit = confirm2FAFlow;
      actionsDiv.innerHTML = `<button class="btn btn-danger" id="disable2faBtn">Disable 2FA</button>`;
      document.getElementById('disable2faBtn').onclick = disable2FAFlow;
    } else {
      statusDiv.innerHTML = '<span class="badge bg-success">2FA Enabled</span>';
      actionsDiv.innerHTML = `<button class="btn btn-danger" id="disable2faBtn">Disable 2FA</button> <button class="btn btn-outline-secondary" id="showRecoveryBtn">Show Recovery Codes</button>`;
      document.getElementById('disable2faBtn').onclick = disable2FAFlow;
      document.getElementById('showRecoveryBtn').onclick = showRecoveryCodes;
    }
  } catch (e) {
    statusDiv.innerHTML = `<span class="text-danger">${e.message}</span>`;
  }
}

async function enable2FAFlow() {
  actionsDiv.innerHTML = 'Enabling...';
  try {
    await enable2FA();
    await render2FA();
  } catch (e) {
    actionsDiv.innerHTML = `<span class="text-danger">${e.message}</span>`;
  }
}

async function showQrAndRecovery() {
  try {
    const data = await get2FAQrAndCodes();
    qrDiv.innerHTML = data.qr_code ? `<div><strong>Scan QR Code:</strong><br>${data.qr_code}</div>` : '';
    if (data.recovery_codes && data.recovery_codes.length) {
      recoveryDiv.innerHTML = `<div><strong>Recovery Codes:</strong><pre>${data.recovery_codes.join('\n')}</pre></div>`;
    }
  } catch (e) {
    qrDiv.innerHTML = `<span class="text-danger">${e.message}</span>`;
  }
}

async function confirm2FAFlow(e) {
  e.preventDefault();
  const code = document.getElementById('confirm2faCode').value.trim();
  const msgDiv = document.getElementById('confirm2faMsg');
  msgDiv.innerHTML = 'Confirming...';
  try {
    await confirm2FA(code);
    msgDiv.innerHTML = '<span class="text-success">2FA confirmed!</span>';
    setTimeout(render2FA, 1000);
  } catch (e) {
    msgDiv.innerHTML = `<span class="text-danger">${e.message}</span>`;
  }
}

async function disable2FAFlow() {
  actionsDiv.innerHTML = 'Disabling...';
  try {
    await disable2FA();
    await render2FA();
  } catch (e) {
    actionsDiv.innerHTML = `<span class="text-danger">${e.message}</span>`;
  }
}

async function showRecoveryCodes() {
  recoveryDiv.innerHTML = 'Loading...';
  try {
    const data = await get2FARecoveryCodes();
    if (data.recovery_codes && data.recovery_codes.length) {
      recoveryDiv.innerHTML = `<div><strong>Recovery Codes:</strong><pre>${data.recovery_codes.join('\n')}</pre></div>`;
    } else {
      recoveryDiv.innerHTML = '<span class="text-warning">No recovery codes found.</span>';
    }
  } catch (e) {
    recoveryDiv.innerHTML = `<span class="text-danger">${e.message}</span>`;
  }
}

render2FA();
</script>
@endpush
