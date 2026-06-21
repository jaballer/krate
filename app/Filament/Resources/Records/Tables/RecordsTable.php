<?php

namespace App\Filament\Resources\Records\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('front_image')
                    ->disk('public'),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('artist')
                    ->searchable(),
                TextColumn::make('genre')
                    ->searchable(),
                TextColumn::make('release_year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('label')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('catalog_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('format')
                    ->badge(),
                TextColumn::make('speed')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('condition')
                    ->badge(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purchase_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('bpm')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
