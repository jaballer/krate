<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            // Optional link to the album this track belongs to. Nullable so the
            // standalone track library keeps working; nullOnDelete orphans a
            // record's tracks (back to standalone) rather than deleting them.
            $table->foreignId('record_id')
                ->nullable()
                ->after('id')
                ->constrained('vinyl_records')
                ->nullOnDelete();

            // Tracklist ordering within a record: side (A/B/C/D) then position.
            $table->string('side', 1)->nullable()->after('album');
            $table->integer('position')->nullable()->after('side');
        });
    }

    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropForeign(['record_id']);
            $table->dropColumn(['record_id', 'side', 'position']);
        });
    }
};
