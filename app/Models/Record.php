<?php

namespace App\Models;

use App\Enums\RecordCondition;
use App\Enums\RecordFormat;
use App\Enums\RecordSpeed;
use Database\Factories\RecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title', 'artist', 'genre', 'release_year', 'label', 'catalog_number',
    'format', 'speed', 'condition', 'purchase_date', 'purchase_price', 'notes',
    'front_image', 'back_image', 'purchase_link', 'audio_file_url', 'bpm',
])]
class Record extends Model
{
    /** @use HasFactory<RecordFactory> */
    use HasFactory;

    protected $table = 'vinyl_records';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_year' => 'integer',
            'purchase_date' => 'date',
            'purchase_price' => 'decimal:2',
            'bpm' => 'integer',
            'format' => RecordFormat::class,
            'speed' => RecordSpeed::class,
            'condition' => RecordCondition::class,
        ];
    }

    /**
     * This record's tracklist, pre-ordered by side then position.
     *
     * @return HasMany<Track, $this>
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class)
            ->orderBy('side')
            ->orderBy('position')
            ->orderBy('id');
    }
}
