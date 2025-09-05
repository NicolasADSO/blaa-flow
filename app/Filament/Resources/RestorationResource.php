<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RestorationResource\Pages;
use App\Models\Restoration;
use App\Models\Phase;
use App\Models\Binding;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class RestorationResource extends Resource
{
    protected static ?string $model = Restoration::class;

    protected static ?string $navigationIcon  = 'lucide-wrench';
    protected static ?string $navigationGroup = 'Fases Especializadas';
    protected static ?string $navigationLabel = 'Restauración';

    /** 🔐 Solo visible para Restaurador y Admin */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Restaurador']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('damage_type')
                ->label('Daño identificado')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('technique_used')
                ->label('Técnica utilizada')
                ->maxLength(255),

            Forms\Components\Textarea::make('materials')
                ->label('Materiales utilizados')
                ->rows(3),

            Forms\Components\FileUpload::make('before_photo')
                ->label('Foto antes de la restauración')
                ->image()
                ->directory('restorations/before')
                ->downloadable(),

            Forms\Components\FileUpload::make('after_photo')
                ->label('Foto después de la restauración')
                ->image()
                ->directory('restorations/after')
                ->downloadable(),

            Forms\Components\Textarea::make('notes')
                ->label('Observaciones adicionales')
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

                    // Buscar siguiente fase por orden global: Empaste
                    $nextPhase = Phase::where('order', '>', $currentPhase->order)
                        ->orderBy('order')
                        ->first();

                    if ($nextPhase && $nextPhase->name === 'Empaste') {
                        $controlProcess->update([
                            'phase_id' => $nextPhase->id,
                            'status'   => 'En Proceso',
                        ]);

                        Binding::create([
                            'control_process_id' => $controlProcess->id,
                            'user_id'            => auth()->id(),
                            'status'             => 'Pendiente',
                        ]);

                        $controlProcess->phaseLogs()->create([
                            'phase_id'     => $nextPhase->id,
                            'user_id'      => auth()->id(),
                            'observations' => 'Avanzó automáticamente a Empaste desde Restauración',
                        ]);

                        Notification::make()
                            ->title('El proceso avanzó automáticamente a Empaste')
                            ->success()
                            ->send();
                    }
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ⚡ Optimización: eager-loading y select de columnas mínimas
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
                        'damage_type',
                        'technique_used',
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

                Tables\Columns\TextColumn::make('damage_type')
                    ->label('Daño'),

                Tables\Columns\TextColumn::make('technique_used')
                    ->label('Técnica'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsable')
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
                        auth()->user()?->hasAnyRole(['Admin', 'Restaurador'])
                        && $record->status !== 'Finalizado'
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('Admin')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRestorations::route('/'),
            'create' => Pages\CreateRestoration::route('/create'),
            'edit'   => Pages\EditRestoration::route('/{record}/edit'),
            'view'   => Pages\ViewRestoration::route('/{record}'),
        ];
    }

    // 🔐 Permisos
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Restaurador']);
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Restaurador']);
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Restaurador']);
    }
}
