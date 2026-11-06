<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Slot;
use App\Models\Tutor;
use App\Models\User;
use App\Services\SchedulingService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulingTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $staff;

    private Tutor $tutor;

    private Slot $slot;

    private SchedulingService $schedule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->student = User::factory()->create();
        $this->staff = User::factory()->create();
        $this->staff->forceFill(['role' => 'staff'])->save();
        $this->tutor = Tutor::create(['name' => 'Tutor Pengujian', 'subject' => 'Matematika', 'level' => 'SMP', 'headline' => 'Pendekatan bertahap untuk memahami konsep', 'bio' => 'Profil tutor khusus pengujian alur reservasi dan jadwal.', 'hourly_rate' => 100000, 'mode' => 'Online', 'city' => 'Bandung']);
        $this->schedule = new SchedulingService;
        $this->slot = $this->schedule->addSlot($this->tutor, ['starts_at' => now()->addDays(7)->format('Y-m-d H:i:s'), 'duration' => 90, 'meeting_details' => 'PETUNJUK-RAHASIA']);
    }

    private function book(): Booking
    {
        return $this->schedule->book($this->student, $this->slot, 'Latihan persamaan linear untuk ujian.');
    }

    public function test_booking_stores_real_slot_and_server_fee(): void
    {
        $this->actingAs($this->student)->post('/jadwal/'.$this->slot->id.'/pesan', ['topic' => 'Belajar persamaan dan latihan soal.', 'fee' => 1])->assertRedirect();
        $b = Booking::first();
        $this->assertSame(150000, $b->fee);
        $this->assertSame($this->slot->id, $b->active_slot_id);
    }

    public function test_second_student_cannot_book_same_slot(): void
    {
        $this->book();
        $this->actingAs(User::factory()->create())->post('/jadwal/'.$this->slot->id.'/pesan', ['topic' => 'Topik pelajaran untuk murid kedua.'])->assertSessionHasErrors('slot');
        $this->assertSame(1, Booking::count());
    }

    public function test_database_unique_constraint_rejects_two_active_bookings(): void
    {
        $b = $this->book();
        $this->expectException(QueryException::class);
        Booking::create(['number' => 'BT-DUPLICATE', 'user_id' => $this->staff->id, 'slot_id' => $this->slot->id, 'active_slot_id' => $this->slot->id, 'hourly_rate' => 100000, 'fee' => 150000, 'topic' => 'Pengujian constraint database.']);
    }

    public function test_overlapping_slots_are_rejected(): void
    {
        $this->actingAs($this->staff)->post('/petugas/tutor/'.$this->tutor->id.'/jadwal', ['starts_at' => $this->slot->starts_at->copy()->addMinutes(30)->format('Y-m-d\TH:i'), 'duration' => 60])->assertSessionHasErrors('starts_at');
        $this->assertSame(1, Slot::count());
    }

    public function test_adjacent_slots_are_allowed(): void
    {
        $s = $this->schedule->addSlot($this->tutor, ['starts_at' => $this->slot->ends_at->format('Y-m-d H:i:s'), 'duration' => 60]);
        $this->assertSame(2, Slot::count());
    }

    public function test_cancellation_releases_slot_without_erasing_history(): void
    {
        $b = $this->book();
        $this->schedule->change($this->student, $b, 'cancel', 'Kebutuhan jadwal berubah.');
        $this->assertNull($b->fresh()->active_slot_id);
        $this->assertSame('cancelled', $b->fresh()->status);
        $new = $this->schedule->book(User::factory()->create(), $this->slot, 'Topik untuk pelajar pengganti.');
        $this->assertNotSame($b->id, $new->id);
        $this->assertSame(2, Booking::count());
    }

    public function test_student_cannot_cancel_within_24_hours(): void
    {
        $b = $this->book();
        $this->travelTo($this->slot->starts_at->copy()->subHours(12));
        $this->actingAs($this->student)->patch('/reservasi/'.$b->id, ['action' => 'cancel', 'note' => 'Ingin ganti waktu'])->assertSessionHasErrors('booking');
        $this->assertSame('pending', $b->fresh()->status);
    }

    public function test_only_staff_can_confirm_and_completion_requires_session_end(): void
    {
        $b = $this->book();
        $this->actingAs($this->student)->patch('/reservasi/'.$b->id, ['action' => 'confirm', 'note' => 'Mencoba konfirmasi sendiri'])->assertSessionHasErrors('booking');
        $this->schedule->change($this->staff, $b, 'confirm', 'Tutor telah menyetujui sesi.');
        $this->actingAs($this->staff)->patch('/reservasi/'.$b->id, ['action' => 'complete', 'note' => 'Belum waktunya selesai'])->assertSessionHasErrors('booking');
        $this->travelTo($this->slot->ends_at->copy()->addMinute());
        $this->schedule->change($this->staff, $b, 'complete', 'Sesi dilaksanakan.');
        $this->assertSame('completed', $b->fresh()->status);
    }

    public function test_booked_slot_cannot_be_disabled(): void
    {
        $this->book();
        $this->actingAs($this->staff)->patch('/petugas/jadwal/'.$this->slot->id)->assertSessionHasErrors('slot');
        $this->assertTrue($this->slot->fresh()->active);
    }

    public function test_inactive_slot_and_tutor_cannot_be_booked(): void
    {
        $this->slot->update(['active' => false]);
        $this->actingAs($this->student)->post('/jadwal/'.$this->slot->id.'/pesan', ['topic' => 'Belajar topik matematika.'])->assertSessionHasErrors('slot');
        $this->slot->update(['active' => true]);
        $this->tutor->update(['active' => false]);
        $this->post('/jadwal/'.$this->slot->id.'/pesan', ['topic' => 'Belajar topik matematika.'])->assertSessionHasErrors('slot');
    }

    public function test_rate_changes_preserve_booking_snapshot(): void
    {
        $b = $this->book();
        $this->tutor->update(['hourly_rate' => 200000]);
        $this->assertSame(150000, $b->fresh()->fee);
        $this->assertSame(100000, $b->fresh()->hourly_rate);
    }

    public function test_public_calendar_never_exposes_private_instructions(): void
    {
        $this->get('/tutor/'.$this->tutor->id.'?week='.$this->slot->starts_at->toDateString())->assertOk()->assertDontSee('PETUNJUK-RAHASIA');
    }

    public function test_private_instructions_need_confirmation_and_owner(): void
    {
        $b = $this->book();
        $this->actingAs($this->student)->get('/reservasi/'.$b->id)->assertDontSee('PETUNJUK-RAHASIA');
        $this->schedule->change($this->staff, $b, 'confirm', 'Sesi disetujui tutor.');
        $this->get('/reservasi/'.$b->id)->assertSee('PETUNJUK-RAHASIA');
        $this->actingAs(User::factory()->create())->get('/reservasi/'.$b->id)->assertForbidden();
    }

    public function test_student_cannot_manage_tutors(): void
    {
        $this->actingAs($this->student)->get('/petugas/tutor')->assertForbidden();
        $this->post('/petugas/tutor', [])->assertForbidden();
    }

    public function test_guests_cannot_book(): void
    {
        $this->post('/jadwal/'.$this->slot->id.'/pesan', ['topic' => 'Latihan soal matematika'])->assertRedirect('/masuk');
    }

    public function test_tutor_search_uses_real_profile(): void
    {
        $this->get('/?subject=Matematika&level=SMP&q=Tutor')->assertSee('Tutor Pengujian');
        $this->get('/?subject=Fisika')->assertDontSee('Tutor Pengujian');
    }

    public function test_form_duration_string_creates_slot(): void
    {
        $this->actingAs($this->staff)->post('/petugas/tutor/'.$this->tutor->id.'/jadwal', [
            'starts_at' => $this->slot->starts_at->copy()->addDays(2)->format('Y-m-d\TH:i'),
            'duration' => '60', 'meeting_details' => 'Petunjuk sesi privat',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(2, Slot::count());
    }
}
