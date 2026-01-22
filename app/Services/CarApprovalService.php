<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CarDocument;
use App\Models\CarPhoto;
use App\Models\User;
use App\Traits\HandlesFileUploads;
use Illuminate\Support\Facades\DB;

class CarApprovalService
{
    use HandlesFileUploads;

    public function createCar(User $owner, array $data): Car
    {
        return DB::transaction(function () use ($owner, $data) {
            $car = Car::create([
                'owner_id' => $owner->id,
                'title' => $data['title'] ?? null,
                'make' => $data['make'],
                'model' => $data['model'],
                'year' => $data['year'],
                'license_plate' => $data['plate_number'],
                'status' => 'pending_review',
                'approval_status' => 'pending',
                'daily_rate' => $data['daily_price'],
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'currency' => 'USD',
                'description' => $data['description'] ?? null,
                'pickup_latitude' => $data['pickup_lat'] ?? null,
                'pickup_longitude' => $data['pickup_lng'] ?? null,
            ]);

            $photoDir = "cars/{$car->id}/photos";
            foreach ($data['photos'] as $index => $photo) {
                $upload = $this->storeUploadedFile($photo, 'public', $photoDir);

                CarPhoto::create([
                    'car_id' => $car->id,
                    'path' => $upload['path'],
                    'disk' => $upload['disk'],
                    'mime_type' => $upload['mime'],
                    'size' => $upload['size'],
                    'type' => 'exterior',
                    'sequence' => $index,
                ]);
            }

            $documents = $data['documents'] ?? [];
            if (!empty($documents) && is_array($documents)) {
                $docDir = "cars/{$car->id}/documents";
                $docTypes = $data['doc_types'] ?? [];

                foreach ($documents as $idx => $document) {
                    $upload = $this->storeUploadedFile($document, 'public', $docDir);
                    $docType = $docTypes[$idx] ?? 'other';

                    CarDocument::create([
                        'car_id' => $car->id,
                        'doc_type' => $docType,
                        'file_path' => $upload['path'],
                        'disk' => $upload['disk'],
                        'mime_type' => $upload['mime'],
                        'size' => $upload['size'],
                    ]);
                }
            }

            return $car->load(['photos', 'documents']);
        });
    }

    public function reviewCar(Car $car, string $status, ?User $admin = null, ?string $notes = null): Car
    {
        return DB::transaction(function () use ($car, $status, $admin, $notes) {
            $car->approval_status = $status;
            $car->status = $status === 'approved' ? 'active' : 'inactive';
            $car->approved_by = $admin?->id;
            $car->approved_at = now();
            if ($notes) {
                $car->description = trim(($car->description ? $car->description . PHP_EOL : '') . 'Review: ' . $notes);
            }
            $car->save();

            return $car->fresh(['photos', 'documents', 'owner']);
        });
    }
}
