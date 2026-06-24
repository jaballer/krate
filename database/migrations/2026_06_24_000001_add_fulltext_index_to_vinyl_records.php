<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backs the public catalog's MATCH … AGAINST search (RecordController).
     * Only MariaDB/MySQL get the FULLTEXT index; SQLite (used in tests) has no
     * FULLTEXT support, and the controller falls back to LIKE there.
     */
    public function up(): void
    {
        if (! $this->supportsFullText()) {
            return;
        }

        Schema::table('vinyl_records', function (Blueprint $table) {
            $table->fullText(['title', 'artist', 'genre', 'label'], 'vinyl_records_search_fulltext');
        });
    }

    public function down(): void
    {
        if (! $this->supportsFullText()) {
            return;
        }

        Schema::table('vinyl_records', function (Blueprint $table) {
            $table->dropFullText('vinyl_records_search_fulltext');
        });
    }

    private function supportsFullText(): bool
    {
        return in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
