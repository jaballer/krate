<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('setting_key')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                Select::make('setting_type')
                    ->options([
                        'string' => 'String',
                        'integer' => 'Integer',
                        'float' => 'Float',
                        'boolean' => 'Boolean',
                        'json' => 'JSON',
                        'array' => 'Array',
                    ])
                    ->required()
                    ->default('string'),
                Textarea::make('setting_value')
                    ->columnSpanFull(),
                TextInput::make('category')
                    ->required()
                    ->default('general'),
                TextInput::make('description'),
                Toggle::make('is_private'),
            ]);
    }
}
