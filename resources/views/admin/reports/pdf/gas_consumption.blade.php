<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consumo de Gas - {{ $month }}/{{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, Arial, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header { text-align: center; padding-bottom: 16px; border-bottom: 2px solid #163c6e; margin-bottom: 16px; }
        .header h1 { font-size: 20px; color: #163c6e; margin-bottom: 2px; }
        .header h2 { font-size: 14px; color: #3a3a5a; font-weight: normal; }
        .header .condo-name { font-size: 13px; color: #555; margin-top: 4px; }
        .header .report-date { font-size: 10px; color: #888; margin-top: 4px; }
        .summary-row { width: 100%; margin-bottom: 16px; }
        .summary-row table { border-collapse: separate; border-spacing: 6px; width: 100%; }
        .summary-row td { padding: 10px; border-radius: 6px; text-align: center; vertical-align: top; }
        .summary-card.m3 { background: #e3f2fd; }
        .summary-card.gal { background: #e8f5e9; }
        .summary-card.total { background: #fff3e0; }
        .summary-card h4 { font-size: 10px; color: #555; margin-bottom: 4px; }
        .summary-card .value { font-size: 18px; font-weight: bold; color: #163c6e; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #163c6e; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f9f9fc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; background: #eef2f9 !important; border-top: 2px solid #163c6e; }
        .footer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #ccc; font-size: 9px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CondoPro</h1>
        <h2>Reporte de Consumo de Gas - {{ $month }}/{{ $year }}</h2>
        <div class="condo-name">{{ $condominium->name }} - {{ $condominium->address }}</div>
        <div class="report-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary-row">
        <table>
            <tr>
                <td class="summary-card m3">
                    <h4>Consumo Total (m³)</h4>
                    <div class="value">{{ number_format($totalConsumption, 2) }}</div>
                </td>
                <td class="summary-card gal">
                    <h4>Total Galones</h4>
                    <div class="value">{{ number_format($totalGallons, 2) }}</div>
                </td>
                <td class="summary-card total">
                    <h4>Costo Total Gas</h4>
                    <div class="value">RD$ {{ number_format($totalGas, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    @if($readings->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th>Apartamento</th>
                <th>Lectura Inicial</th>
                <th>Lectura Final</th>
                <th class="text-right">Consumo (m³)</th>
                <th class="text-right">Galones</th>
                <th class="text-right">Precio/Gal</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($readings as $reading)
            <tr>
                <td>{{ $reading->apartment->number ?? '-' }}</td>
                <td>{{ number_format($reading->reading_initial, 2) }}</td>
                <td>{{ number_format($reading->reading_final, 2) }}</td>
                <td class="text-right">{{ number_format($reading->consumption_m3, 2) }}</td>
                <td class="text-right">{{ number_format($reading->gallons, 2) }}</td>
                <td class="text-right">RD$ {{ number_format($reading->price_per_gallon, 2) }}</td>
                <td class="text-right">RD$ {{ number_format($reading->total_gas, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTALES</td>
                <td class="text-right">{{ number_format($totalConsumption, 2) }}</td>
                <td class="text-right">{{ number_format($totalGallons, 2) }}</td>
                <td></td>
                <td class="text-right">RD$ {{ number_format($totalGas, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <p style="text-align: center; padding: 24px; color: #888;">No hay lecturas de gas registradas para este período.</p>
    @endif

    <div class="footer">
        Reporte generado por CondoPro el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>