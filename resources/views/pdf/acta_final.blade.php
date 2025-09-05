<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta Final - {{ $process->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        /* 🔹 Portada con margen superior */
        .portada {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;  /* ya no centrado */
            align-items: center;
            height: 100vh;
            text-align: center;
            padding-top: 200px; /* 🔹 ajusta este valor (ej: 150px, 250px) */
            page-break-after: always; /* salta a nueva página después */
        }

        .portada h1 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        .portada p {
            margin: 6px 0;
        }

        .fase {
            margin-top: 20px;
            border: 1px solid #ccc;
            padding: 10px;
            page-break-inside: avoid;
        }

        .fase h3 {
            background: #f0f0f0;
            padding: 5px;
            border-bottom: 1px solid #ccc;
            margin-top: 0;
        }

        .images img {
            max-width: 200px;
            margin: 5px;
            border: 1px solid #666;
            display: inline-block;
            page-break-inside: avoid;
        }

        .firmas {
            margin-top: 50px;
            width: 100%;
            text-align: center;
        }

        .firmas td {
            width: 50%;
            padding-top: 60px;
        }
    </style>
</head>
<body>
    {{-- 🔹 Portada más abajo --}}
    <div class="portada">
        <h1>📑 Acta Final del Proceso</h1>
        <p><strong>Número de Pedido:</strong> {{ $process->order_number }}</p>
        <p><strong>Título / Descripción:</strong> {{ $process->book_title }}</p>
        <p><strong>Proveedor:</strong> {{ $process->provider }}</p>
        <p><strong>Responsable:</strong> {{ $process->responsible }}</p>
        <p><strong>Fecha de Inicio:</strong> {{ optional($process->start_date)->format('d/m/Y H:i') }}</p>
        <p><strong>Fecha de Finalización:</strong> {{ optional($process->end_date)->format('d/m/Y H:i') }}</p>
        <p><strong>Estado Final:</strong> {{ $process->status }}</p>
    </div>

    {{-- ================= Restauración ================= --}}
    @if($process->restorations->count())
        <div class="fase">
            <h3>🛠️ Restauración</h3>
            @foreach($process->restorations as $r)
                <p><strong>Usuario:</strong> {{ $r->user->name ?? 'No asignado' }}</p>
                <p><strong>Tipo de daño:</strong> {{ $r->damage_type }}</p>
                <p><strong>Técnica usada:</strong> {{ $r->technique_used }}</p>
                <p><strong>Materiales:</strong> {{ $r->materials }}</p>
                <p><strong>Estado:</strong> {{ $r->status }}</p>
                <p><strong>Observaciones:</strong> {{ $r->notes ?? '—' }}</p>
                @if($r->before_photo || $r->after_photo)
                    <div class="images">
                        @if($r->before_photo)
                            <p>📷 Antes:</p>
                            @php $path = storage_path('app/public/'.$r->before_photo); @endphp
                            @if(file_exists($path))
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($path)) }}">
                            @endif
                        @endif
                        @if($r->after_photo)
                            <p>📷 Después:</p>
                            @php $path = storage_path('app/public/'.$r->after_photo); @endphp
                            @if(file_exists($path))
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($path)) }}">
                            @endif
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- ================= Encuadernación / Empaste ================= --}}
    @if($process->bindings->count())
        <div class="fase">
            <h3>📚 Encuadernación / Empaste</h3>
            @foreach($process->bindings as $b)
                <p><strong>Usuario:</strong> {{ $b->user->name ?? 'No asignado' }}</p>
                <p><strong>Tipo de empaste:</strong> {{ $b->binding_type }}</p>
                <p><strong>Materiales:</strong> {{ $b->materials }}</p>
                <p><strong>Estado:</strong> {{ $b->status }}</p>
                <p><strong>Notas:</strong> {{ $b->notes ?? '—' }}</p>
                @if($b->cover_photo)
                    <div class="images">
                        <p>📷 Foto final:</p>
                        @php $path = storage_path('app/public/'.$b->cover_photo); @endphp
                        @if(file_exists($path))
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($path)) }}">
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- ================= Digitalización ================= --}}
    @if($process->digitalizations->count())
        <div class="fase">
            <h3>💻 Digitalización</h3>
            @foreach($process->digitalizations as $d)
                <p><strong>Usuario:</strong> {{ $d->user->name ?? 'No asignado' }}</p>
                <p><strong>Estado:</strong> {{ $d->status }}</p>
                <p><strong>Observaciones:</strong> {{ $d->notes ?? '—' }}</p>
            @endforeach
        </div>
    @endif

    {{-- ================= Control de Calidad ================= --}}
    @if($process->qualityControls->count())
        <div class="fase">
            <h3>✅ Control de Calidad</h3>
            @foreach($process->qualityControls as $qc)
                <p><strong>Usuario:</strong> {{ $qc->user->name ?? 'No asignado' }}</p>
                <p><strong>Estado:</strong> {{ $qc->status }}</p>
                <p><strong>Observaciones:</strong> {{ $qc->notes ?? '—' }}</p>
            @endforeach
        </div>
    @endif

    {{-- ================= Entrega Final ================= --}}
    @if($process->deliveries->count())
        <div class="fase">
            <h3>📦 Entrega Final</h3>
            @foreach($process->deliveries as $del)
                <p><strong>Usuario:</strong> {{ $del->user->name ?? 'No asignado' }}</p>
                <p><strong>Entregado a:</strong> {{ $del->delivered_to }}</p>
                <p><strong>Fecha:</strong> {{ optional($del->delivery_date)->format('d/m/Y') }}</p>
                <p><strong>Estado:</strong> {{ $del->status }}</p>
                <p><strong>Observaciones:</strong> {{ $del->notes ?? '—' }}</p>
            @endforeach
        </div>
    @endif

    <table class="firmas">
        <tr>
            <td>_____________________________<br>Firma Responsable</td>
            <td>_____________________________<br>Firma Biblioteca</td>
        </tr>
    </table>
</body>
</html>
