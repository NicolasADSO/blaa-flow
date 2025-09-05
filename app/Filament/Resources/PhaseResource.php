<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhaseResource\Pages;
use App\Models\Phase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PhaseResource extends Resource
{
    protected static ?string $model = Phase::class;

    protected static ?string $navigationIcon = 'lucide-flag';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Fases del Proceso';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre de la fase')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->label('Descripción')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\Select::make('input_type')
                ->label('Tipo de entrada')
                ->options([
                    'form' => 'Formulario',
                    'file' => 'Archivo',
                    'check' => 'Confirmación',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre'),
                Tables\Columns\TextColumn::make('description')->label('Descripción')->limit(50),
                Tables\Columns\TextColumn::make('input_type')->label('Tipo de entrada'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhases::route('/'),
            'create' => Pages\CreatePhase::route('/create'),
            'edit' => Pages\EditPhase::route('/{record}/edit'),
        ];
    }

    // 🔐 Permisos
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->hasRole('Administrador') || $user?->hasRole('Recepcionista');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user?->hasRole('Administrador');
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        return $user?->hasRole('Administrador');
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        return $user?->hasRole('Administrador');
    }
}
