<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rpl', function (Blueprint $table) {
            $table->text('media')->nullable()->after('tempat');
        });
    }

    public function down(): void
    {
        Schema::table('rpl', function (Blueprint $table) {
            $table->dropColumn('media');
        });
    }
};
