# Pick-Drop — Testing Guide

Date: 8 October 2026

This document is for QA. It explains what the product does, who uses it, how the pieces connect, and what to test. Use it with the Postman collection:

`postman/Pick-Drop-Mobile-APIs.postman_collection.json`

Payment-only notes are also in `postman/PAYMENT-API-CHANGES.md`.

---

## 1. What this product is

Pick-Drop is a school and commute transport system. A parent books a daily pick-up and drop for a child. A self user books the same kind of ride for themselves. A driver accepts the booking and runs the daily trip. An admin runs the web panel: approvals, assignment, payments, complaints, and emergencies.

There are four surfaces:

| Surface | Who | How to test |
|---|---|---|
| Admin web panel | Admin staff | Browser |
| Driver app | Driver | Mobile app, or Postman against `/api/driver` |
| Parent app | Parent of a student | Mobile app, or Postman against `/api/parent` |
| Self app | Person riding for themselves | Mobile app, or Postman against `/api/self` |

Parent and Self use the same API shape. Only the prefix changes (`/api/parent` or `/api/self`). A parent token must not work on `/api/self`, and a self token must not work on `/api/parent`. A driver token must not work on either.

---

## 2. Access

Ask the project owner for the live URL, admin login, and test accounts before you start. Do not invent production passwords.

Typical local admin URL, when the project runs under XAMPP:

`http://localhost/Pick-Drop/public`

API base:

`http://localhost/Pick-Drop/public/api`

Health check (no login):

`GET /api/ping` → `{ "status": "ok" }`

Every protected API call needs:

`Authorization: Bearer <token>`

`Accept: application/json`

File uploads (KYC, vehicle photos, payment screenshot) use `multipart/form-data`. Image files must be 5 MB or smaller.

Login returns a token. Use that token until logout. After logout the same token must be rejected.

---

## 3. Accounts and the order they must follow

A new user cannot book or drive until admin approves them. Test this order. Skipping a step should fail with a clear error.

### Driver

1. `POST /api/driver/register` — name, phone, email, password. Referral code is optional.
2. `POST /api/driver/verify-email` — OTP. Until email is verified, later steps stay blocked.
3. `POST /api/driver/verification` — driver KYC. Required: date of birth, address, city, at least one service area, CNIC front, CNIC back, selfie, licence number, licence front, licence back, licence expiry (must be a future date), terms accepted.
4. Admin opens **People → Driver KYC**, reviews documents, and approves or rejects.
5. `POST /api/driver/vehicle-verification` — vehicle documents. See section 7 for the exact fields.
6. Admin opens **People → Vehicle Verification** and approves or rejects.
7. Driver can go on duty (`PUT /api/driver/me/duty`) and see available requests.

Expected blocks:

- Vehicle form before email verification → 403.
- Vehicle form before KYC approval → 403, message says complete driver KYC first.
- Second submit while the current one is pending → 422.
- Submit again after approval → 422.

### Parent

1. `POST /api/parent/register`
2. Verify email.
3. `POST /api/parent/verification` — date of birth, address, city, CNIC front, CNIC back, selfie, terms accepted.
4. Admin approves under **People → Parent / Self KYC**.
5. Add a student: `POST /api/parent/students`.
6. Create a pick-up request. `student_id` is required. `type` must be `parent`.

### Self

Same as parent, on `/api/self`, with two differences:

- No students. The rider is the account itself.
- A commute profile exists: `GET/POST /api/self/commute-profile`.
- Request `type` must be `self`.

### Forgot password

`POST /api/driver/forgot-password`, `/api/parent/forgot-password`, and `/api/self/forgot-password` are separate.

- A driver email on the parent or self forgot-password API must not receive a reset mail.
- A parent email on the driver forgot-password API must not receive a reset mail.
- An unknown email must not reveal that some other role owns it.

---

## 4. Admin panel map

Log in on the web panel. The left menu is the test map.

