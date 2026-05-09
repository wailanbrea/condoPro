<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagos Pendientes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Inter, Arial, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header { text-align: center; padding-bottom: 16px; border-bottom: 2px solid #163c6e; margin-bottom: 16px; }
        .header h1 { font-size: 20px; color: #163c6e; margin-bottom: 2px; }
        .header h2 { font-size: 14px; color: #3a3a5a; font-weight: normal; }
        .header .condo-name { font-size: 13px; color: #555; margin-top: 4px; }
        .header .report-date { font-size: 10px; color: #888; margin-top: 4px; }
        .summary { background: #fff3e0; padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: center; }
        .summary h3 { color: #e67e22; font-size: 14px; margin-bottom: 4px; }
        .summary .amount { font-size: 22px; color: #e67e22; font-weight: bold; }
        .summary .count { font-size: 11px; color: #888; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #163c6e; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background: #f9f9fc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; background: #eef2f9 !important; border-top: 2px solid #163c6e; }
        .status-pending { background: #fff3e0; color: #e67e22; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .footer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #ccc; font-size: 9px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CondoPro</h1>
        <h2>Pagos Pendientes de Confirmación</h2>
        <div class="condo-name">{{ $condominium->name }} - {{ $condominium->address }}</div>
        <div class="report-date">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary">
        <h3>Total Pendiente</h3>
        <div class="amount">RD$ {{ number_format($totalPending, 2) }}</div>
        <div class="count">{{ $payments->count() }} pago(s) por confirmar</div>
    </div>

    @if($payments->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th>Fecha Pago</th>
                <th>Apartamento</th>
                <th>Residente</th>
                <th>Referencia</th>
                <th>Cuenta Bancaria</th>
                <th class="text-right">Monto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $payment->apartment->number ?? '-' }}</td>
                <td>{{ $payment->user->name ?? '-' }}</td>
                <td>{{ $payment->reference_number ?? '-' }}</td>
                <td>{{ $payment->bankAccount->bank_name ?? '-' }}</td>
                <td class="text-right">RD$ {{ number_format($payment->amount, 2) }}</td>
                <td><span class="status-pending">PENDIENTE</span></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL</td>
                <td class="text-right">RD$ {{ number_format($totalPending, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <p style="text-align: center; padding: 24px; color: #888;">No hay pagos pendientes de confirmación.</p>
    @endif

    <div class="footer">
        Reporte generado por CondoPro el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>