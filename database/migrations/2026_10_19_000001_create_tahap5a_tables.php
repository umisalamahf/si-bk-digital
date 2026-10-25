<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(file_get_contents(__DIR__.'/sql/tahap5a_tables.sql'));
    }

    public function down(): void
    {
        DB::unprepared("
            SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE IF EXISTS alumni, kalender_agenda, jadwal_layanan, home_visit;
            SET FOREIGN_KEY_CHECKS=1;
        ");
    }
};
