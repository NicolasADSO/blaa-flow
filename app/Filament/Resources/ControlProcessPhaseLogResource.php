<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControlProcessPhaseLogResource\Pages;
use App\Models\ControlProcessPhaseLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ControlProcessPhaseLogResource extends Resource
{
    protected static ?string $model = ControlProcessPhaseLog::class;

    protected static ?string $navigationIcon = 'lucide-clipboard-check';
    protected static ?string $navigationGroup = 'Seguimiento';
    protected static ?string $navigationLabel = 'Historial de Procesos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('control_process_id')
                ->label('Proceso')
                ->relationship('controlProcess', 'order_number')
                ->searchable()
                ->preload() // 🔹 carga registros al desplegar
                ->required(),

            Forms\Components\Select::make('phase_id')
                ->label('Fase')
                ->relationship('phase', 'name')
                ->searchable()
                ->preload() // 🔹 carga registros al desplegar
                ->required(),

            Forms\Components\Select::make('user_id')
                ->label('Usuario')
                ->relationship('user', 'name')
                ->searchable()
                ->preload(), // 🔹 carga registros al desplegar

            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'Pendiente'   => 'Pendiente',
                    'En Proceso'  => 'En Proceso',
                    'Finalizado'  => 'Finalizado',
                ])
                ->default('Pendiente')
                ->required(),

            Forms\Components\FileUpload::make('file_path')
                ->label('Archivo adjunto')
                ->directory('phase-logs')
                ->nullable(),

            Forms\Components\Textarea::make('notes')
                ->label('Notas / Comentarios')
                ->columnSpanFull(),

            Forms\Components\DateTimePicker::make('started_at')
                ->label('Inicio'),

            Forms\Components\DateTimePicker::make('finished_at')
                ->label('Fin'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('controlProcess.order_number')
                    ->label('Proceso')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('phase.name')
                    ->label('Fase')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'danger'  => 'Pendiente',
                        'warning' => 'En Proceso',
                        'success' => 'Finalizado',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('finished_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListControlProcessPhaseLogs::route('/'),
            'create' => Pages\CreateControlProcessPhaseLog::route('/create'),
            'edit'   => Pages\EditControlProcessPhaseLog::route('/{record}/edit'),
        ];
    }
}
