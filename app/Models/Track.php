<?php

namespace App\Models;

use App\Enums\TrackSide;
use Database\Factories\TrackFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'record_id', 'title', 'artist', 'album', 'side', 'position', 'genre',
    'release_year', 'duration_seconds', 'bpm', 'audio_file_url', 'notes',
])]
class Track extends Model
{
    /** @use HasFactory<TrackFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'record_id' => 'integer',
            'side' => TrackSide::class,
            'position' => 'integer',
            'release_year' => 'integer',
            'duration_seconds' => 'integer',
            'bpm' => 'integer',
        ];
    }

    /**
     * The album this track belongs to, if any (tracks may be standalone).
     *
     * @return BelongsTo<Record, $this>
     */
    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class);
    }

    /** Format a whole-second duration as m:ss (e.g. 214 → "3:34"); null when unset. */
    public static function formatDuration(?int $seconds): ?string
    {
        return $seconds === null
            ? null
            : sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}