| Menu | What to check |
|---|---|
| Dashboard | Counts load, no blank error page |
| Reports | Filters and export download a file |
| People → Users | Create, edit, disable a user |
| People → Login logs | A login appears here |
| People → Driver KYC | Pending list, open documents, approve, reject |
| People → Parent / Self KYC | Same for parent and self |
| People → Vehicle Verification | Photos open, approve, reject. Owner document photos are not a separate required pair |
| Locations → Cities / Areas / Points | Add, edit, delete. A city used by a request should not vanish silently |
| Vehicles → List / Categories | Category appears in the driver vehicle form |
| Operations → Pickup Requests | New app request shows here. Assign a driver. Mark driver payout |
| Operations → Live fleet | Driver location updates after the app sends location |
| Operations → Routes | Create a route and stops |
| Operations → Institutions | Schools list is what the parent app loads from `GET /api/parent/schools` |
| Complaints | Driver or parent issue appears. Change status |
| SOS Alerts | Driver or parent SOS appears. Acknowledge, resolve, and **End trip** |
| Calendar | A holiday blocks or skips that date for rides |
| Payments | Invoice, screenshot receipt, mark received, reject, refund, print |
| Pick-Drop Charges | Charge used when a request is priced |
| Driver Payments | Payroll request from the driver app shows here |
| Settings | Platform settings save and stay after refresh |

---

## 5. Main journey (test this first)

Run this once with a parent and once with a self user. This is the path that must work before anything else is signed off.

1. Admin has at least one city, one area, one vehicle category, and one charge.
2. Parent or self registers, verifies email, submits KYC.
3. Admin approves KYC.
4. Parent adds a student. Self skips this.
5. Parent or self creates a request with both a pick-up point and a drop point, times, and weekdays.
6. The request appears in the app list and in **Admin → Pickup Requests**.
7. An approved driver who is on duty sees it in `GET /api/driver/requests/available`.
8. Driver accepts it. Parent sees the driver on `GET /api/parent/requests/{id}/driver`.
9. On the ride day, driver opens `GET /api/driver/rides/today`.
10. Driver starts the ride: `POST /api/driver/rides/{pickup_request_id}/start`.
11. Driver sends location: `POST /api/driver/location/update`. Parent tracking (`GET .../tracking`) shows the new point.
12. Driver marks pick-up, then drop.
13. `GET /api/driver/rides/today` shows `trip.can_end = true`.
14. Driver ends the trip: `POST /api/driver/rides/end`.
15. Parent rates the driver. Driver can rate the request.
16. Admin creates or sends the invoice. Parent pays by screenshot. Admin marks the receipt received. Invoice becomes paid.

If step 13 stays false, do not force the end call. Read `trip.end_blocked_reason` and `trip.pending_passengers`. Those names are the passengers still not dropped and not marked not-going.

---

## 6. Booking rules

Create with `POST /api/parent/requests` or `POST /api/self/requests`.

`service_type` is one of:

| Value | Meaning |
|---|---|
| `both` | Morning pick-up and afternoon drop. Default |
| `pickup_only` | Pick-up only |
| `drop_only` | Drop only |

`trip_mode` is `round_trip` or `one_way`.

For a normal booking (no `stops` array):

- `type` is required and must match the app (`parent` or `self`).
- `city_id` is required.
- Pick-up point, pick-up lat/lng, drop point, and drop lat/lng are required.
- Both locations are required even when the service is pick-up only or drop only.
- `days` is a non-empty list of weekdays.
- Pick-up time (`H:i`, example `07:30`) is required unless the service is drop only.
- Drop time is required unless the service is pick-up only.
- Parent requests need `student_id` that belongs to that parent.

Optional multi-stop body: `stops[]` with `type` (`pickup` or `drop`), `point`, `lat`, `lng`. Max 20 stops.

After create, also test:

- List and open the request.
- Edit it before a driver is locked in.
- Cancel it. Call cancellation preview first: `GET .../cancellation-preview`.
- Renew, turn auto-renew on and off, and decline renewal.
- Skip a day (`POST .../attendance/skip`) and undo it.
- A skipped day must not appear as a normal ride for the driver.

---

## 7. Vehicle verification (changed)

`GET /api/driver/vehicle-verification` returns `vehicle_categories` (active categories: id, name, passenger capacity). The app must build the category dropdown from this list.

`POST /api/driver/vehicle-verification` required fields:

| Field | Rule |
|---|---|
| `vehicle_name` | Required |
| `license_plate` | Required, unique |
| `registration_card_front` | Required image |
| `registration_card_back` | Required image |
| `vehicle_front_photo` | Required image |
| `vehicle_back_photo` | Required image |
| `number_plate_photo` | Required image |
| `owner_name` | Required |
| `vehicle_category_id` | Optional, must be a real category id |
| `vehicle_model` | Optional |
| `vehicle_color` | Optional |
| `owner_cnic_number` | Optional |

