<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_has_no_fictitious_tutors(): void
    {
        $this->withoutVite()->get('/')->assertOk()->assertSee('0 tutor tersedia');
    }
}
