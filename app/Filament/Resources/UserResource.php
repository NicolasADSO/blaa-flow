<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'lucide-users';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Usuarios';

    /** 🔐 Mostrar en menú solo para Admin */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Correo')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                ->required(fn(string $context) => $context === 'create')
                ->maxLength(255),

            // 🔹 Selector de roles
            Forms\Components\Select::make('roles')
                ->label('Roles')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload(),

            // 🔹 Selector de permisos directos
            Forms\Components\Select::make('permissions')
                ->label('Permisos directos')
                ->multiple()
                ->relationship('permissions', 'name')
                ->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre'),
                Tables\Columns\TextColumn::make('email')->label('Correo'),
                Tables\Columns\TextColumn::make('roles.name')->label('Roles')->badge(),
                Tables\Columns\TextColumn::make('permissions.name')->label('Permisos')->badge(),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // 🔐 Permisos solo Admin
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('Admin');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('Admin');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('Admin');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('Admin');
    }
}
