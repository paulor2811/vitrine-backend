<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TopClickedProductsWidget extends BaseWidget
{
    protected static ?string $heading = '🔥 Produtos Mais Clicados (30 dias)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $frontendUrl = config('app.frontend_url');

        return $table
            ->query(fn (): Builder => Product::query()
                ->select([
                    'products.id',
                    'products.name',
                    'products.niche_id',
                    'products.store_id',
                    'products.affiliate_url',
                    DB::raw('COUNT(analytics_events.id) as clicks_count'),
                ])
                ->join('analytics_events', 'analytics_events.product_id', '=', 'products.id')
                ->where('analytics_events.event_type', 'product_redirect')
                ->where('analytics_events.created_at', '>=', now()->subDays(30))
                ->groupBy('products.id', 'products.name', 'products.niche_id', 'products.store_id', 'products.affiliate_url')
                ->orderByDesc('clicks_count')
                ->with('niche:id,name,icon,slug', 'store:id,name')
                ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produto')
                    ->searchable()
                    ->wrap()
                    ->url(fn ($record) => ProductResource::getUrl('edit', ['record' => $record->id]))
                    ->color('primary'),

                Tables\Columns\TextColumn::make('niche.name')
                    ->label('Nicho')
                    ->formatStateUsing(fn ($state, $record) => ($record->niche?->icon ?? '') . ' ' . ($state ?? '—'))
                    ->badge()
                    ->color('gray')
                    ->url(fn ($record) => $record->niche?->slug ? $frontendUrl . '/' . $record->niche->slug : null),

                Tables\Columns\TextColumn::make('store.name')
                    ->label('Loja')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Cliques')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('affiliate_url')
                    ->label('Link afiliado')
                    ->limit(30)
                    ->url(fn ($record) => $record->affiliate_url, true)
                    ->color('gray')
                    ->icon('heroicon-m-arrow-top-right-on-square'),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_vitrine')
                    ->label('Vitrine')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->url(fn ($record) => $record->niche?->slug
                        ? $frontendUrl . '/' . $record->niche->slug . '/' . $record->id
                        : $frontendUrl, true),

                Tables\Actions\Action::make('ver_oferta')
                    ->label('Oferta')
                    ->icon('heroicon-m-shopping-cart')
                    ->color('success')
                    ->url(fn ($record) => $record->affiliate_url, true),
            ])
            ->paginated(false)
            ->defaultSort('clicks_count', 'desc');
    }
}
