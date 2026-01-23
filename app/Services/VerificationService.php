<?php

namespace App\Services;

use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationFile;
use App\Notifications\ActionNotification;
use App\Traits\HandlesFileUploads;
use Illuminate\Support\Facades\DB;

class VerificationService
{
    use HandlesFileUploads;

    public function submitOwnerKyc(User $user, array $data): Verification
    {
        return DB::transaction(function () use ($user, $data) {
            $verification = new Verification();
            $verification->user_id = $user->id;
            $verification->entity_type = 'owner';
            $verification->status = 'pending';
            $verification->save();

            $dir = "verifications/{$verification->id}";

            $this->storeVerificationFile($verification, $data['nrc_front'], 'identity', $dir, 1, 'nrc_front');
            $this->storeVerificationFile($verification, $data['nrc_back'], 'identity', $dir, 2, 'nrc_back');
            $this->storeVerificationFile($verification, $data['selfie'], 'identity', $dir, 3, 'selfie');

            if (!empty($data['other_files']) && is_array($data['other_files'])) {
                $order = 10;
                foreach ($data['other_files'] as $file) {
                    $this->storeVerificationFile($verification, $file, 'other', $dir, $order++, 'other');
                }
            }

            $user->notify(new ActionNotification(
                'KYC submitted',
                'Your owner verification has been submitted for review.',
                'kyc.submitted',
                'pending',
                [
                    'verification_id' => $verification->id,
                    'entity_type' => 'owner',
                ],
            ));

            return $verification->load('files');
        });
    }

    public function submitDriverKyc(User $user, array $data): Verification
    {
        return DB::transaction(function () use ($user, $data) {
            $verification = new Verification();
            $verification->user_id = $user->id;
            $verification->entity_type = 'driver';
            $verification->status = 'pending';
            $verification->save();

            $dir = "verifications/{$verification->id}";

            $this->storeVerificationFile($verification, $data['license_front'], 'identity', $dir, 1, 'license_front');
            $this->storeVerificationFile($verification, $data['license_back'], 'identity', $dir, 2, 'license_back');
            $this->storeVerificationFile($verification, $data['nrc_front'], 'identity', $dir, 3, 'nrc_front');
            $this->storeVerificationFile($verification, $data['nrc_back'], 'identity', $dir, 4, 'nrc_back');
            $this->storeVerificationFile($verification, $data['selfie'], 'identity', $dir, 5, 'selfie');

            if (!empty($data['other_files']) && is_array($data['other_files'])) {
                $order = 10;
                foreach ($data['other_files'] as $file) {
                    $this->storeVerificationFile($verification, $file, 'other', $dir, $order++, 'other');
                }
            }

            $user->notify(new ActionNotification(
                'KYC submitted',
                'Your driver verification has been submitted for review.',
                'kyc.submitted',
                'pending',
                [
                    'verification_id' => $verification->id,
                    'entity_type' => 'driver',
                ],
            ));

            return $verification->load('files');
        });
    }

    public function reviewVerification(Verification $verification, string $status, ?string $notes = null): Verification
    {
        return DB::transaction(function () use ($verification, $status, $notes) {
            $verification->status = $status;
            $verification->notes = $notes;
            $verification->completed_at = now();
            $verification->save();

            $verification->user()->first()?->notify(new ActionNotification(
                'KYC reviewed',
                $status === 'approved' ? 'Your KYC has been approved.' : 'Your KYC has been rejected.',
                'kyc.reviewed',
                $status,
                [
                    'verification_id' => $verification->id,
                    'entity_type' => $verification->entity_type,
                    'notes' => $notes,
                ],
            ));

            return $verification->fresh('files', 'user');
        });
    }

    private function storeVerificationFile(
        Verification $verification,
        $file,
        string $category,
        string $dir,
        int $sortOrder,
        string $label
    ): void {
        $upload = $this->storeUploadedFile($file, 'public', $dir);

        VerificationFile::create([
            'verification_id' => $verification->id,
            'category' => $category,
            'file_path' => $upload['path'],
            'disk' => $upload['disk'],
            'mime_type' => $upload['mime'],
            'size' => $upload['size'],
            'meta' => [
                'original' => $upload['original'],
                'label' => $label,
            ],
            'sort_order' => $sortOrder,
        ]);
    }
}
