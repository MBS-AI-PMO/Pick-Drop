# Payment API changes (screenshot flow)

Applies to both apps. Use `/api/parent/...` for the Parent app and `/api/self/...` for the Self app.
All endpoints need `Authorization: Bearer <token>`.

## Flow

1. Parent opens an invoice (`GET invoices/{id}`).
2. Parent pays outside the app (bank, JazzCash, or EasyPaisa) using the details from `payment-methods`.
3. Parent uploads the payment screenshot (`POST invoices/{id}/pay/screenshot`).
4. Status becomes `not_received` until admin checks it.
5. Admin marks it received on the web panel. The invoice becomes `paid` and the invoice and receipt PDFs are emailed.
6. App can check status with `GET invoices/{id}/pay/status`.

## Removed

| Endpoint | Replace with |
|---|---|
| `POST invoices/{id}/pay/jazzcash` | `POST invoices/{id}/pay/screenshot` with `method=jazzcash` |
| `POST invoices/{id}/pay/easypaisa` | `POST invoices/{id}/pay/screenshot` with `method=easypaisa` |

## New endpoints

### POST `invoices/{id}/pay/screenshot`

Body: `multipart/form-data`

| Field | Required | Notes |
|---|---|---|
| `proof` | yes | Image file, max 5 MB |
| `method` | no | `bank_transfer`, `jazzcash`, `easypaisa`, `manual` (default `manual`) |
| `reference` | no | Transaction ID, max 100 chars |
| `notes` | no | Max 500 chars |

Sending again while status is `not_received` replaces the previous screenshot.

Response `200`:

```json
{
  "success": true,
  "message": "Screenshot sent. Status is not received until admin confirms.",
  "data": {
    "receipt_status": "not_received",
    "receipt_status_label": "Not received",
    "paid": false,
    "payment": { "...": "payment object" },
    "invoice": { "...": "invoice object" }
  }
}
```

Errors `422`: validation failed, invoice already paid or cancelled, or screenshot already marked received.

### GET `invoices/{id}/pay/status`

```json
{
  "success": true,
  "data": {
    "receipt_status": "received",
    "receipt_status_label": "Received",
    "paid": true,
    "status": "paid",
    "invoice": { "...": "invoice object" }
  }
}
```

## Changed endpoints

### POST `invoices/{id}/pay/bank`

- `proof` is now **required** (was optional).
- `reference` is now **optional** (was required).
- Response now has the same shape as `pay/screenshot`.

### GET `payment-methods`

Removed: `stripe_enabled`, `jazzcash_enabled`, `easypaisa_enabled`.

Added:

```json
{
  "currency": "PKR",
  "screenshot": {
    "id": "screenshot",
    "enabled": true,
    "label": "Payment screenshot",
    "fields": [
      { "key": "proof", "label": "Payment screenshot", "required": true },
      { "key": "method", "label": "How you paid", "required": false, "options": ["bank_transfer", "jazzcash", "easypaisa", "manual"] },
      { "key": "reference", "label": "Transaction reference", "required": false },
      { "key": "notes", "label": "Note", "required": false }
    ],
    "statuses": [
      { "key": "pending", "label": "Pending", "meaning": "Screenshot has not been sent yet." },
      { "key": "not_received", "label": "Not received", "meaning": "Screenshot sent. Admin has not confirmed it yet." },
      { "key": "received", "label": "Received", "meaning": "Admin confirmed the payment was received." }
    ],
    "hint": "Pay by bank, JazzCash, or EasyPaisa, then upload the screenshot."
  }
}
```

`bank`, `banks`, `company`, `pickup_otp_enabled`, and `cancel` are unchanged.

### Invoice object (`invoices`, `invoices/{id}`, and inside other responses)

- Removed: `stripe_enabled`.
- Added: `receipt_status`, `receipt_status_label`.
- `bank` is now always returned (was `null` once the invoice was not payable).
- `payable` is now `false` once a screenshot is sent or received. For the upload button, use `receipt_status` (see table below), because re-upload is still allowed while `not_received`.

### Payment object (inside `invoice.payments[]` and `payment`)

- Added: `receipt_status`, `receipt_status_label`.
- `method` can now also be `jazzcash`, `easypaisa`, or `wallet`.

## Receipt status values

| Value | Show to user | UI |
|---|---|---|
| `pending` | Pending | Show "Upload screenshot" button |
| `not_received` | Not received | Show "Waiting for confirmation", allow re-upload |
| `received` | Received | Show "Paid", hide upload |

## Postman

`postman/Pick-Drop-Mobile-APIs.postman_collection.json` already has **Invoices - Pay Screenshot** and **Invoices - Payment Status**.
