<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'active' => 'boolean', 'duration' => 'integer'];
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getFeeAttribute(): int
    {
        return intdiv($this->tutor->hourly_rate * $this->duration + 30, 60);
    }
}
