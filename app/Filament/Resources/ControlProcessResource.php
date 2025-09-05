<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControlProcessResource\Pages;
use App\Models\Binding;
use App\Models\Cataloging;
use App\Models\ControlProcess;
use App\Models\Delivery;
use App\Models\Digitalization;
use App\Models\Phase;
use App\Models\QualityControl;
use App\Models\Restoration;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ControlProcessResource extends Resource
{
    protected static ?string $model = ControlProcess::class;

    protected static ?string $navigationIcon  = 'lucide-layers';
    protected static ?string $navigationGroup = 'Seguimiento';
    protected static ?string $navigationLabel = 'Control de Procesos';

    protected static ?string $recordTitleAttribute = 'order_number';

    /** 🔐 Mostrar en menú solo para Admin y Recepcionista */
    public static function shouldRegisterNavigation(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $user = auth()->user();
        return $user->hasAnyRole(['Admin', 'Recepcionista']);
    }

    /** ✅ Formulario (con plan de fases para Recepción) */
    public static function form(Form $form): Form
    {
        // Mostrar en el plan solo las fases realmente implementadas (las que tienes creadas)
        $allowedPhaseNames = [
            'Restauración',
            'Empaste',
            'Digitalización',
            'Catalogación',
            'Control de Calidad', // 👈 nombre actual
            'Aprobación final',   // 👈 alias por compatibilidad (si existiera en DB)
            'Disponibilización',
        ];

        return $form->schema([
            Forms\Components\TextInput::make('book_title')
                ->label('Título / Descripción del Libro')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('internal_code')
                ->label('Código Interno')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(100),

            Forms\Components\TextInput::make('provider')
                ->label('Proveedor')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('order_number')
                ->label('N° Pedido')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\TextInput::make('act_number')
                ->label('N° Acta')
                ->required()
                ->maxLength(255),

            // 🔹 Responsable (recepcionista que crea el proceso)
            Forms\Components\Select::make('responsible_id')
                ->label('Responsable')
                ->options(fn () => User::role('Recepcionista')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            // ==========================
            //   Plan del flujo (UI mejorada)
            // ==========================
            Forms\Components\Section::make('Plan del flujo (opcional)')
                ->description('Solo para Recepción. Selecciona las fases por donde pasará este proceso y arrástralas para definir el orden.')
                ->collapsible()
                // ->compact()  // si te da error, quita esta línea (según versión de Filament)
                ->schema([
                    Repeater::make('phasePlan')
                        ->relationship('phasePlan')
                        ->label('Fases seleccionadas')
                        ->orderColumn('sort')               // usa la columna sort en la relación
                        ->reorderable()                     // drag & drop
                        ->reorderableWithButtons()          // y también con botones
                        ->addActionLabel('Agregar fase')
                        ->defaultItems(0)
                        ->minItems(0)
                        ->helperText('Arrastra los elementos para cambiar el orden. No incluyas “Recepción”.')
                        ->schema([
                            Select::make('phase_id')
                                ->label('Fase')
                                ->options(fn () =>
                                    Phase::query()
                                        ->whereIn('name', $allowedPhaseNames)
                                        ->orderBy('order')
                                        ->pluck('name', 'id')
                                )
                                ->searchable()
                                ->required()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        ])
                        ->columns(1),
                ])
                ->visible(fn () => auth()->user()?->hasRole('Recepcionista')),

            Forms\Components\Hidden::make('status')
                ->default('Pendiente'),

            Forms\Components\Hidden::make('phase_id')
                ->default(fn () => Phase::orderBy('order')->first()?->id),
        ]);
    }

    /** ✅ Tabla */
    public static function table(Table $table): Table
    {
        return $table
            // OPTIMIZACIÓN: eager load + columnas mínimas
            ->modifyQueryUsing(function (Builder $query) {
                $query
                    ->with([
                        'responsible:id,name',
                        'phase:id,name',
                    ])
                    ->select([
                        'id',
                        'order_number',
                        'book_title',
                        'provider',
                        'responsible_id',
                        'status',
                        'phase_id',
                        'created_at',
                    ]);
            })
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->label('N° Pedido')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('book_title')->label('Título')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('provider')->label('Proveedor')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('responsible.name')->label('Responsable')->sortable()->searchable(),
                Tables\Columns\BadgeColumn::make('status')->label('Estado')
                    ->colors([
                        'warning' => 'Pendiente',
                        'info'    => 'En Proceso',
                        'success' => 'Finalizado',
                    ]),
                Tables\Columns\TextColumn::make('phase.name')->label('Fase actual')->sortable(),
            ])
            ->filters([
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn () => auth()->user()?->can('editar procesos')),
                Tables\Actions\DeleteAction::make()->visible(fn () => auth()->user()?->can('eliminar procesos')),

                // 🚀 Avanzar fase (respeta el plan del proceso si existe)
                Tables\Actions\Action::make('avanzar')
                    ->label('Avanzar fase')
                    ->icon('lucide-arrow-right-circle')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! $record->isFinalizado())
                    ->action(function (ControlProcess $record) {
                        $user = auth()->user();

                        if (! $user->hasRole('Admin') && ! $user->can('avanzar procesos')) {
                            Notification::make()
                                ->title('No tienes permisos para avanzar esta fase.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Avanzar respetando el plan y dejando como responsable a quien envía
                        $advanced = $record->avanzarAFaseSiguiente($user->id);
                        $record->refresh(); // traer nueva fase/estado

                        // ¿Se finalizó?
                        if (! $advanced) {
                            Notification::make()
                                ->title('El proceso ha sido finalizado.')
                                ->success()
                                ->send();
                            return;
                        }

                        // Nueva fase tras avanzar
                        $newPhaseName = optional($record->phase)->name;

                        // Helper: primer usuario por rol
                        $findUserByRole = function (string $roleName): ?int {
                            return User::role($roleName)->first()?->id;
                        };

                        switch ($newPhaseName) {
                            case 'Restauración':
                                Restoration::firstOrCreate(
                                    ['control_process_id' => $record->id],
                                    ['user_id' => $findUserByRole('Restaurador'), 'status' => 'Pendiente']
                                );
                                Notification::make()->title('Avanzado a Restauración')->success()->send();
                                return redirect()->route('filament.admin.resources.restorations.index');

                            case 'Empaste':
                                Binding::firstOrCreate(
                                    ['control_process_id' => $record->id],
                                    ['user_id' => $findUserByRole('Encuadernador'), 'status' => 'Pendiente']
                                );
                                Notification::make()->title('Avanzado a Encuadernación/Empaste')->success()->send();
                                return redirect()->route('filament.admin.resources.bindings.index');

                            case 'Digitalización':
                                Digitalization::firstOrCreate(
                                    ['control_process_id' => $record->id],
                                    ['user_id' => $findUserByRole('Digitalizador'), 'status' => 'Pendiente']
                                );
                                Notification::make()->title('Avanzado a Digitalización')->success()->send();
                                return redirect()->route('filament.admin.resources.digitalizations.index');

                            case 'Catalogación':
                                $catalogadorId = $findUserByRole('Catalogador');
                                Cataloging::firstOrCreate(
                                    ['control_process_id' => $record->id],
                                    ['user_id' => $catalogadorId, 'status' => 'Pendiente']
                                );
                                if ($catalogadorId) {
                                    $record->update(['responsible_id' => $catalogadorId]);
                                }
                                Notification::make()->title('Avanzado a Catalogación')->success()->send();
                                return redirect()->route('filament.admin.resources.catalogings.index');

                            case 'Control de Calidad': // nombre actual
                            case 'Aprobación final':  // alias por compatibilidad
                                QualityControl::firstOrCreate(
                                    ['control_process_id' => $record->id],
                                    ['user_id' => $findUserByRole('Calidad'), 'status' => 'Pendiente']
                                );
                                Notification::make()->title('Avanzado a Control de Calidad')->success()->send();
                                return redirect()->route('filament.admin.resources.quality-controls.index');

                            case 'Disponibilización':
                                Delivery::firstOrCreate(
                                    ['control_process_id' => $record->id],
                                    ['user_id' => $findUserByRole('Entrega'), 'status' => 'Pendiente']
                                );
                                Notification::make()->title('Avanzado a Entrega final')->success()->send();
                                return redirect()->route('filament.admin.resources.deliveries.index');

                            default:
                                Notification::make()
                                    ->title('Proceso avanzado a la fase: ' . ($newPhaseName ?? '—'))
                                    ->success()
                                    ->send();
                                return;
                        }
                    }),

                // 📄 Exportar
                Tables\Actions\Action::make('export_excel')
                    ->label('Excel')
                    ->icon('lucide-file-down')
                    ->url(fn ($record) => route('process.export.excel', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('lucide-file-text')
                    ->url(fn ($record) => route('process.export.pdf', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('export_acta')
                    ->label('Acta Final')
                    ->icon('lucide-clipboard-check')
                    ->url(fn ($record) => route('process.export.acta', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->status === 'Finalizado'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Información general')
                ->schema([
                    TextEntry::make('order_number')->label('N° Pedido'),
                    TextEntry::make('book_title')->label('Título'),
                    TextEntry::make('provider')->label('Proveedor'),
                    TextEntry::make('responsible.name')->label('Responsable'),
                    TextEntry::make('status')->label('Estado'),
                    TextEntry::make('phase.name')->label('Fase actual'),
                ])->columns(3),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListControlProcesses::route('/'),
            'create' => Pages\CreateControlProcess::route('/create'),
            'edit'   => Pages\EditControlProcess::route('/{record}/edit'),
            'view'   => Pages\ViewControlProcess::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->can('crear procesos');
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->can('editar procesos');
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->can('eliminar procesos');
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->can('ver procesos');
    }
}
