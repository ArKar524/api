<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $verification = $this->resolveVerification();
        $status = $verification['status'] ?? null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'role' => $this->role,
            'email_verified_at' => $this->email_verified_at?->toJSON(),
            'kyc_status' => $status,
            'verification_status' => $status,
            'verification' => $verification,
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
            'owner_verifications' => $this->whenLoaded('ownerVerifications'),
            'latest_owner_verification' => $this->whenLoaded('latestOwnerVerification'),
            'latest_driver_verification' => $this->whenLoaded('latestDriverVerification'),
        ];
    }

    /**
     * @return array{status:string,notes: ?string}|null
     */
    private function resolveVerification(): ?array
    {
        $verification = null;

        if ($this->role === 'owner') {
            $verification = $this->relationLoaded('latestOwnerVerification')
                ? $this->latestOwnerVerification
                : $this->latestOwnerVerification()->first();
        } elseif ($this->role === 'driver') {
            $verification = $this->relationLoaded('latestDriverVerification')
                ? $this->latestDriverVerification
                : $this->latestDriverVerification()->first();
        }

        if (!$verification) {
            return null;
        }

        return [
            'status' => $verification->status,
            'notes' => $verification->notes,
        ];
    }
}
