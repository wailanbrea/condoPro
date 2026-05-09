<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Egresos - {{ $month }}/{{ $year }}</title>
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
        <h2>Reporte de Egresos - {{ $month }}/{{ $year }}</h2>
        <div class="condo-name">{{ $condominium->name }} - {{ $condominium->address }}</div>
        <div class="report-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary">
        <h3>Total Egresos</h3>
        <div class="amount">RD$ {{ number_format($totalExpenses, 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Categoría</th>
                <th>Concepto</th>
                <th>Registrado por</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->date ? $expense->date->format('d/m/Y') : '-' }}</td>
                <td>{{ $expense->category->name ?? '-' }}</td>
                <td>{{ $expense->concept }}</td>
                <td>{{ $expense->creator->name ?? '-' }}</td>
                <td class="text-right">RD$ {{ number_format($expense->amount, 2) }}</td>
            </tr>
            @endforeach
            @if($expenses->isEmpty())
            <tr><td colspan="5" class="text-center">No hay egresos registrados para este período</td></tr>
            @endif
        </tbody>
        @if($expenses->isNotEmpty())
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL</td>
                <td class="text-right">RD$ {{ number_format($totalExpenses, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Reporte generado por CondoPro el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>