These two fields are **removed**. Sending them is unnecessary. Omitting them must succeed:

- `owner_document_front`
- `owner_document_back`

Test cases:

| ID | Action | Expected |
|---|---|---|
| VV-1 | Submit with registration card, vehicle photos, number plate, owner name. No owner document photos | 201, status pending |
| VV-2 | Submit with no registration card front | 422, that field is required |
| VV-3 | Open admin vehicle verification detail | Registration and vehicle photos show. Missing owner-document photos do not block approval |
| VV-4 | Approve | Driver `next_step` moves forward and onboarding can complete |
| VV-5 | Reject | Driver can submit again |
| VV-6 | Duplicate number plate already used by another driver | 422 |

---

## 8. Daily trip, end trip, not going, emergency (new)

These are the newest driver rules. Test them on a day that has at least two accepted passengers when you can.

### Today screen

`GET /api/driver/rides/today`

Each passenger includes:

- `passenger_status`: `waiting`, `picked`, `dropped`, or `not_going`
- `not_going_reason`
- `can_mark_not_going` (true only after the trip has started, and only if that passenger is not already picked or already marked not going)

Trip object includes:

- `can_end`
- `end_blocked_reason`
- `emergency_open`
- `pending_passengers` (names still not settled)
- counts: total, picked, dropped, not going

Show the End Trip button only when `can_end` is true.

### Start and finish a passenger

| Call | When |
|---|---|
| `POST /api/driver/rides/{id}/start` | Start that passenger's day |
| `POST /api/driver/rides/{id}/arrive` | Driver reached the point |
| `POST /api/driver/rides/{ride}/pickup` | Passenger is in the vehicle |
| `POST /api/driver/rides/{ride}/drop` | Passenger is dropped |

### End trip

`POST /api/driver/rides/end` with an empty body.

| ID | Setup | Expected |
|---|---|---|
| TR-1 | Trip not started | 422, start the trip first. `can_end` is false |
| TR-2 | Started, one passenger still waiting | 422. Message names that passenger. `pending_passengers` lists them |
| TR-3 | Every passenger dropped | 200. `can_end` becomes false and `ended` is true. A second end call fails |
| TR-4 | One passenger dropped, one marked not going | 200. Not-going does not block the end |
| TR-5 | No passengers today | 422 |

### Passenger does not want to go

`POST /api/driver/rides/{pickup_request_id}/not-going`

Body:

```json
{ "reason": "Does not want to go today" }
```

`reason` is required, max 500 characters.

| ID | Setup | Expected |
|---|---|---|
| NG-1 | Trip not started | 422 |
| NG-2 | Trip started, passenger not picked yet | 200. Status `not_going`. Reason saved. Parent gets a notification. Admin panel gets a warning |
| NG-3 | Passenger already picked up | 422. Driver must drop them instead |
| NG-4 | Empty reason | 422 |
| NG-5 | After NG-2, call end when everyone else is dropped | End succeeds |

### Emergency

`POST /api/driver/sos`

| Field | Rule |
|---|---|
| `type` | Optional. One of `accident`, `medical`, `breakdown`, `security` |
| `message` | Optional, max 500 |
| `lat`, `lng` | Optional. If omitted, last driver location is used |
| `pickup_request_id` | Optional. Must belong to this driver |

| ID | Action | Expected |
|---|---|---|
| EM-1 | Send SOS with `type=accident` during an active trip | 200. Alert is on **Admin → SOS Alerts**. Type is visible |
| EM-2 | Driver calls `POST /api/driver/rides/end` while that SOS is still open or acknowledged | 422. `can_end` is false. Reason: admin will end this trip |
| EM-3 | Admin opens the SOS and clicks **End trip** | Trip ends. SOS becomes resolved. Driver can no longer end it themselves |
| EM-4 | Admin only acknowledges, does not end | Driver still cannot end |
| EM-5 | Admin resolves without ending, if the button exists separately | Confirm whether the trip is still locked. Open SOS and acknowledged SOS both lock the driver. A resolved SOS from a previous action should not keep blocking a new day |
| EM-6 | SOS `type` value outside the four names, example `fire` | 422 |
| EM-7 | Parent SOS `POST /api/parent/sos` | Appears in admin SOS list. This does not use the driver end-trip lock |

