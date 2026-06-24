<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClicksBySourceWidget extends BaseWidget
{
    protected static ?string $heading = '🌐 Cliques por Fonte (30 dias)';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => AnalyticsEvent::query()
                ->select([
                    DB::raw('MIN(id) as id'),
                    DB::raw("COALESCE(utm_source, 'direto') as utm_source"),
                    DB::raw('COUNT(*) as clicks_count'),
                ])
                ->where('event_type', 'product_redirect')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy(DB::raw("COALESCE(utm_source, 'direto')"))
                ->orderByDesc('clicks_count')
                ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('utm_source')
                    ->label('Fonte de tráfego'),

                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Cliques')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
            ])
            ->paginated(false)
            ->defaultSort('clicks_count', 'desc');
    }
}
