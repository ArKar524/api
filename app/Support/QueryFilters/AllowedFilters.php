<?php

namespace App\Support\QueryFilters;

class AllowedFilters
{
    public static function carPublic(): array
    {
        return [
            'status' => fn($q, $v) => $q->where('status', $v),
            'make' => fn($q, $v) => $q->where('make', $v),
            'model' => fn($q, $v) => $q->where('model', $v),
            'year' => fn($q, $v) => $q->where('year', $v),
            'price_min' => fn($q, $v) => $q->where('daily_rate', '>=', $v),
            'price_max' => fn($q, $v) => $q->where('daily_rate', '<=', $v),
        ];
    }

    public static function carOwner(): array
    {
        return [
            'status' => fn($q, $v) => $q->where('status', $v),
        ];
    }

    public static function tripDriver(): array
    {
        return [
            'status' => fn($q, $v) => $q->where('status', $v),
            'car_id' => fn($q, $v) => $q->where('car_id', $v),
        ];
    }

    public static function tripOwner(): array
    {
        return [
            'status' => fn($q, $v) => $q->where('status', $v),
            'car_id' => fn($q, $v) => $q->where('car_id', $v),
            'driver_id' => fn($q, $v) => $q->where('driver_id', $v),
        ];
    }

    public static function tripApplicationOwner(): array
    {
        return [
            'status' => fn($q, $v) => $q->where('status', $v),
            'car_id' => fn($q, $v) => $q->where('car_id', $v),
            'driver_id' => fn($q, $v) => $q->where('driver_id', $v),
            'start_at_from' => fn($q, $v) => $q->whereDate('start_at', '>=', $v),
            'start_at_to' => fn($q, $v) => $q->whereDate('start_at', '<=', $v),
        ];
    }
}
