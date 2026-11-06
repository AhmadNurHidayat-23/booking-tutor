<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['fee' => 'integer', 'hourly_rate' => 'integer'];
    }

    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(BookingEvent::class);
    }
}
