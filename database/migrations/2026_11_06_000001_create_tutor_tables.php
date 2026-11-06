<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->string('role')->default('student'));
        Schema::create('tutors', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('subject');
            $t->string('level');
            $t->string('headline');
            $t->text('bio');
            $t->unsignedInteger('hourly_rate');
            $t->string('mode');
            $t->string('city');
            $t->string('photo')->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('slots', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tutor_id')->constrained();
            $t->dateTime('starts_at');
            $t->dateTime('ends_at');
            $t->unsignedInteger('duration');
            $t->boolean('active')->default(true);
            $t->text('meeting_details')->nullable();
            $t->timestamps();
            $t->unique(['tutor_id', 'starts_at']);
        });
        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('slot_id')->constrained();
            $t->foreignId('active_slot_id')->nullable()->unique()->constrained('slots');
            $t->unsignedInteger('hourly_rate');
            $t->unsignedInteger('fee');
            $t->string('status')->default('pending');
            $t->text('topic');
            $t->timestamps();
        });
        Schema::create('booking_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('booking_id')->constrained();
            $t->foreignId('actor_id')->constrained('users');
            $t->string('description');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['booking_events', 'bookings', 'slots', 'tutors'] as $n) {
            Schema::dropIfExists($n);
        }Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
    }
};
