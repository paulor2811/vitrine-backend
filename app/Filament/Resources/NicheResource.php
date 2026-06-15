<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NicheResource\Pages;
use App\Models\Niche;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NicheResource extends Resource
{
    protected static ?string $model = Niche::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Nichos';
    protected static ?string $modelLabel = 'Nicho';
    protected static ?string $pluralModelLabel = 'Nichos';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                        $set('slug', \Illuminate\Support\Str::slug($state))
                    ),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),

                Forms\Components\TextInput::make('icon')
                    ->label('Ícone (emoji)')
                    ->maxLength(10),

                Forms\Components\ColorPicker::make('bg_color')
                    ->label('Cor de fundo'),

                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->rows(2)
                    ->maxLength(255),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Imagem do nicho')
                    ->image()
                    ->disk('r2')
                    ->directory('niches')
                    ->imageEditor()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120),

                Forms\Components\TextInput::make('instagram_url')
                    ->label('Instagram URL')
                    ->url()
                    ->maxLength(255),

                Forms\Components\TextInput::make('tiktok_url')
                    ->label('TikTok URL')
                    ->url()
                    ->maxLength(255),

                Forms\Components\Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')->label(''),
                Tables\Columns\TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produtos')
                    ->counts('products')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('active')->label('Ativo'),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')->label('Ativo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNiches::route('/'),
            'create' => Pages\CreateNiche::route('/create'),
            'edit'   => Pages\EditNiche::route('/{record}/edit'),
        ];
    }
}