---

## 9. Payments

Full field notes: `postman/PAYMENT-API-CHANGES.md`.

Parent and self:

1. `GET /api/parent/payment-methods` — bank, JazzCash, and EasyPaisa details the app should show.
2. `GET /api/parent/invoices` and `GET /api/parent/invoices/{id}`.
3. Pay outside the app, then `POST /api/parent/invoices/{id}/pay/screenshot`.
   - `proof` image is required.
   - `method` optional: `bank_transfer`, `jazzcash`, `easypaisa`, `manual`.
   - `reference` and `notes` optional.
4. Response status is `not_received`. Invoice is not paid yet.
5. Admin opens **Payments**, reviews the screenshot, and marks it received.
6. App calls `GET /api/parent/invoices/{id}/pay/status`. `receipt_status` is `received`, `paid` is true.

Also test:

| ID | Action | Expected |
|---|---|---|
| PY-1 | Screenshot on an already paid invoice | 422 |
| PY-2 | Second screenshot while the first is still `not_received` | Replaces the first screenshot |
| PY-3 | Admin rejects the bank receipt | App status is not paid. User can send a new screenshot |
| PY-4 | `POST .../pay/bank` without `proof` | 422. Proof is required. Reference is optional |
| PY-5 | Old JazzCash or EasyPaisa pay URLs | Those direct pay URLs are removed. Use screenshot with `method=jazzcash` or `method=easypaisa` |

---

## 10. Other driver checks

| Area | Call | Expected |
|---|---|---|
| Duty | `PUT /api/driver/me/duty` | Off-duty driver does not receive new available requests |
| Available / accepted | `GET /api/driver/requests/available` and `/accepted` | Accepted request leaves the available list |
| Accept / reject | `POST .../accept` and `.../reject` | Second driver cannot accept the same request |
| Cover | `POST .../unavailable`, then another driver `GET /api/driver/requests/cover` and `POST /api/driver/cover/{id}/accept` | Original driver is replaced for that cover. Parent sees the replacement |
| Live status | `POST /api/driver/status/update` | Parent today-status changes |
| Issue | `POST /api/driver/issues` | Shows under Admin → Complaints and on the parent issues list |
| Chat | Messages on a request, both sides | Only the assigned pair can read that thread |
| Earnings / payroll | `GET /api/driver/earnings`, `POST /api/driver/payroll/request` | Request shows under Admin → Driver Payments |
| Notifications | List, mark one read, mark all read | Unread count drops |
| Change password | Wrong current password | 422, password unchanged |
| Delete account | `DELETE /api/driver/account` | Token stops working |

---

## 11. Other parent and self checks

| Area | What to confirm |
|---|---|
| Profile | Name and phone update, then `GET /me` returns the new values |
| Students | Parent can add, edit, delete. Self has no students API |
| Tracking | Map point matches the last driver location call |
| OTP | `GET .../otp` returns today's code only for that user's request |
| Contact | Phone shown only after a driver is assigned |
| Ratings | One rating per completed request. A second rating should fail or update, not create a duplicate silently — record which one the API does |
| Holidays | Date on the admin calendar is returned by `GET /api/parent/holidays` |
| Wallet | Balance on `GET /api/parent/wallet` matches admin after a payment |
| Notifications preferences | Turning a type off stops that push. In-app list behaviour should be noted in the bug if it still arrives |
| Issues | Parent complaint appears in Admin → Complaints |

---

## 12. API list

Replace `{id}` with the real id from the previous response. All of these except register, login, forgot-password, reset-password, verify-email, resend OTP, cities, and `/api/ping` need a Bearer token.

### Driver `/api/driver`

