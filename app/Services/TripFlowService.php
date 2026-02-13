<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Trip;
use App\Models\TripApplication;
use App\Models\TripApplicationEvent;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TripFlowService
{
    public function createTrip(User $owner, array $data): Trip
    {
        $this->assertVerified($owner, 'owner');

        return Trip::create([
            'car_id' => $data['car_id'],
            'owner_id' => $owner->id,
            'driver_id' => null,
            'status' => 'pending',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'daily_rate' => $data['daily_rate'],
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'pickup_location' => $data['pickup_location'],
            'dropoff_location' => $data['dropoff_location'],
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function updateTrip(User $owner, Trip $trip, array $data): Trip
    {
        $this->assertVerified($owner, 'owner');

        if ((int) $trip->owner_id !== (int) $owner->id) {
            throw new BusinessRuleException(403, 'Forbidden.');
        }

        if ($trip->driver_id) {
            throw new BusinessRuleException(
                422,
                'Cannot update trip after a driver has applied.',
                ['trip' => ['Cannot update trip after a driver has applied.']],
            );
        }

        if ($trip->status !== 'pending') {
            throw new BusinessRuleException(
                422,
                'Only pending trips can be updated.',
                ['status' => ['Only pending trips can be updated.']],
            );
        }

        $startDate = $data['start_date'] ?? $trip->start_date;
        $endDate = $data['end_date'] ?? $trip->end_date;

        $this->assertDateRange($startDate, $endDate, 'end_date', 'start_date');

        $trip->fill([
            'car_id' => $data['car_id'] ?? $trip->car_id,
            'status' => $data['status'] ?? $trip->status,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'daily_rate' => $data['daily_rate'] ?? $trip->daily_rate,
            'currency' => array_key_exists('currency', $data)
                ? strtoupper((string) $data['currency'])
                : $trip->currency,
            'pickup_location' => $data['pickup_location'] ?? $trip->pickup_location,
            'dropoff_location' => $data['dropoff_location'] ?? $trip->dropoff_location,
            'notes' => $data['notes'] ?? $trip->notes,
        ]);
        $trip->save();

        return $trip->fresh();
    }

    public function apply(User $driver, Trip $trip): TripApplication
    {
        $this->assertVerified($driver, 'driver');

        if ((int) $trip->owner_id === (int) $driver->id) {
            throw new BusinessRuleException(
                422,
                'You cannot apply to your own trip.',
                ['trip' => ['You cannot apply to your own trip.']],
            );
        }

        $existingApplication = $trip->tripApplication()->first();
        if ($existingApplication) {
            if ((int) $existingApplication->driver_id === (int) $driver->id) {
                return $existingApplication->fresh();
            }

            throw new BusinessRuleException(
                409,
                'This trip has already been applied by another driver.',
                ['driver_id' => ['This trip has already been applied by another driver.']],
            );
        }

        if ($trip->status !== 'pending') {
            throw new BusinessRuleException(
                422,
                'This trip is not available for application.',
                ['status' => ['This trip is not available for application.']],
            );
        }

        if ($trip->driver_id && (int) $trip->driver_id !== (int) $driver->id) {
            throw new BusinessRuleException(
                409,
                'This trip has already been applied by another driver.',
                ['driver_id' => ['This trip has already been applied by another driver.']],
            );
        }

        $hasOtherActiveApplication = TripApplication::query()
            ->where('driver_id', $driver->id)
            ->where('status', 'active')
            ->where('trip_id', '!=', $trip->id)
            ->exists();

        if ($hasOtherActiveApplication) {
            throw new BusinessRuleException(
                422,
                'You can only apply to one trip at a time.',
                ['driver_id' => ['You can only apply to one trip at a time.']],
            );
        }

        return DB::transaction(function () use ($driver, $trip) {
            $trip->driver_id = $driver->id;
            $trip->status = 'approved';
            $trip->save();

            $startAt = Carbon::parse($trip->start_date)->startOfDay();
            $endAt = Carbon::parse($trip->end_date)->startOfDay();
            $days = max($startAt->copy()->diffInDays($endAt), 1);

            $tripApplication = TripApplication::create([
                'trip_id' => $trip->id,
                'car_id' => $trip->car_id,
                'driver_id' => $driver->id,
                'owner_id' => $trip->owner_id,
                'status' => 'active',
                'total_amount' => $days * max((float) $trip->daily_rate, 0),
                'currency' => $trip->currency ?: 'USD',
                'start_at' => $startAt->toDateTimeString(),
                'end_at' => $endAt->toDateTimeString(),
                'pickup_location' => $trip->pickup_location,
                'dropoff_location' => $trip->dropoff_location,
                'contract_terms' => null,
            ]);

            TripApplicationEvent::create([
                'trip_application_id' => $tripApplication->id,
                'type' => 'created',
                'notes' => 'Trip application submitted by driver.',
            ]);

            return $tripApplication->fresh();
        });
    }

    private function assertVerified(User $user, string $entityType): void
    {
        $isVerified = Verification::query()
            ->where('user_id', $user->id)
            ->where('entity_type', $entityType)
            ->where('status', 'approved')
            ->exists();

        if (!$isVerified) {
            $actor = $entityType === 'owner' ? 'Owner' : 'Driver';
            throw new BusinessRuleException(
                403,
                $actor . ' verification is required.',
                null,
            );
        }
    }

    private function assertDateRange(
        string $startDate,
        string $endDate,
        string $endField,
        string $startField
    ): void {
        if (Carbon::parse($endDate)->lessThanOrEqualTo(Carbon::parse($startDate))) {
            throw new BusinessRuleException(
                422,
                'Validation failed.',
                [$endField => ['The ' . $endField . ' must be a date after ' . $startField . '.']],
            );
        }
    }
}
