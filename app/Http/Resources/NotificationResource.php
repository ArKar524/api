<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payload = is_array($this->data) ? $this->data : [];

        return [
            'id' => $this->id,
            'type' => $payload['action'] ?? $this->type,
            'title' => $payload['title'] ?? null,
            'message' => $payload['message'] ?? null,
            'status' => $payload['status'] ?? null,
            'data' => $payload['data'] ?? null,
            'read_at' => $this->read_at?->toJSON(),
            'created_at' => $this->created_at?->toJSON(),
        ];
    }
}
