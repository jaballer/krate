<?php

namespace App\Filament\Resources\Records\Schemas;

use App\Enums\RecordCondition;
use App\Enums\RecordFormat;
use App\Enums\RecordSpeed;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('artist')
                    ->required(),
                TextInput::make('genre'),
                TextInput::make('release_year')
                    ->numeric(),
                TextInput::make('label'),
                TextInput::make('catalog_number'),
                Select::make('format')
                    ->options(RecordFormat::class)
                    ->required(),
                Select::make('speed')
                    ->options(RecordSpeed::class)
                    ->required(),
                Select::make('condition')
                    ->options(RecordCondition::class)
                    ->required(),
                DatePicker::make('purchase_date'),
                TextInput::make('purchase_price')
                    ->numeric()
                    ->prefix('$'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                FileUpload::make('front_image')
                    ->image()
                    ->disk('public')
                    ->directory('records'),
                FileUpload::make('back_image')
                    ->image()
                    ->disk('public')
                    ->directory('records'),
                TextInput::make('purchase_link')
                    ->url(),
                TextInput::make('audio_file_url')
                    ->url(),
                TextInput::make('bpm')
                    ->numeric(),
            ]);
    }
}
