<?php

namespace App\Filament\Resources\Records\RelationManagers;

use App\Enums\TrackSide;
use App\Models\Track;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TracksRelationManager extends RelationManager
{
    protected static string $relationship = 'tracks';

    // Used by AssociateAction's search and by dissociate/delete confirmations.
    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        // No record picker here — the parent record is implied by this manager.
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('artist')
                    ->required(),
                FileUpload::make('image')
                    ->image()
                    ->maxSize(5120)
                    ->disk('public')
                    ->directory('tracks'),
                Select::make('side')
                    ->options(TrackSide::class),
                TextInput::make('position')
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('public'),
                TextColumn::make('side')
                    ->badge(),
                TextColumn::make('position')
                    ->numeric(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('artist')
                    ->searchable(),
                TextColumn::make('duration_seconds')
                    ->label('Length')
                    ->formatStateUsing(fn (?int $state): ?string => Track::formatDuration($state)),
                TextColumn::make('bpm')
                    ->numeric(),
            ])
            ->headerActions([
                CreateAction::make(),
                // Only offer unlinked tracks — associating a track already on
                // another record would silently steal it from that tracklist.
                AssociateAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->whereNull('record_id')),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
