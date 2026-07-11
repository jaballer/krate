<?php

namespace App\Filament\Resources\Tracks\Pages;

use App\Filament\Resources\Tracks\TrackResource;
use Filament\Resources\Pages\CreateRecord as BaseCreateRecord;

class CreateTrack extends BaseCreateRecord
{
    protected static string $resource = TrackResource::class;
}
