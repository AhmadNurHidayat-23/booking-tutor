<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Slot;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_status_helpers_and_scopes(): void
    {
        $student = User::factory()->create();
        $tutor = Tutor::create([
            'name' => 'Dr. Hendra Wijaya',
            'subject' => 'Matematika',
            'level' => 'SMA',
            'headline' => 'Guru Matematika Berpengalaman',
            'bio' => 'Mengajar kalkulus dan aljabar',
            'hourly_rate' => 150000,
            'mode' => 'Online',
            'city' => 'Bandung',
            'active' => true,
        ]);

        $slot = Slot::create([
            'tutor_id' => $tutor->id,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHour(),
            'duration' => 60,
            'active' => true,
        ]);

        $booking = Booking::create([
            'number' => 'BKG-001',
            'user_id' => $student->id,
            'slot_id' => $slot->id,
            'active_slot_id' => $slot->id,
            'hourly_rate' => $tutor->hourly_rate,
            'fee' => $slot->fee,
            'status' => 'pending',
            'topic' => 'Kalkulus Dasar',
        ]);

        $this->assertTrue($booking->isPending());
        $this->assertFalse($booking->isConfirmed());

        $booking->status = 'confirmed';
        $booking->save();
        $this->assertTrue($booking->isConfirmed());

        $this->assertCount(1, Tutor::active()->get());
        $this->assertCount(1, Slot::active()->get());
    }
}
