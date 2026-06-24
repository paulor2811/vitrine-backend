<?php

namespace App\Filament\Widgets;

use App\Models\Niche;
use App\Repositories\AnalyticsRepository;
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
        $frontendUrl = config('app.frontend_url');
        $total = app(AnalyticsRepository::class)->clicksTotal(30) ?: 1;

        return $table
            ->query(fn (): Builder => Niche::query()
                ->select([
                    'niches.id',
                    'niches.name',
                    'niches.icon',
                    'niches.slug',
                    DB::raw('COUNT(analytics_events.id) as clicks_count'),
                ])
                ->join('analytics_events', 'analytics_events.niche_id', '=', 'niches.id')
                ->where('analytics_events.event_type', 'product_redirect')
                ->where('analytics_events.created_at', '>=', now()->subDays(30))
                ->groupBy('niches.id', 'niches.name', 'niches.icon', 'niches.slug')
                ->orderByDesc('clicks_count')
                ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nicho')
                    ->formatStateUsing(fn ($state, $record) => $record->icon . ' ' . $state)
                    ->url(fn ($record) => $frontendUrl . '/' . $record->slug, true)
                    ->color('primary'),

                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Cliques')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pct')
                    ->label('% do total')
                    ->state(fn ($record) => number_format($record->clicks_count / $total * 100, 1) . '%')
                    ->badge()
                    ->color('gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_nicho')
                    ->label('')
                    ->tooltip('Abrir nicho')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn ($record) => $frontendUrl . '/' . $record->slug, true),
            ])
            ->paginated(false)
            ->defaultSort('clicks_count', 'desc');
    }
}
