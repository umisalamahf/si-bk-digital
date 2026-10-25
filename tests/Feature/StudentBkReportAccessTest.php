<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class StudentBkReportAccessTest extends TestCase
{
    public function test_public_users_are_redirected_from_the_student_bk_report(): void
    {
        $this->get(route('laporan.siswa-bk.index'))
            ->assertRedirect(route('login'));
    }

    public function test_student_and_non_counselor_roles_cannot_access_the_student_bk_report(): void
    {
        foreach (['siswa', 'wali_kelas', 'kepala_sekolah'] as $role) {
            $this->actingAs(new User(['role' => $role]))
                ->get(route('laporan.siswa-bk.index'))
                ->assertForbidden();
        }
    }
}
