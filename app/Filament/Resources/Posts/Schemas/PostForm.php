<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Category;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255),

            Textarea::make('content')
                ->label('Contenido')
                ->rows(6)
                ->required(),

            Select::make('category_id')
                ->label('Categoría')
                ->relationship('category', 'name') // Usa la relación con Category
                ->searchable()
                ->preload()
                ->required(),
        ]);
    }
}
