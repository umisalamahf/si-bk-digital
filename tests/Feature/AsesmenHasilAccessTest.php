<?php

namespace Tests\Feature;

use Tests\TestCase;

class AsesmenHasilAccessTest extends TestCase
{
    public function test_public_users_cannot_access_counselor_assessment_results(): void
    {
        $this->get(route('asesmen.hasil'))->assertRedirect(route('login'));
    }
}
