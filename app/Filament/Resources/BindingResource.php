<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BindingResource\Pages;
use App\Models\Binding;
use App\Models\ControlProcess;
use App\Models\Phase;
use App\Models\Digitalization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class BindingResource extends Resource
{
    protected static ?string $model = Binding::class;

    protected static ?string $navigationIcon  = 'lucide-home';
    protected static ?string $navigationGroup = 'Fases Especializadas';
    protected static ?string $navigationLabel = 'Encuadernación / Empaste';

    /** 🔐 Solo visible en el menú para Encuadernador y Admin */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Encuadernador']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('binding_type')
                ->label('Tipo de empaste')
                ->placeholder('Ej: Rústica, Tapa dura, Cosido')
                ->required()
                ->maxLength(100),

            Forms\Components\Textarea::make('materials')
                ->label('Materiales utilizados')
                ->rows(3),

            Forms\Components\FileUpload::make('cover_photo')
                ->label('Foto del libro empastado')
                ->image()
                ->directory('bindings')
                ->downloadable(),

            Forms\Components\Textarea::make('notes')
                ->label('Observaciones')
                ->rows(3)
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
                    $currentPhase   = $controlProcess->phase;

                    // Buscar la siguiente fase (Digitalización)
                    $nextPhase = Phase::where('order', '>', $currentPhase->order)
                        ->orderBy('order')
                        ->first();

                    if ($nextPhase && $nextPhase->name === 'Digitalización') {
                        // Avanzar proceso
                        $controlProcess->update([
                            'phase_id' => $nextPhase->id,
                            'status'   => 'En Proceso',
                        ]);

                        // Crear registro en Digitalización
                        Digitalization::create([
                            'control_process_id' => $controlProcess->id,
                            'user_id'            => auth()->id(),
                            'status'             => 'Pendiente',
                        ]);

                        // Log con observación
                        $controlProcess->phaseLogs()->create([
                            'phase_id'     => $nextPhase->id,
                            'user_id'      => auth()->id(),
                            'observations' => 'Avanzó automáticamente a Digitalización desde Empaste',
                        ]);

                        Notification::make()
                            ->title('El proceso avanzó automáticamente a Digitalización')
                            ->success()
                            ->send();
                    }
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ⚡ Optimización: menos columnas y eager-loading
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
                        'binding_type',
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

                Tables\Columns\TextColumn::make('binding_type')
                    ->label('Tipo de empaste'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario responsable')
                    ->sortable()
                    ->searchable(),

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
                        auth()->user()?->hasAnyRole(['Admin', 'Encuadernador'])
                        && $record->status !== 'Finalizado'
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('Admin')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBindings::route('/'),
            'create' => Pages\CreateBinding::route('/create'),
            'edit'   => Pages\EditBinding::route('/{record}/edit'),
            'view'   => Pages\ViewBinding::route('/{record}'),
        ];
    }

    // 🔐 Permisos
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Encuadernador']);
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Encuadernador']);
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Encuadernador']);
    }
}
