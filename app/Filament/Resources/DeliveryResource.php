<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Models\Delivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon  = 'lucide-truck';
    protected static ?string $navigationGroup = 'Fases Especializadas';
    protected static ?string $navigationLabel = 'Entrega Final';

    /** 🔐 Solo visible en el menú para Entrega y Admin */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Entrega']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('delivered_to')
                ->label('Entregado a')
                ->placeholder('Nombre de la persona o área que recibe')
                ->maxLength(255)
                ->required(),

            Forms\Components\DatePicker::make('delivery_date')
                ->label('Fecha de Entrega')
                ->default(now())
                ->required(),

            Forms\Components\Textarea::make('notes')
                ->label('Observaciones')
                ->maxLength(500),

            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'Pendiente'   => 'Pendiente',
                    'En Proceso'  => 'En Proceso',
                    'Finalizado'  => 'Finalizado',
                ])
                ->default('Pendiente')
                ->required()
                ->afterStateUpdated(function ($state, $record) {
                    if ($state !== 'Finalizado' || ! $record) {
                        return;
                    }

                    $controlProcess = $record->controlProcess;
                    if (! $controlProcess) return;

                    // ✅ Marcar proceso principal como FINALIZADO + fecha de finalización
                    $controlProcess->update([
                        'status'   => 'Finalizado',
                        'end_date' => now(),
                    ]);

                    // ✅ Guardar log final (observación)
                    $controlProcess->phaseLogs()->create([
                        'phase_id'     => $controlProcess->phase_id,
                        'user_id'      => auth()->id(),
                        'observations' => 'Proceso completado en Entrega Final',
                    ]);

                    Notification::make()
                        ->title('El proceso fue marcado como COMPLETAMENTE FINALIZADO 🎉')
                        ->success()
                        ->send();
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ⚡ Optimización: eager-load y select mínimo
            ->modifyQueryUsing(function (Builder $query) {
                $query
                    ->with([
                        'controlProcess:id,order_number',
                        'user:id,name',
                    ])
                    ->select([
                        'id',
                        'control_process_id',
                        'user_id',
                        'delivered_to',
                        'delivery_date',
                        'status',
                        'created_at',
                    ]);
            })
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('controlProcess.order_number')
                    ->label('N° Pedido')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario responsable')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('delivered_to')
                    ->label('Entregado a')
                    ->default('—'),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Fecha de Entrega')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'Pendiente',
                        'info'    => 'En Proceso',
                        'success' => 'Finalizado',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) =>
                        auth()->user()?->hasAnyRole(['Admin', 'Entrega'])
                        && $record->status !== 'Finalizado'
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('Admin')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit'   => Pages\EditDelivery::route('/{record}/edit'),
            'view'   => Pages\ViewDelivery::route('/{record}'),
        ];
    }

    // 🔐 Permisos
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Entrega']);
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Entrega']);
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Entrega']);
    }
}
