<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Facturas - {{ $month }}/{{ $year }}</title>
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
        .summary-card.paid { background: #e8f5e9; }
        .summary-card.pending { background: #fdecea; }
        .summary-card h4 { font-size: 10px; margin-bottom: 4px; }
        .summary-card .value { font-size: 18px; font-weight: bold; }
        .summary-card.paid h4, .summary-card.paid .value { color: #27ae60; }
        .summary-card.pending h4, .summary-card.pending .value { color: #c0392b; }
        h3 { font-size: 13px; color: #163c6e; margin: 16px 0 8px 0; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #163c6e; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f9f9fc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; background: #eef2f9 !important; border-top: 2px solid #163c6e; }
        .status-paid { background: #e8f5e9; color: #27ae60; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .status-pending { background: #fdecea; color: #c0392b; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .status-partial { background: #fff3e0; color: #e67e22; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .footer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #ccc; font-size: 9px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CondoPro</h1>
        <h2>Estado de Facturas - {{ $month }}/{{ $year }}</h2>
        <div class="condo-name">{{ $condominium->name }} - {{ $condominium->address }}</div>
        <div class="report-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary-row">
        <table>
            <tr>
                <td class="summary-card paid">
                    <h4>Total Pagado</h4>
                    <div class="value">RD$ {{ number_format($totalPaid, 2) }}</div>
                </td>
                <td class="summary-card pending">
                    <h4>Total Pendiente</h4>
                    <div class="value">RD$ {{ number_format($totalPending, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h3>Facturas</h3>
    @if($bills->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th>Apartamento</th>
                <th>Período</th>
                <th>Subtotal</th>
                <th>Balance Anterior</th>
                <th>Total</th>
                <th>Vencimiento</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bills as $bill)
            <tr>
                <td>{{ $bill->apartment->number ?? '-' }}</td>
                <td>{{ $bill->billing_month }}/{{ $bill->billing_year }}</td>
                <td>RD$ {{ number_format($bill->subtotal, 2) }}</td>
                <td>RD$ {{ number_format($bill->previous_balance, 2) }}</td>
                <td>RD$ {{ number_format($bill->total, 2) }}</td>
                <td>{{ $bill->due_date ? $bill->due_date->format('d/m/Y') : '-' }}</td>
                <td>
                    @if($bill->status === 'paid')
                        <span class="status-paid">PAGADA</span>
                    @elseif($bill->status === 'partial')
                        <span class="status-partial">PARCIAL</span>
                    @else
                        <span class="status-pending">{{ strtoupper($bill->status) }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTALES</td>
                <td>RD$ {{ number_format($bills->sum('subtotal'), 2) }}</td>
                <td>RD$ {{ number_format($bills->sum('previous_balance'), 2) }}</td>
                <td>RD$ {{ number_format($bills->sum('total'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    @else
    <p style="text-align: center; padding: 24px; color: #888;">No hay facturas registradas para este período.</p>
    @endif

    <div class="footer">
        Reporte generado por CondoPro el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>