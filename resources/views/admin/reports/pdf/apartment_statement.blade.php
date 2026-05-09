<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta - Apartamento {{ $apartment->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, Arial, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header { text-align: center; padding-bottom: 16px; border-bottom: 2px solid #163c6e; margin-bottom: 16px; }
        .header h1 { font-size: 20px; color: #163c6e; margin-bottom: 2px; }
        .header h2 { font-size: 14px; color: #3a3a5a; font-weight: normal; }
        .header .condo-name { font-size: 13px; color: #555; margin-top: 4px; }
        .header .report-date { font-size: 10px; color: #888; margin-top: 4px; }
        .summary { background: #f4f6fb; padding: 12px; border-radius: 6px; margin-bottom: 16px; }
        .summary-row { margin-bottom: 6px; }
        .summary-row table { width: 100%; }
        .summary-row td { padding: 2px 0; }
        .summary-row .col-label { width: 40%; font-weight: bold; color: #3a3a5a; }
        .summary-row .col-value { width: 60%; text-align: right; }
        .summary-label { font-weight: bold; color: #3a3a5a; }
        .summary-value { color: #163c6e; font-weight: bold; }
        .negative { color: #c0392b; }
        .positive { color: #27ae60; }
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
        <h2>Estado de Cuenta - Apartamento {{ $apartment->number }}</h2>
        <div class="condo-name">{{ $condominium->name }} - {{ $condominium->address }}</div>
        <div class="report-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary">
        <div class="summary-row"><table><tr>
            <td class="col-label">Propietario:</td>
            <td class="col-value">{{ $apartment->owner_name }}</td>
        </tr></table></div>
        <div class="summary-row"><table><tr>
            <td class="col-label">Apartamento:</td>
            <td class="col-value">{{ $apartment->number }}</td>
        </tr></table></div>
        <div class="summary-row"><table><tr>
            <td class="col-label">Total Facturado:</td>
            <td class="col-value summary-value">RD$ {{ number_format($totalBills, 2) }}</td>
        </tr></table></div>
        <div class="summary-row"><table><tr>
            <td class="col-label">Total Pagado:</td>
            <td class="col-value summary-value positive">RD$ {{ number_format($totalPayments, 2) }}</td>
        </tr></table></div>
        <div class="summary-row"><table><tr>
            <td class="col-label">Balance Actual:</td>
            <td class="col-value summary-value {{ $balance < 0 ? 'negative' : 'positive' }}">RD$ {{ number_format(abs($balance), 2) }} {{ $balance < 0 ? '(Deuda)' : '(Favor)' }}</td>
        </tr></table></div>
    </div>

    <h3>Facturas</h3>
    <table>
        <thead>
            <tr>
                <th>Período</th>
                <th>Subtotal</th>
                <th>Balance Anterior</th>
                <th>Pagos Aplicados</th>
                <th class="text-right">Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bills as $bill)
            <tr>
                <td>{{ $bill->billing_month }}/{{ $bill->billing_year }}</td>
                <td>RD$ {{ number_format($bill->subtotal, 2) }}</td>
                <td>RD$ {{ number_format($bill->previous_balance, 2) }}</td>
                <td>RD$ {{ number_format($bill->payments_applied, 2) }}</td>
                <td class="text-right">RD$ {{ number_format($bill->total, 2) }}</td>
                <td class="text-center">{{ ucfirst($bill->status) }}</td>
            </tr>
            @endforeach
            @if($bills->isEmpty())
            <tr><td colspan="6" class="text-center">No hay facturas registradas</td></tr>
            @endif
        </tbody>
    </table>

    <h3>Pagos</h3>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Referencia</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Confirmado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $payment->reference_number ?? '-' }}</td>
                <td>RD$ {{ number_format($payment->amount, 2) }}</td>
                <td class="text-center">{{ ucfirst($payment->status) }}</td>
                <td>{{ $payment->confirmed_at ? $payment->confirmed_at->format('d/m/Y') : '-' }}</td>
            </tr>
            @endforeach
            @if($payments->isEmpty())
            <tr><td colspan="5" class="text-center">No hay pagos registrados</td></tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Reporte generado por CondoPro el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>