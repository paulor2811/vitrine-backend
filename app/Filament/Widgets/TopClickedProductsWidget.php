<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\AnalyticsEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopClickedProductsWidget extends BaseWidget
{
    protected static ?string $heading = '🔥 Produtos Mais Clicados';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Product::query()
                ->select([
                    'products.id',
                    'products.name',
                    'products.niche_id',
                    DB::raw('COUNT(analytics_events.id) as clicks_count'),
                ])
                ->join('analytics_events', 'analytics_events.product_id', '=', 'products.id')
                ->where('analytics_events.event_type', 'product_redirect')
                ->where('analytics_events.created_at', '>=', now()->subDays(30))
                ->groupBy('products.id', 'products.name', 'products.niche_id')
                ->orderByDesc('clicks_count')
                ->with('niche:id,name,icon', 'store:id,name')
                ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produto')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('niche.name')
                    ->label('Nicho')
                    ->formatStateUsing(fn ($state, $record) => ($record->niche?->icon ?? '') . ' ' . ($record->niche?->name ?? '—'))
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Loja')
                    ->badge(),

                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Cliques (30d)')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->paginated(false)
            ->defaultSort('clicks_count', 'desc');
    }
}
