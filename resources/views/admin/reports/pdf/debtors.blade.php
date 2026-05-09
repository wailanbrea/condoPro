<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Deudores</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, Arial, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header { text-align: center; padding-bottom: 16px; border-bottom: 2px solid #163c6e; margin-bottom: 16px; }
        .header h1 { font-size: 20px; color: #163c6e; margin-bottom: 2px; }
        .header h2 { font-size: 14px; color: #3a3a5a; font-weight: normal; }
        .header .condo-name { font-size: 13px; color: #555; margin-top: 4px; }
        .header .report-date { font-size: 10px; color: #888; margin-top: 4px; }
        .summary { background: #fdecea; padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: center; }
        .summary h3 { color: #c0392b; font-size: 14px; margin-bottom: 4px; }
        .summary .amount { font-size: 22px; color: #c0392b; font-weight: bold; }
        .summary .count { font-size: 11px; color: #888; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #163c6e; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f9f9fc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; background: #eef2f9 !important; border-top: 2px solid #163c6e; }
        .debt { color: #c0392b; font-weight: bold; }
        .footer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #ccc; font-size: 9px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CondoPro</h1>
        <h2>Reporte de Deudores</h2>
        <div class="condo-name">{{ $condominium->name }} - {{ $condominium->address }}</div>
        <div class="report-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary">
        <h3>Deuda Total</h3>
        <div class="amount">RD$ {{ number_format(abs($totalDebt), 2) }}</div>
        <div class="count">{{ $apartments->count() }} apartamento(s) con deuda</div>
    </div>

    @if($apartments->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th>Apartamento</th>
                <th>Propietario</th>
                <th>Residente(s)</th>
                <th class="text-right">Deuda</th>
            </tr>
        </thead>
        <tbody>
            @foreach($apartments as $apartment)
            <tr>
                <td>{{ $apartment->number }}</td>
                <td>{{ $apartment->owner_name }}</td>
                <td>{{ $apartment->users->pluck('name')->join(', ') ?: '-' }}</td>
                <td class="text-right debt">RD$ {{ number_format(abs($apartment->balance), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL DEUDA</td>
                <td class="text-right debt">RD$ {{ number_format(abs($totalDebt), 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <p style="text-align: center; padding: 24px; color: #888;">No hay apartamentos con deuda pendiente.</p>
    @endif

    <div class="footer">
        Reporte generado por CondoPro el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>