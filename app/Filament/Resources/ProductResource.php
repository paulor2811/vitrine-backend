<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Niche;
use App\Models\Product;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Produtos';
    protected static ?string $modelLabel = 'Produto';
    protected static ?string $pluralModelLabel = 'Produtos';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informações principais')->schema([
                Forms\Components\Select::make('niche_id')
                    ->label('Nicho')
                    ->options(Niche::where('active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->native(false),

                Forms\Components\Select::make('store_id')
                    ->label('Loja')
                    ->options(Store::where('active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->native(false),

                Forms\Components\TextInput::make('name')
                    ->label('Nome do produto')
                    ->required()
                    ->minLength(3)
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('description')
                    ->label('Descrição curta')
                    ->rows(2)
                    ->maxLength(500)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('affiliate_url')
                    ->label('Link de afiliado')
                    ->required()
                    ->url()
                    ->maxLength(2048)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Imagem')->schema([
                Forms\Components\FileUpload::make('image_path')
                    ->label('Imagem do produto')
                    ->image()
                    ->disk('r2')
                    ->visibility('public')
                    ->directory('products')
                    ->imageEditor()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->helperText('JPEG, PNG ou WebP — máx. 5 MB.')
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Preço e avaliação')->schema([
                Forms\Components\TextInput::make('price')
                    ->label('Preço atual (R$)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(99999999.99)
                    ->step(0.01)
                    ->prefix('R$'),

                Forms\Components\TextInput::make('original_price')
                    ->label('Preço original (R$)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(99999999.99)
                    ->step(0.01)
                    ->prefix('R$'),

                Forms\Components\TextInput::make('rating')
                    ->label('Avaliação (0–5)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(5)
                    ->step(0.1),

                Forms\Components\TextInput::make('rating_count')
                    ->label('Nº de avaliações')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(9999999)
                    ->integer(),
            ])->columns(2),

            Forms\Components\Section::make('Galeria de mídia')->schema([
                Forms\Components\Repeater::make('media')
                    ->label('Mídia (até 10 itens — imagens e vídeos)')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options(['image' => 'Imagem', 'video' => 'Vídeo'])
                            ->required()
                            ->default('image')
                            ->live(),

                        Forms\Components\FileUpload::make('path')
                            ->label('Imagem')
                            ->image()
                            ->disk('r2')
                            ->directory('product-media')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->helperText('JPEG, PNG ou WebP — máx. 5 MB.')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'image'),

                        Forms\Components\TextInput::make('video_url')
                            ->label('URL do vídeo (YouTube ou TikTok)')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://youtube.com/watch?v=...')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'video'),
                    ])
                    ->columns(1)
                    ->maxItems(10)
                    ->reorderable()
                    ->reorderableWithDragAndDrop()
                    ->orderColumn('sort_order')
                    ->collapsible()
                    ->addActionLabel('Adicionar mídia')
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Destaques')->schema([
                Forms\Components\Select::make('badge')
                    ->label('Badge')
                    ->options([
                        'mais_vendido' => '🔥 Mais Vendido',
                        'top_avaliado' => '⭐ Top Avaliado',
                        'promocao'     => '🏷️ Promoção',
                        'destaque'     => '✨ Destaque',
                    ])
                    ->nullable()
                    ->native(false),

                Forms\Components\Toggle::make('featured')
                    ->label('Em destaque')
                    ->default(false),

                Forms\Components\Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('')
                    ->disk('r2')
                    ->square()
                    ->size(48),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('niche.name')
                    ->label('Nicho')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Loja')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\IconColumn::make('featured')
                    ->label('Destaque')
                    ->boolean(),
                Tables\Columns\ToggleColumn::make('active')
                    ->label('Ativo'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('niche')
                    ->label('Nicho')
                    ->relationship('niche', 'name'),
                Tables\Filters\SelectFilter::make('store')
                    ->label('Loja')
                    ->relationship('store', 'name'),
                Tables\Filters\TernaryFilter::make('featured')->label('Em destaque'),
                Tables\Filters\TernaryFilter::make('active')->label('Ativo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