| Method | Path |
|---|---|
| POST | `/register` `/login` `/forgot-password` `/reset-password` `/verify-email` `/resend/otp` `/logout` |
| GET | `/cities` `/cities/{city}/areas` |
| GET, POST | `/verification` |
| GET, POST | `/vehicle-verification` |
| GET, PUT | `/me` |
| PUT | `/me/duty` `/account/change-password` `/account/payment-details` |
| GET | `/account/payment-details` |
| DELETE | `/account` |
| GET, POST | `/service-areas` |
| GET | `/requests/available` `/requests/accepted` `/requests/cover` |
| POST | `/requests/{id}/accept` `/requests/{id}/reject` `/requests/{id}/status` `/requests/{id}/unavailable` |
| GET, POST | `/requests/{id}/messages` |
| GET | `/requests/{id}/contact` |
| POST | `/requests/{id}/ratings` |
| GET | `/rides/today` `/rides` |
| POST | `/rides/end` `/rides/{id}/start` `/rides/{id}/arrive` `/rides/{id}/not-going` `/rides/{id}/pickup` `/rides/{id}/drop` |
| POST | `/location/update` `/status/update` `/issues` `/sos` |
| GET | `/issues/today` `/notifications` `/holidays` `/earnings` `/payroll` `/payroll/{id}` `/payrolls` `/payrolls/{id}` |
| POST | `/notifications/mark-all-read` `/notifications/{id}/read` `/phone/verify/send` `/phone/verify` |
| POST | `/payroll/request` `/payroll/preview` `/cover/{id}/accept` `/device-token` |

### Parent `/api/parent` and Self `/api/self`

Same paths on both prefixes, except students and commute profile.

| Method | Path | Who |
|---|---|---|
| POST | `/register` `/login` `/forgot-password` `/reset-password` `/logout` | Both |
| POST | `/email/verify/send` `/email/verify` `/phone/verify/send` `/phone/verify` | Both |
| GET, POST | `/verification` | Both |
| GET, POST | `/commute-profile` | Self only |
| GET, POST, PUT, DELETE | `/students` and `/students/{id}` | Parent only |
| GET, PUT | `/me` | Both |
| PUT | `/account/change-password` | Both |
| DELETE | `/account` | Both |
| GET | `/cities` `/cities/{city}/areas` `/cities/{city}/points` `/schools` `/wallet` `/holidays` `/payment-methods` | Both |
| GET, POST | `/requests` | Both |
| GET, PUT, DELETE | `/requests/{id}` | Both |
| GET | `/requests/{id}/driver` `/tracking` `/replacements` `/attendance` `/ratings` `/contact` `/cancellation-preview` `/otp` | Both |
| POST | `/requests/{id}/renew` `/auto-renew` `/decline-renewal` `/attendance/skip` `/ratings` | Both |
| DELETE | `/requests/{id}/attendance/skip` | Both |
| GET | `/invoices` `/invoices/{id}` `/invoices/{id}/pay/status` | Both |
| POST | `/invoices/{id}/pay/stripe` `/pay/bank` `/pay/screenshot` | Both |
| GET | `/trips/recent` `/trips/today-status` `/issues` `/notifications` `/notification-preferences` `/sos` | Both |
| POST | `/issues` `/sos` `/device-token` `/notifications/mark-all-read` `/notifications/{id}/read` | Both |
| GET, PUT | `/notification-preferences` | Both |
| GET, POST | `/requests/{id}/messages` | Both |

---

## 13. Cross-checks (easy to miss)

- Parent token on `/api/driver/...` returns 401 or 403.
- Driver token on `/api/parent/...` returns 401 or 403.
- Parent token on `/api/self/...` returns an account-type error.
- Request `type=self` from the parent app is rejected.
- Two drivers accepting the same request: only the first succeeds.
- Ending a trip with one child still in the vehicle is rejected.
- SOS during a trip blocks the driver end button until admin ends it.
- Owner document front and back are not required.
- Payment screenshot does not mark the invoice paid until admin confirms.
- Holiday date does not generate a normal ride.
- After logout, the old token fails.

---

## 14. How to report a bug

Include all of these:

1. Surface: Admin, Driver app, Parent app, or Self app.
2. Account email and role used.
3. Steps, numbered.
4. Request method and path if it is an API bug.
5. Response status code and the `message` field.
6. Expected result, from this guide.
7. Actual result.
8. Screenshot or response body.
9. Time of the test.

Severity:

| Level | Meaning |
|---|---|
| Blocker | Register, login, booking, accept, pay, or trip end cannot be completed |
| Major | A rule in sections 7, 8, or 9 fails |
| Minor | Wrong text, layout, or a field that is saved but displayed incorrectly |
| Note | Unclear message. Not a functional break |
