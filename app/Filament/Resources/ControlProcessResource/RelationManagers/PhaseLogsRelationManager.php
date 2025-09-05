<?php

namespace App\Filament\Resources\ControlProcessResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class PhaseLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'phaseLogs';
    
    // 🔹 Personalización de pestaña
    protected static ?string $title = '📝 Historial de Fases';
    protected static ?string $navigationIcon = 'lucide-clipboard-check';

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        // 🚫 Sin formulario → solo lectura
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();

                // Admin ve todo el historial
                if ($user && $user->hasRole('Admin')) {
                    return $query;
                }

                // Otros roles ven solo sus registros
                return $query->where('user_id', $user?->id);
            })
            ->columns([
                Tables\Columns\TextColumn::make('phase.name')
                    ->label('Fase')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('action') // 👈 Badge en vez de texto plano
                    ->label('Acción')
                    ->colors([
                        'success' => fn ($state) => str_contains($state, 'Avanzó'),
                        'danger'  => fn ($state) => str_contains($state, 'finalizado'),
                        'warning' => fn ($state) => str_contains($state, 'creado'),
                    ])
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('observations')
                    ->label('Observaciones')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->headerActions([]) // 🚫 sin crear registros manuales
            ->actions([
                Tables\Actions\ViewAction::make(), // 👁️ solo ver
            ])
            ->bulkActions([]); // 🚫 sin acciones masivas
    }
}
