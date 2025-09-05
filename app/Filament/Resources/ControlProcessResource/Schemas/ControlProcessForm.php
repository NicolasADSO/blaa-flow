<?php

namespace App\Filament\Resources\ControlProcessResource\Schemas;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Checkbox;

class ControlProcessForm
{
    public static function configure(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            /* 🔹 Identificación del Proceso → editable solo en Recepción */
            Fieldset::make('Identificación del Proceso')
                ->schema([
                    TextInput::make('book_title')
                        ->label('Título / Descripción del Libro')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    TextInput::make('internal_code')
                        ->label('Código Interno')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100)
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    TextInput::make('provider')
                        ->label('Proveedor')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    TextInput::make('order_number')
                        ->label('N° Pedido')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    TextInput::make('act_number')
                        ->label('N° Acta')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    TextInput::make('responsible_id')
                        ->label('Responsable')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),
                ])
                ->columns(3),

            /* 🔹 Valores Económicos → editables solo en Recepción */
            Fieldset::make('Valores Económicos')
                ->schema([
                    TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->prefix('$')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $subtotal = is_numeric($state) ? (float) $state : 0;
                            $iva = round($subtotal * 0.19, 2);
                            $set('iva', $iva);
                            $set('total', $subtotal + $iva);
                        })
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    TextInput::make('iva')
                        ->label('IVA (19%)')
                        ->prefix('$')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(true),

                    TextInput::make('total')
                        ->label('Total')
                        ->prefix('$')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(true)
                        ->reactive()
                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            $subtotal = is_numeric($get('subtotal')) ? (float) $get('subtotal') : 0;
                            $iva = is_numeric($get('iva')) ? (float) $get('iva') : 0;
                            $set('total', $subtotal + $iva);
                        }),
                ])
                ->columns(3),

            /* 🔹 Fechas → Recepción y Empaste */
            Fieldset::make('Fechas')
                ->schema([
                    DatePicker::make('reception_date')
                        ->label('Fecha de Recepción')
                        ->required()
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    DatePicker::make('delivery_date')
                        ->label('Fecha de Entrega')
                        ->afterOrEqual('reception_date')
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    DatePicker::make('invoice_date')
                        ->label('Fecha de Factura')
                        ->nullable()
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    DatePicker::make('payment_date')
                        ->label('Fecha de Pago')
                        ->nullable()
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Recepción'),

                    DatePicker::make('start_date')
                        ->label('Fecha de Inicio')
                        ->default(now())
                        ->disabled()
                        ->dehydrated(true),

                    DatePicker::make('end_date')
                        ->label('Fecha de Fin')
                        ->afterOrEqual('start_date')
                        ->nullable()
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Empaste'),
                ])
                ->columns(3),

            /* 🔹 Fase actual → no editable manualmente */
            Fieldset::make('Fase del Proceso')
                ->schema([
                    Select::make('phase_id')
                        ->label('Fase actual')
                        ->relationship('phase', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(1)
                        ->reactive()
                        ->disabled(),
                ])
                ->columns(1),

            /* 🔹 Evidencia / Acción según fase */
            Fieldset::make('Evidencia / Acción de la fase')
                ->schema([
                    // Restauración y Catalogación (formularios)
                    TextInput::make('extra_field')
                        ->label('Información adicional')
                        ->visible(fn ($get) => optional($get('phase'))->input_type === 'form'),

                    // Digitalización / Empaste / Calidad (archivos)
                    FileUpload::make('evidence_file')
                        ->label('Subir evidencia')
                        ->directory('procesos')
                        ->visible(fn ($get) => optional($get('phase'))->input_type === 'file'),

                    // Control de Calidad y Entrega Final
                    Checkbox::make('phase_checked')
                        ->label('Confirmar fase completada')
                        ->visible(fn ($get) => optional($get('phase'))->input_type === 'check'),
                ])
                ->columns(1),

            /* 🔹 Estado y Observaciones */
            Fieldset::make('Estado y Observaciones')
                ->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'Pendiente'   => 'Pendiente',
                            'En Proceso'  => 'En Proceso',
                            'Finalizado'  => 'Finalizado',
                        ])
                        ->default('Pendiente')
                        ->required()
                        ->disabled(fn ($get) => ! in_array(optional($get('phase'))->name, [
                            'Recepción', 'Empaste', 'Control de Calidad', 'Disponibilización'
                        ])),

                    TextInput::make('real_duration')
                        ->label('Duración real (días)')
                        ->numeric()
                        ->minValue(0)
                        ->suffix('días')
                        ->disabled(fn ($get) => optional($get('phase'))->name !== 'Empaste'),

                    Textarea::make('observations')
                        ->label('Observaciones')
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
