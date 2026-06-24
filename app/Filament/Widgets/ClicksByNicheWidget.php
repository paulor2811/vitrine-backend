<?php

namespace App\Filament\Widgets;

use App\Models\Niche;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClicksByNicheWidget extends BaseWidget
{
    protected static ?string $heading = '📊 Cliques por Nicho (30 dias)';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Niche::query()
                ->select([
                    'niches.id',
                    'niches.name',
                    'niches.icon',
                    DB::raw('COUNT(analytics_events.id) as clicks_count'),
                ])
                ->join('analytics_events', 'analytics_events.niche_id', '=', 'niches.id')
                ->where('analytics_events.event_type', 'product_redirect')
                ->where('analytics_events.created_at', '>=', now()->subDays(30))
                ->groupBy('niches.id', 'niches.name', 'niches.icon')
                ->orderByDesc('clicks_count')
                ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nicho')
                    ->formatStateUsing(fn ($state, $record) => $record->icon . ' ' . $state),

                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Cliques')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->paginated(false)
            ->defaultSort('clicks_count', 'desc');
    }
}
