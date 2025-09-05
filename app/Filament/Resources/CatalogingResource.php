<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatalogingResource\Pages;
use App\Models\Cataloging;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select as FSelect;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CatalogingResource extends Resource
{
    protected static ?string $model = Cataloging::class;

    protected static ?string $navigationIcon = 'lucide-book-open-check';
    protected static ?string $navigationGroup = 'Fases Especializadas';
    protected static ?string $navigationLabel = 'Catalogaciones';

    public static function shouldRegisterNavigation(): bool
    {
        if (! auth()->check()) return false;
        return auth()->user()->hasAnyRole(['Admin', 'Catalogador']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Asignación')
                ->schema([
                    FSelect::make('user_id')
                        ->label('Responsable')
                        ->options(User::role('Catalogador')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                    FSelect::make('status')
                        ->label('Estado')
                        ->options([
                            'Pendiente'  => 'Pendiente',
                            'En Proceso' => 'En Proceso',
                            'Finalizado' => 'Finalizado',
                        ])
                        ->required(),
                    FSelect::make('quality_status')
                        ->label('Calidad')
                        ->options([
                            'Pendiente' => 'Pendiente',
                            'Revisar'   => 'Revisar',
                            'Aprobado'  => 'Aprobado',
                        ])
                        ->default('Pendiente'),
                    DateTimePicker::make('record_completed_at')
                        ->label('Sellado (auto)')
                        ->disabled(),
                ])
                ->columns(4),

            Section::make('Bibliografía')
                ->schema([
                    TextInput::make('title')->label('Título')->maxLength(255)
                        ->required(fn (Get $get) => $get('status') === 'Finalizado'),
                    TextInput::make('subtitle')->label('Subtítulo')->maxLength(255),
                    TagsInput::make('authors')->label('Autores')->placeholder('Agregar autor'),
                    TextInput::make('publisher')->label('Editorial')->maxLength(255),
                    TextInput::make('place_of_publication')->label('Lugar de publicación')->maxLength(255),
                    TextInput::make('edition')->label('Edición')->maxLength(100),
                    TextInput::make('publication_year')->label('Año')->numeric()->minValue(0)->maxValue(2100),
                    TextInput::make('isbn')->label('ISBN')->maxLength(32),
                    FSelect::make('language')->label('Idioma')->options([
                        'es' => 'Español',
                        'en' => 'Inglés',
                        'fr' => 'Francés',
                        'pt' => 'Portugués',
                        'de' => 'Alemán',
                        'it' => 'Italiano',
                    ])->searchable(),
                ])
                ->columns(3),

            Section::make('Clasificación y Signatura')
                ->schema([
                    FSelect::make('classification_system')->label('Sistema')->options([
                        'DDC'   => 'Dewey (DDC)',
                        'LCC'   => 'Library of Congress (LCC)',
                        'UDC'   => 'Universal (UDC)',
                        'LOCAL' => 'Local',
                    ])
                    ->required(fn (Get $get) => $get('status') === 'Finalizado'),
                    TextInput::make('classification_code')->label('Código de clasificación')->maxLength(100),
                    TextInput::make('call_number')->label('Signatura topográfica')->maxLength(150)
                        ->required(fn (Get $get) => $get('status') === 'Finalizado'),
                    TextInput::make('shelf_location')->label('Ubicación en estantería')->maxLength(150),
                ])
                ->columns(4),

            Section::make('Materias / Descriptores')
                ->schema([
                    TagsInput::make('subjects')->label('Materias')->placeholder('Agregar materia'),
                    Textarea::make('descriptors')->label('Descriptores')->rows(3),
                ])
                ->columns(1),

            Section::make('Descripción física')
                ->schema([
                    TextInput::make('pages')->label('Páginas')->numeric()->minValue(0)->maxValue(50000),
                    TextInput::make('dimensions')->label('Dimensiones')->placeholder('ej: 23 cm')->maxLength(50),
                    FSelect::make('material_type')->label('Tipo de material')->options([
                        'Libro'      => 'Libro',
                        'Revista'    => 'Revista',
                        'Manuscrito' => 'Manuscrito',
                        'Mapa'       => 'Mapa',
                        'Foto'       => 'Foto',
                        'Otro'       => 'Otro',
                    ]),
                    TextInput::make('barcode')->label('Código de barras')->maxLength(100),
                ])
                ->columns(4),

            Section::make('Adjuntos / Notas')
                ->schema([
                    FileUpload::make('cover_image_path')
                        ->label('Portada')
                        ->image()
                        ->directory('catalog/portadas')
                        ->visibility('public'),
                    Textarea::make('notes')->label('Notas internas')->rows(4),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('process.order_number')
                    ->label('N° Pedido')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsable')->sortable()->searchable(),
                Tables\Columns\BadgeColumn::make('status')->label('Estado')->colors([
                    'warning' => 'Pendiente',
                    'info'    => 'En Proceso',
                    'success' => 'Finalizado',
                ]),
                Tables\Columns\BadgeColumn::make('quality_status')->label('Calidad')->colors([
                    'warning' => 'Pendiente',
                    'danger'  => 'Revisar',
                    'success' => 'Aprobado',
                ]),
                Tables\Columns\ImageColumn::make('cover_image_path')
                    ->label('Portada')->circular()->height(36),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Pendiente'  => 'Pendiente',
                        'En Proceso' => 'En Proceso',
                        'Finalizado' => 'Finalizado',
                    ]),
                Tables\Filters\SelectFilter::make('quality_status')
                    ->label('Calidad')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Revisar'   => 'Revisar',
                        'Aprobado'  => 'Aprobado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // 👇 Edit solo si el proceso sigue en la fase "Catalogación"
                Tables\Actions\EditAction::make()
                    ->visible(fn (Cataloging $record) =>
                        optional($record->process?->phase)->name === 'Catalogación'
                    ),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCatalogings::route('/'),
            'create' => Pages\CreateCataloging::route('/create'),
            'edit'   => Pages\EditCataloging::route('/{record}/edit'),
            'view'   => Pages\ViewCataloging::route('/{record}'),
        ];
    }

    // 🔒 Bloquea la edición por ruta si el proceso ya cambió de fase
    public static function canEdit($record): bool
    {
        if (! auth()->check() || ! $record) return false;

        // Solo editable mientras el ControlProcess está en "Catalogación"
        return optional($record->process?->phase)->name === 'Catalogación';
    }
}
