<?php

namespace App\Filament\Resources\Tracks\Schemas;

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
                TextInput::make('album'),
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
