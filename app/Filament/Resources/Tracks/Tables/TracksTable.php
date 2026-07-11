<?php

namespace App\Filament\Resources\Tracks\Tables;

use App\Models\Track;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TracksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('artist')
                    ->searchable(),
                TextColumn::make('album')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('genre')
                    ->searchable(),
                TextColumn::make('duration_seconds')
                    ->label('Length')
                    // Stored as whole seconds; shown as m:ss (e.g. 214 -> "3:34").
                    ->formatStateUsing(fn (?int $state): ?string => Track::formatDuration($state)),
                TextColumn::make('bpm')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('release_year')
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
