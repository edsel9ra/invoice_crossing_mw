<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Boleta #{{ $ticket->ticket_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            padding: 2rem;
        }
        .ticket {
            width: 320px;
            background: #fff;
            border: 2px dashed #d97706;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .header h1 {
            font-size: 1.5rem;
            color: #92400e;
        }
        .header p {
            font-size: 0.75rem;
            color: #b45309;
            margin-top: 0.25rem;
        }
        .code {
            text-align: center;
            margin: 1rem 0;
        }
        .code .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #1e293b;
            letter-spacing: 0.1em;
        }
        .code .label {
            font-size: 0.7rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }
        .info {
            border-top: 1px dashed #e2e8f0;
            border-bottom: 1px dashed #e2e8f0;
            padding: 0.75rem 0;
            margin: 0.75rem 0;
            font-size: 0.8rem;
        }
        .info .row {
            display: flex;
            justify-content: space-between;
            padding: 0.25rem 0;
        }
        .info .label {
            color: #64748b;
        }
        .info .value {
            color: #1e293b;
            font-weight: 600;
            text-align: right;
        }
        .footer {
            text-align: center;
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e2e8f0;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .ticket { border: 1px solid #ccc; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1>Concurso Mister Wings</h1>
            <p>Boleta de participación</p>
        </div>

        <div class="code">
            <div class="label">N° de Boleta</div>
            <div class="number">{{ $ticket->ticket_code }}</div>
        </div>

        <div class="info">
            <div class="row">
                <span class="label">Cliente</span>
                <span class="value">{{ $client->name }}</span>
            </div>
            <div class="row">
                <span class="label">Documento</span>
                <span class="value">{{ $client->doc_num }}</span>
            </div>
            <div class="row">
                <span class="label">Factura</span>
                <span class="value">{{ $ticket->invoice_number }}</span>
            </div>
            <div class="row">
                <span class="label">Sede</span>
                <span class="value">{{ $ticket->branch_name }}</span>
            </div>
            <div class="row">
                <span class="label">Item</span>
                <span class="value">{{ $ticket->detail?->matched_item_name ?: $ticket->item_code }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Fecha: {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
            <p style="margin-top:0.25rem;">Gracias por su participación</p>
        </div>
    </div>
</body>
</html>
