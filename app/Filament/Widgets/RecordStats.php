<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use App\Models\Track;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RecordStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Records', Record::count())
                ->description('Total records in the crate'),
            Stat::make('Tracks', Track::count())
                ->description('Total tracks in the library'),
            Stat::make('Collection value', '$'.number_format((float) Record::sum('purchase_price'), 2))
                ->description('Sum of purchase prices'),
            Stat::make('Members', User::count())
                ->description('Registered accounts'),
        ];
    }
}
