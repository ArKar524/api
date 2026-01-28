# Kargate Mobile API Contract (2026-01-23)

Base path: `/api` (e.g. `https://<host>/api`). Authenticated calls use Bearer tokens issued by Laravel Sanctum.

## Response envelope
- All responses follow: `{ "success": bool, "data": <payload|null>, "message": string, "errors": object|null }`.
- Paginated lists use `data.items` plus `data.meta` (`current_page`, `last_page`, `per_page`, `total`, `from`, `to`, `links.next`, `links.prev`).

## Data models (columns returned unless noted)
- **User**: `id`, `name`, `email`, `phone`, `role`, `status`, `timezone`, `email_verified_at`, `created_at`, `updated_at` (password & remember_token are hidden).
- **Car**: `id`, `owner_id`, `title`, `make`, `model`, `year`, `license_plate`, `status`, `approval_status`, `daily_rate`, `deposit_amount`, `currency`, `description`, `pickup_latitude`, `pickup_longitude`, `approved_by`, `approved_at`, `created_at`, `updated_at`.
- **CarPhoto**: `id`, `car_id`, `path`, `disk`, `type`, `mime_type`, `size`, `caption`, `sequence`, `created_at`, `updated_at`.
- **CarDocument**: `id`, `car_id`, `doc_type`, `file_path`, `disk`, `mime_type`, `size`, `expires_at`, `verified_at`, `created_at`, `updated_at`.
- **RentalRequest**: `id`, `car_id`, `driver_id`, `owner_id`, `status`, `start_date`, `end_date`, `daily_rate`, `currency`, `notes`, `created_at`, `updated_at`.
- **Rental**: `id`, `rental_request_id`, `car_id`, `driver_id`, `owner_id`, `status`, `total_amount`, `currency`, `start_at`, `end_at`, `pickup_location`, `dropoff_location`, `contract_terms`, `created_at`, `updated_at`.
- **Verification**: `id`, `user_id`, `entity_type` (owner|driver), `status`, `requested_at`, `completed_at`, `notes`, `created_at`, `updated_at`.
- **VerificationFile**: `id`, `verification_id`, `category`, `file_path`, `disk`, `mime_type`, `size`, `meta` (`original`, `label`), `sort_order`, `uploaded_at`, `created_at`, `updated_at`.

## Query conventions (list endpoints)
- Pagination: `page` (min 1), `per_page` (1–100, default 15).
- Sorting: `sort=<field>` ascending, `sort=-<field>` descending. Only the whitelisted fields per endpoint are accepted; otherwise default sort is used.
- Search: `q=<term>` (applies a `LIKE` across the endpoint’s searchable columns).
- Filters: `filter[<key>]=<value>`; only allowed keys per endpoint are applied, null/empty values are ignored.

## Authentication
| Method | Path | Body | Notes |
| --- | --- | --- | --- |
| POST | `/auth/register` | `name`, `phone`, `email?`, `password`, `role? (owner|driver, default driver)`, `device_name?` | Returns `user` + `token` |
| POST | `/auth/login` | `login` (email or phone), `password`, `device_name?` | Returns `user` + `token` |
| POST | `/auth/logout` | — | Bearer token required; revokes current token |
| GET | `/auth/me` | — | Bearer token required; returns current `user` |

## Public
| Method | Path | Purpose | Sort | Search columns | Allowed filters (`filter[...]`) |
| --- | --- | --- | --- | --- | --- |
| GET | `/cars` | List only approved & active cars | `created_at`, `daily_rate` | `title`, `make`, `model`, `license_plate` | `status`, `make`, `model`, `year`, `price_min`, `price_max` |

Example:
```
GET /api/cars?page=1&per_page=10&sort=-daily_rate&q=hybrid&filter[make]=Toyota&filter[price_max]=80
```

## Owner scope (Bearer + role: owner)
| Method | Path | Purpose | Sort | Search columns | Allowed filters |
| --- | --- | --- | --- | --- | --- |
| GET | `/owner/cars` | List owner’s cars | `created_at` | `title`, `make`, `model`, `license_plate` | `status` |
| POST | `/owner/cars` | Create car (goes to review) | — | — | — |
| GET | `/owner/rental-requests` | Incoming rental requests | `created_at` | `notes` | `status`, `car_id`, `driver_id` |
| GET | `/owner/rentals` | Active/history rentals | `start_at`, `created_at` | `pickup_location`, `dropoff_location` | `status`, `car_id`, `driver_id`, `start_at_from`, `start_at_to` |
| POST | `/owner/kyc` | Submit owner KYC | — | — | — |

### POST /owner/cars (multipart/form-data)
- Required: `title`, `make`, `model`, `year` (1980–next year), `plate_number`, `daily_price`, `pickup_lat`, `pickup_lng`, `photos[]` (min 3 images).
- Optional: `deposit_amount`, `description`, `documents[]` (pdf/jpg/jpeg/png ≤8 MB), `doc_types[]` (aligned with documents).

### POST /owner/kyc (multipart/form-data)
- Required images ≤5 MB: `nrc_front`, `nrc_back`, `selfie`.
- Optional: `other_files[]` (≤8 MB each).

## Driver scope (Bearer + role: driver)
| Method | Path | Purpose | Sort | Search columns | Allowed filters |
| --- | --- | --- | --- | --- | --- |
| GET | `/driver/rental-requests` | Driver’s rental requests | `created_at` | `notes` | `status`, `car_id` |
| POST | `/driver/kyc` | Submit driver KYC | — | — | — |

