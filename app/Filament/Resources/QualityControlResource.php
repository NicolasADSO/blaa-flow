<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QualityControlResource\Pages;
use App\Models\QualityControl;
use App\Models\Phase;
use App\Models\Delivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class QualityControlResource extends Resource
{
    protected static ?string $model = QualityControl::class;

    protected static ?string $navigationIcon  = 'lucide-badge-check';
    protected static ?string $navigationGroup = 'Fases Especializadas';
    protected static ?string $navigationLabel = 'Control de Calidad';

    /** 🔐 Solo visible en el menú para Calidad y Admin */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Calidad']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\CheckboxList::make('checklist')
                ->label('Criterios verificados')
                ->options([
                    'legibilidad'    => 'Legibilidad del texto',
                    'integridad'     => 'Integridad de las páginas',
                    'encuadernacion' => 'Encuadernación correcta',
                    'digitalizacion' => 'Archivos digitales correctos',
                ])
                ->columns(2),

            Forms\Components\Toggle::make('approved')
                ->label('¿Aprobado?')
                ->helperText('Marca si el libro cumple con todos los criterios de calidad.')
                ->default(false),

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

                    $cp          = $record->controlProcess()->with('phase')->first();
                    $current     = $cp?->phase;
                    if (! $cp || ! $current) return;

                    // Buscar la siguiente fase
                    $next = Phase::where('order', '>', $current->order)
                        ->orderBy('order')
                        ->first();

                    // Acepta "Entrega Final" (nuevo nombre) o "Disponibilización" (alias viejo)
                    if ($next && in_array($next->name, ['Entrega Final', 'Disponibilización'])) {
                        // Avanzar el proceso principal
                        $cp->update([
                            'phase_id' => $next->id,
                            'status'   => 'En Proceso',
                        ]);

                        // Crear registro en Entrega
                        Delivery::firstOrCreate(
                            ['control_process_id' => $cp->id],
                            ['user_id' => auth()->id(), 'status' => 'Pendiente']
                        );

                        // Log con observación
                        $cp->phaseLogs()->create([
                            'phase_id'     => $next->id,
                            'user_id'      => auth()->id(),
                            'observations' => 'Avanzó automáticamente a Entrega Final desde Control de Calidad',
                        ]);

                        Notification::make()
                            ->title('El proceso avanzó automáticamente a Entrega Final')
                            ->success()
                            ->send();
                    }
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ⚡ Optimización de consulta
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
                        'approved',
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

                Tables\Columns\IconColumn::make('approved')
                    ->label('Aprobado')
                    ->boolean(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'Pendiente',
                        'info'    => 'En Proceso',
                        'success' => 'Finalizado',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) =>
                        auth()->user()?->hasAnyRole(['Admin', 'Calidad'])
                        && $record->status !== 'Finalizado'
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('Admin')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListQualityControls::route('/'),
            'create' => Pages\CreateQualityControl::route('/create'),
            'edit'   => Pages\EditQualityControl::route('/{record}/edit'),
            'view'   => Pages\ViewQualityControl::route('/{record}'),
        ];
    }

    // 🔐 Permisos
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Calidad']);
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Calidad']);
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Calidad']);
    }
}
