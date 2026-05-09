<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Balance Mensual - {{ $month }}/{{ $year }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, Arial, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header { text-align: center; padding-bottom: 16px; border-bottom: 2px solid #163c6e; margin-bottom: 16px; }
        .header h1 { font-size: 20px; color: #163c6e; margin-bottom: 2px; }
        .header h2 { font-size: 14px; color: #3a3a5a; font-weight: normal; }
        .header .condo-name { font-size: 13px; color: #555; margin-top: 4px; }
        .header .report-date { font-size: 10px; color: #888; margin-top: 4px; }
        .balance-summary { width: 100%; margin-bottom: 16px; }
        .balance-summary table { border-collapse: separate; border-spacing: 6px; width: 100%; }
        .balance-summary td { padding: 12px; border-radius: 6px; text-align: center; vertical-align: top; }
        .balance-card.income { background: #e8f5e9; }
        .balance-card.expense { background: #fdecea; }
        .balance-card.result { background: {{ $balance >= 0 ? '#e3f2fd' : '#fff3e0' }}; }
        .balance-card h4 { font-size: 11px; margin-bottom: 4px; }
        .balance-card .amount { font-size: 18px; font-weight: bold; }
        .balance-card.income h4, .balance-card.income .amount { color: #27ae60; }
        .balance-card.expense h4, .balance-card.expense .amount { color: #c0392b; }
        .balance-card.result h4, .balance-card.result .amount { color: {{ $balance >= 0 ? '#163c6e' : '#e67e22' }}; }
        h3 { font-size: 13px; color: #163c6e; margin: 16px 0 8px 0; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
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
        <h2>Balance Mensual - {{ $month }}/{{ $year }}</h2>
        <div class="condo-name">{{ $condominium->name }} - {{ $condominium->address }}</div>
        <div class="report-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="balance-summary">
        <table>
            <tr>
                <td class="balance-card income">
                    <h4>Total Ingresos</h4>
                    <div class="amount">RD$ {{ number_format($totalIncome, 2) }}</div>
                </td>
                <td class="balance-card expense">
                    <h4>Total Egresos</h4>
                    <div class="amount">RD$ {{ number_format($totalExpenses, 2) }}</div>
                </td>
                <td class="balance-card result">
                    <h4>Balance</h4>
                    <div class="amount">RD$ {{ number_format(abs($balance), 2) }} {{ $balance >= 0 ? '(Superávit)' : '(Déficit)' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h3>Detalle de Ingresos</h3>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Apartamento</th>
                <th>Referencia</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incomes as $income)
            <tr>
                <td>{{ $income->payment_date ? $income->payment_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $income->apartment->number ?? '-' }}</td>
                <td>{{ $income->reference_number ?? '-' }}</td>
                <td class="text-right">RD$ {{ number_format($income->amount, 2) }}</td>
            </tr>
            @endforeach
            @if($incomes->isEmpty())
            <tr><td colspan="4" class="text-center">No hay ingresos para este período</td></tr>
            @endif
        </tbody>
    </table>

    <h3>Detalle de Egresos</h3>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Categoría</th>
                <th>Concepto</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->date ? $expense->date->format('d/m/Y') : '-' }}</td>
                <td>{{ $expense->category->name ?? '-' }}</td>
                <td>{{ $expense->concept }}</td>
                <td class="text-right">RD$ {{ number_format($expense->amount, 2) }}</td>
            </tr>
            @endforeach
            @if($expenses->isEmpty())
            <tr><td colspan="4" class="text-center">No hay egresos para este período</td></tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Reporte generado por CondoPro el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>