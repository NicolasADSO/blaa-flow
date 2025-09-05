<?php

namespace App\Filament\Resources\ControlProcessResource\Pages;

use App\Filament\Resources\ControlProcessResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions;

class ListControlProcesses extends ListRecords
{
    protected static string $resource = ControlProcessResource::class;

    /**
     * Botones en el encabezado (arriba a la derecha)
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // 👈 dejamos solo este
        ];
    }

    /**
     * Filtros disponibles en la tabla
     */
    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->label('Estado')
                ->options([
                    'Pendiente'   => 'Pendiente',
                    'En Proceso'  => 'En Proceso',
                    'Finalizado'  => 'Finalizado',
                ]),

            Tables\Filters\SelectFilter::make('phase_id')
                ->label('Fase actual')
                ->relationship('phase', 'name'),
        ];
    }

    /**
     * Acciones disponibles en cada fila de la tabla
     */
    protected function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->infolist([
                    Section::make('Resumen del Proceso')
                        ->schema([
                            TextEntry::make('order_number')->label('Número de Orden'),
                            TextEntry::make('phase.name')->label('Fase actual'),
                            TextEntry::make('status')->label('Estado'),
                            TextEntry::make('responsible')->label('Responsable'),
                            TextEntry::make('start_date')->label('Inicio')->dateTime('d/m/Y H:i'),
                            TextEntry::make('end_date')->label('Finalización')->dateTime('d/m/Y H:i'),
                        ])
                        ->columns(2),

                    Section::make('Último movimiento')
                        ->schema([
                            TextEntry::make('phaseLogs.last.phase.name')
                                ->label('Fase registrada'),
                            TextEntry::make('phaseLogs.last.user.name')
                                ->label('Usuario')
                                ->default('Sistema'),
                            TextEntry::make('phaseLogs.last.status')
                                ->label('Estado'),
                            TextEntry::make('phaseLogs.last.notes')
                                ->label('Notas')
                                ->default('—'),
                            TextEntry::make('phaseLogs.last.created_at')
                                ->label('Fecha de registro')
                                ->dateTime('d/m/Y H:i'),
                        ])
                        ->columns(2),
                ]),

            // Editar y eliminar solo para Admin
            Tables\Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->hasRole('Admin')),
            Tables\Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->hasRole('Admin')),
        ];
    }
}
