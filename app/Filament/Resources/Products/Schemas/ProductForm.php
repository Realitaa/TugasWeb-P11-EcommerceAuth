<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Kategori')
                    ->disabled(fn () => ! auth()->user()?->isAdmin())
                    ->dehydrated(fn () => auth()->user()?->isAdmin()),
                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn () => ! auth()->user()?->isAdmin())
                    ->dehydrated(fn () => auth()->user()?->isAdmin()),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(100)
                    ->disabled(fn () => ! auth()->user()?->isAdmin())
                    ->dehydrated(fn () => auth()->user()?->isAdmin()),
                TextInput::make('price')
                    ->label('Harga (IDR)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('Rp')
                    ->helperText('Dapat diubah oleh Admin & Editor'),
                TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Dapat diubah oleh Admin & Editor'),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Tags Produk')
                    ->helperText('Dapat dikelola & di-assign oleh Admin & Editor'),
                TextInput::make('discount_percentage')
                    ->label('Diskon (%)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->disabled(fn () => ! auth()->user()?->isAdmin())
                    ->dehydrated(fn () => auth()->user()?->isAdmin()),
                TextInput::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(5)
                    ->step(0.1)
                    ->disabled(fn () => ! auth()->user()?->isAdmin())
                    ->dehydrated(fn () => auth()->user()?->isAdmin()),
                TextInput::make('thumbnail')
                    ->label('URL Foto / Thumbnail')
                    ->placeholder('https://picsum.photos/640/480')
                    ->maxLength(2048)
                    ->disabled(fn () => ! auth()->user()?->isAdmin())
                    ->dehydrated(fn () => auth()->user()?->isAdmin()),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull()
                    ->rows(4)
                    ->disabled(fn () => ! auth()->user()?->isAdmin())
                    ->dehydrated(fn () => auth()->user()?->isAdmin()),
            ]);
    }
}
