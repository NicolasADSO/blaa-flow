<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DigitalizationResource\Pages;
use App\Models\Digitalization;
use App\Models\Phase;
use App\Models\QualityControl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class DigitalizationResource extends Resource
{
    protected static ?string $model = Digitalization::class;

    protected static ?string $navigationIcon  = 'lucide-file-text';
    protected static ?string $navigationGroup = 'Fases Especializadas';
    protected static ?string $navigationLabel = 'Digitalización';

    /** 🔐 Solo visible en el menú para Digitalizador y Admin */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Digitalizador']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('file_path')
                ->label('Archivo Digitalizado')
                ->directory('digitalizations')
                ->downloadable()
                ->previewable(true)
                ->required(),

            Forms\Components\Select::make('format')
                ->label('Formato del archivo')
                ->options([
                    'PDF'  => 'PDF',
                    'JPG'  => 'JPG',
                    'TIFF' => 'TIFF',
                ])
                ->required(),

            Forms\Components\TextInput::make('resolution')
                ->label('Resolución (DPI)')
                ->numeric()
                ->minValue(72)
                ->maxValue(1200)
                ->placeholder('Ej: 300'),

            Forms\Components\TextInput::make('pages_count')
                ->label('Número de páginas digitalizadas')
                ->numeric()
                ->minValue(1),

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
                    $currentPhase   = $controlProcess->phase;

                    // Buscar la siguiente fase (Control de Calidad)
                    $nextPhase = Phase::where('order', '>', $currentPhase->order)
                        ->orderBy('order')
                        ->first();

                    if ($nextPhase && $nextPhase->name === 'Control de Calidad') {
                        // Avanzar el proceso principal
                        $controlProcess->update([
                            'phase_id' => $nextPhase->id,
                            'status'   => 'En Proceso',
                        ]);

                        // Crear registro en QualityControl
                        QualityControl::create([
                            'control_process_id' => $controlProcess->id,
                            'user_id'            => auth()->id(),
                            'status'             => 'Pendiente',
                        ]);

                        // Log con observación
                        $controlProcess->phaseLogs()->create([
                            'phase_id'     => $nextPhase->id,
                            'user_id'      => auth()->id(),
                            'observations' => 'Avanzó automáticamente a Control de Calidad desde Digitalización',
                        ]);

                        Notification::make()
                            ->title('El proceso avanzó automáticamente a Control de Calidad')
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
                        'format',
                        'pages_count',
                        'resolution',
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

                Tables\Columns\TextColumn::make('format')
                    ->label('Formato'),

                Tables\Columns\TextColumn::make('pages_count')
                    ->label('Páginas'),

                Tables\Columns\TextColumn::make('resolution')
                    ->label('Resolución'),

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
                        auth()->user()?->hasAnyRole(['Admin', 'Digitalizador'])
                        && $record->status !== 'Finalizado'
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasRole('Admin')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDigitalizations::route('/'),
            'create' => Pages\CreateDigitalization::route('/create'),
            'edit'   => Pages\EditDigitalization::route('/{record}/edit'),
            'view'   => Pages\ViewDigitalization::route('/{record}'),
        ];
    }

    // 🔐 Permisos
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Digitalizador']);
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Digitalizador']);
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Digitalizador']);
    }
}
