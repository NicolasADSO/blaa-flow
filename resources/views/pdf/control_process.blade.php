<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proceso {{ $process->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 4px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 6px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📑 Reporte del Proceso</h1>
        <p><strong>Número de Pedido:</strong> {{ $process->order_number }}</p>
    </div>

    <div class="info">
        <p><strong>Título / Descripción:</strong> {{ $process->book_title }}</p>
        <p><strong>Proveedor:</strong> {{ $process->provider }}</p>
        <p><strong>Responsable:</strong> {{ $process->responsible }}</p>
        <p><strong>Fase actual:</strong> {{ $process->phase?->name }}</p>
        <p><strong>Estado:</strong> {{ $process->status }}</p>
        <p><strong>Fecha de Inicio:</strong> {{ optional($process->start_date)->format('d/m/Y H:i') }}</p>
        <p><strong>Fecha de Finalización:</strong> {{ optional($process->end_date)->format('d/m/Y H:i') }}</p>
    </div>

    <h3>📌 Historial de fases</h3>
    <table>
        <thead>
            <tr>
                <th>Fase</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($process->phaseLogs as $log)
                <tr>
                    <td>{{ $log->phase?->name ?? '—' }}</td>
                    <td>{{ $log->user?->name ?? 'Sistema' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
