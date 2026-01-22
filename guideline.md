ARCHITECTURE GUIDELINES (MUST FOLLOW):
A) Naming:
- Models: Singular (CarPhoto), Tables: plural (car_photos)
- FK columns: owner_id, driver_id, reviewed_by, approved_by, opened_by, resolved_by (all unsignedBigInteger)
- Use consistent status columns: status (string) and type (string) with defaults.

B) Indexing (MUST):
- Index all FKs automatically via constrained()
- Add indexes for: status columns, plate_number, vin, contract_no, recorded_at
- Add composite index for location_updates: (rental_id, recorded_at)

C) Data retention / audit:
- Use softDeletes() for: cars, rental_requests, rentals, payments, disputes
- Do NOT cascade delete rentals/payments/disputes (audit). Only cascade delete purely child assets:
  car_photos, car_documents, verification_files, rental_events, location_updates (safe)

D) Model design:
- Add status/type constants (or PHP enums if you choose) in each model.
- Use $guarded = ['id'].
- Add relationship methods exactly as specified and keep naming consistent.

E) Output format:
1) Output all artisan commands
2) Output migrations in correct dependency order
3) Output models with relationships + constants
4) Output a short schema map (tables + key relations)

F) No extras:
- No controllers, no services, no policies yet. Only schema + models. 
