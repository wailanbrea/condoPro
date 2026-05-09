<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuotas Extraordinarias</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, Arial, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header { text-align: center; padding-bottom: 16px; border-bottom: 2px solid #163c6e; margin-bottom: 16px; }
        .header h1 { font-size: 20px; color: #163c6e; margin-bottom: 2px; }
        .header h2 { font-size: 14px; color: #3a3a5a; font-weight: normal; }
        .header .condo-name { font-size: 13px; color: #555; margin-top: 4px; }
        .header .report-date { font-size: 10px; color: #888; margin-top: 4px; }
        h3 { font-size: 13px; color: #163c6e; margin: 16px 0 8px 0; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .charge-card { border: 1px solid #ddd; border-radius: 6px; margin-bottom: 16px; overflow: hidden; }
        .charge-header { background: #163c6e; color: #fff; padding: 8px 12px; }
        .charge-header h4 { font-size: 13px; margin-bottom: 2px; }
        .charge-header .meta { font-size: 10px; opacity: 0.85; }
        .charge-body { padding: 12px; }
        .charge-body .detail { margin-bottom: 4px; }
        .charge-body .detail-label { font-weight: bold; color: #3a3a5a; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #f0f2f8; color: #3a3a5a; padding: 5px 8px; text-align: left; font-size: 10px; }
        td { padding: 4px 8px; border-bottom: 1px solid #e0e0e0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .status-active { background: #e8f5e9; color: #27ae60; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .footer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #ccc; font-size: 9px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CondoPro</h1>
        <h2>Cuotas Extraordinarias Activas</h2>
        <div class="condo-name">{{ $condominium->name }} - {{ $condominium->address }}</div>
        <div class="report-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    @if($charges->isNotEmpty())
        @foreach($charges as $charge)
        <div class="charge-card">
            <div class="charge-header">
                <h4>{{ $charge->title }}</h4>
                <div class="meta">
                    Inicio: {{ $charge->start_month }}/{{ $charge->start_year }} |
                    Cuotas: {{ $charge->installments_count }} |
                    Distribución: {{ $charge->distribution_type === 'equal' ? 'Igualitaria' : ucfirst($charge->distribution_type) }}
                </div>
            </div>
            <div class="charge-body">
                @if($charge->description)
                <div class="detail"><span class="detail-label">Descripción:</span> {{ $charge->description }}</div>
                @endif
                <div class="detail"><span class="detail-label">Monto Total:</span> RD$ {{ number_format($charge->total_amount, 2) }}</div>
                <div class="detail"><span class="detail-label">Estado:</span> <span class="status-active">ACTIVA</span></div>

                @if($charge->extraChargeApartments->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th>Apartamento</th>
                            <th class="text-right">Monto Asignado</th>
                            <th class="text-right">Monto Mensual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($charge->extraChargeApartments as $eca)
                        <tr>
                            <td>{{ $eca->apartment->number ?? '-' }}</td>
                            <td class="text-right">RD$ {{ number_format($eca->assigned_amount, 2) }}</td>
                            <td class="text-right">RD$ {{ number_format($eca->monthly_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
        @endforeach
    @else
    <p style="text-align: center; padding: 24px; color: #888;">No hay cuotas extraordinarias activas.</p>
    @endif

    <div class="footer">
        Reporte generado por CondoPro el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>