### POST /driver/kyc (multipart/form-data)
- Required images ≤5 MB: `license_front`, `license_back`, `nrc_front`, `nrc_back`, `selfie`.
- Optional: `other_files[]` (≤8 MB each).

## Admin scope (Bearer + role: admin)
| Method | Path | Body |
| --- | --- | --- |
| POST | `/admin/verifications/{id}/review` | `status` (`approved` or `rejected`), `notes?` |
| POST | `/admin/cars/{id}/review` | `status` (`approved` or `rejected`), `notes?` (also sets car `status` to active/inactive) |

## Notes for mobile integration
- Content types: JSON for standard calls; multipart/form-data for uploads (car creation, KYC).
- Sorting defaults to latest creation when `sort` missing or invalid.
- Filters silently ignore unknown keys, so clients can safely send only the keys they use.
- Currency fields currently fixed to USD in car listings; rentals use MMK default in DB (check backend if displaying totals).

## Endpoint request & response shapes

### Auth
- **POST /auth/register** (JSON)
  - Request: `name` (string, required), `phone` (string, unique, required), `email` (email, optional), `password` (string ≥8), `role` (`owner|driver`, default `driver`), `device_name` (string, optional).
  - Response 201: `data.user` (User), `data.token` (string), `message: "Registered successfully."`.
- **POST /auth/login** (JSON)
  - Request: `login` (email or phone, required), `password` (required), `device_name?` (string).
  - Response 200: `data.user` (User), `data.token` (string), `message: "Logged in successfully."`; on 401 `success=false`, `errors.login`.
- **POST /auth/logout** (Bearer)
  - Request: none.
  - Response 200: `data: null`, `message: "Logged out successfully."`.
- **GET /auth/me** (Bearer)
  - Request: none.
  - Response 200: `data.user` (User), `message: "Profile fetched."`.

### Public
- **GET /cars**
  - Query: pagination + search + filters from table above (`status`, `make`, `model`, `year`, `price_min`, `price_max`); sort `created_at` or `daily_rate` with optional leading `-`.
  - Response 200 (paginated): `data.items[]` = Car rows (no relations); `data.meta` pagination block; `message: "Cars fetched successfully."`.

### Owner scope (Bearer role: owner)
- **GET /owner/cars**
  - Query: pagination + `sort=created_at` (or `-`), `filter[status]`, `q` on title/make/model/license_plate.
  - Response 200 (paginated): `data.items[]` = Car rows owned by caller; `message: "Owner cars fetched."`.
- **POST /owner/cars** (multipart/form-data)
  - Request: required `title`, `make`, `model`, `year` (1980–next year), `plate_number`, `daily_price`, `pickup_lat`, `pickup_lng`, `photos[]` (≥3 images). Optional `deposit_amount`, `description`, `documents[]` (pdf/jpg/jpeg/png ≤8 MB), `doc_types[]` (align with `documents`).
  - Response 201: `data` = Car with eager-loaded `photos[]` (CarPhoto) and `documents[]` (CarDocument); `message: "Car submitted for review."`.
- **GET /owner/rental-requests**
  - Query: pagination + `filter[status|car_id|driver_id]`, `q` on `notes`, sort `created_at`.
  - Response 200 (paginated): `data.items[]` = RentalRequest rows for owner; `message: "Owner rental requests fetched."`.
- **GET /owner/rentals**
  - Query: pagination + `filter[status|car_id|driver_id|start_at_from|start_at_to]`, `q` on pickup/dropoff, sort `start_at` or `created_at`.
  - Response 200 (paginated): `data.items[]` = Rental rows for owner; `message: "Owner rentals fetched."`.
- **POST /owner/kyc** (multipart/form-data)
  - Request: `nrc_front` (image ≤5 MB), `nrc_back` (image ≤5 MB), `selfie` (image ≤5 MB); optional `other_files[]` (≤8 MB each).
  - Response 201: `data` = Verification with `files[]` (VerificationFile); `message: "Owner KYC submitted successfully."`.

### Driver scope (Bearer role: driver)
- **GET /driver/rental-requests**
  - Query: pagination + `filter[status|car_id]`, `q` on `notes`, sort `created_at`.
  - Response 200 (paginated): `data.items[]` = RentalRequest rows for driver; `message: "Driver rental requests fetched."`.
- **POST /driver/kyc** (multipart/form-data)
  - Request: `license_front`, `license_back`, `nrc_front`, `nrc_back`, `selfie` (all images ≤5 MB); optional `other_files[]` (≤8 MB each).
  - Response 201: `data` = Verification with `files[]`; `message: "Driver KYC submitted successfully."`.

### Admin scope (Bearer role: admin)
- **POST /admin/verifications/{id}/review** (JSON)
  - Request: `status` (`approved`|`rejected`), `notes?` (string).
  - Response 200: `data` = Verification (with `files[]` + `user`) after update; `message: "Verification reviewed."`.
- **POST /admin/cars/{id}/review** (JSON)
  - Request: `status` (`approved`|`rejected`), `notes?` (string, appended to car description with "Review:").
  - Response 200: `data` = Car with `photos[]`, `documents[]`, `owner`; `message: "Car review submitted."` (car `status` set to active when approved, inactive when rejected).
