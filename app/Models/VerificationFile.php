<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationFile extends Model
{
    public function verification(): BelongsTo
    {
        return $this->belongsTo(Verification::class);
    }
}
