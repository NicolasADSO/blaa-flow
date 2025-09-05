<?php

namespace App\Filament\Resources\ControlProcessResource\Tables;

use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class ControlProcessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order_number')
                    ->label('N° Pedido')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('act_number')
                    ->label('N° Acta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('responsible.name')
                    ->label('Responsable')
                    ->sortable(),

                TextColumn::make('phase.name')  
                    ->label('Fase actual')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->status === 'Finalizado' ? 'success' : 'info')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->status === 'Finalizado' ? 'Finalizado' : $state),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('COP', true)
                    ->sortable(),

                TextColumn::make('iva')
                    ->label('IVA')
                    ->money('COP', true)
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('COP', true)
                    ->sortable(),

                TextColumn::make('delivery_date')
                    ->label('Entrega')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('invoice_date')
                    ->label('Factura')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Pago')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('real_duration')
                    ->label('Duración (días)')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'danger'  => 'Pendiente',
                        'warning' => 'En Proceso',
                        'success' => 'Pagado',
                        'primary' => 'Finalizado',
                    ])
                    ->sortable(),

                TextColumn::make('observations')
                    ->label('Observaciones')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pendiente'  => 'Pendiente',
                        'En Proceso' => 'En Proceso',
                        'Pagado'     => 'Pagado',
                        'Finalizado' => 'Finalizado',
                    ]),
            ])
            ->headerActions([   // 👈 AQUI AGREGAMOS EL BOTÓN CREATE
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                
                Action::make('avanzar')
                    ->label('Avanzar')
                    ->icon('lucide-arrow-right')
                    ->color('success')
                    ->requiresConfirmation()
                    ->disabled(fn ($record) => $record->status === 'Finalizado')
                    ->action(function ($record) {
                        if ($record->avanzarAFaseSiguiente()) {
                            Notification::make()
                                ->title('Fase actualizada')
                                ->body('El proceso avanzó a la siguiente fase: ' . $record->phase->name)
                                ->success()
                                ->send();
                        } else {
                            $record->status = 'Finalizado';
                            $record->save();

                            Notification::make()
                                ->title('Proceso finalizado')
                                ->body('El proceso ha completado todas las fases 🎉')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
