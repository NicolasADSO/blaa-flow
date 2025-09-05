<?php

namespace App\Filament\Resources\ControlProcessResource\Pages;

use App\Filament\Resources\ControlProcessResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class EditControlProcess extends EditRecord
{
    protected static string $resource = ControlProcessResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Resumen del proceso')
                ->schema([
                    TextEntry::make('order_number')->label('Número de Orden'),
                    TextEntry::make('phase.name')->label('Fase actual'),
                    TextEntry::make('status')->label('Estado'),
                    TextEntry::make('responsible')->label('Responsable'),
                    TextEntry::make('start_date')->label('Inicio')->dateTime('d/m/Y H:i'),
                    TextEntry::make('end_date')->label('Finalización')->dateTime('d/m/Y H:i'),
                ])
                ->columns(3),
        ]);
    }
}
