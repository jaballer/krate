<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('setting_key')
                    ->required(),
                Textarea::make('setting_value')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('setting_type')
                    ->required()
                    ->default('string'),
                TextInput::make('category')
                    ->required()
                    ->default('general'),
                TextInput::make('description')
                    ->default(null),
                Toggle::make('is_private')
                    ->required(),
                TextInput::make('created_by')
                    ->numeric()
                    ->default(null),
                TextInput::make('updated_by')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
