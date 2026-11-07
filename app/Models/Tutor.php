<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    protected $guarded = ['id'];

    public const SUBJECTS = ['Matematika', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Bahasa Indonesia', 'Pemrograman'];

    public const LEVELS = ['SD', 'SMP', 'SMA', 'Umum'];

    public const MODES = ['Online', 'Tatap muka'];

    protected function casts(): array
    {
        return ['hourly_rate' => 'integer', 'active' => 'boolean'];
    }

    public function slots()
    {
        return $this->hasMany(Slot::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
