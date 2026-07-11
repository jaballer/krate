<?php

namespace App\Filament\Resources\Tracks\Schemas;

use App\Enums\TrackSide;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TrackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('artist')
                    ->required(),
                FileUpload::make('image')
                    ->image()
                    ->maxSize(5120) // 5 MB, matches the record image limit
                    ->disk('public')
                    ->directory('tracks'),
                // Optional link to a record. When set, side/position place the
                // track on that record's tracklist.
                Select::make('record_id')
                    ->label('Record')
                    ->relationship('record', 'title')
                    ->searchable()
                    ->preload(),
                Select::make('side')
                    ->options(TrackSide::class),
                TextInput::make('position')
                    ->numeric(),
                TextInput::make('album')
                    ->helperText('Free-text album name, used when the track is not linked to a record.'),
                TextInput::make('genre'),
                TextInput::make('release_year')
                    ->numeric(),
                TextInput::make('duration_seconds')
                    ->numeric()
                    ->suffix('sec'),
                TextInput::make('bpm')
                    ->numeric(),
                TextInput::make('audio_file_url')
                    ->url(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
