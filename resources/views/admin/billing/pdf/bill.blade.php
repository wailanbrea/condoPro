<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>Factura {{ $bill->apartment->number }} - {{ str_pad($bill->billing_month, 2, '0', STR_PAD_LEFT) }}/{{ $bill->billing_year }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; color: #333; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #1a237e; padding-bottom: 10px; margin-bottom: 20px; }
        .header-left h1 { font-size: 22px; color: #1a237e; margin: 0; }
        .header-left p { margin: 2px 0; color: #666; font-size: 11px; }
        .header-right { text-align: right; }
        .header-right h2 { font-size: 16px; color: #c62828; margin: 0 0 5px 0; }
        .header-right p { margin: 2px 0; font-size: 11px; }
        .invoice-info { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .info-box { width: 48%; border: 1px solid #ddd; border-radius: 4px; padding: 10px; }
        .info-box h3 { font-size: 11px; color: #1a237e; text-transform: uppercase; margin: 0 0 8px 0; letter-spacing: 1px; }
        .info-box p { margin: 2px 0; font-size: 12px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: #fff3e0; color: #e65100; }
        .status-partial { background: #e3f2fd; color: #1565c0; }
        .status-paid { background: #e8f5e9; color: #2e7d32; }
        .status-overdue { background: #ffebee; color: #c62828; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table thead th { background: #1a237e; color: white; font-size: 11px; text-transform: uppercase; padding: 8px 10px; text-align: left; letter-spacing: 0.5px; }
        table thead th:last-child { text-align: right; }
        table tbody td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 12px; }
        table tbody td:last-child { text-align: right; font-family: monospace; }
        table tbody tr:nth-child(even) { background: #f8f9ff; }
        .total-section { width: 45%; margin-left: auto; }
        .total-row { display: flex; justify-content: space-between; padding: 6px 10px; font-size: 12px; }
        .total-row.grand { background: #1a237e; color: white; font-size: 14px; font-weight: bold; border-radius: 4px; }
        .total-row.grand .amount { font-family: monospace; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; text-align: center; color: #999; font-size: 10px; }
        @media print { body { margin: 10px; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>{{ $condominium->name }}</h1>
            <p>{{ $condominium->address }}</p>
            @if($condominium->phone)<p>{{ app()->getLocale() === 'es' ? 'Tel:' : 'Phone:' }} {{ $condominium->phone }}</p>@endif
            @if($condominium->email)<p>{{ $condominium->email }}</p>@endif
        </div>
        <div class="header-right">
            <h2>{{ app()->getLocale() === 'es' ? 'FACTURA' : 'INVOICE' }} #{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}</h2>
            <p>{{ app()->getLocale() === 'es' ? 'Período:' : 'Period:' }} {{ str_pad($bill->billing_month, 2, '0', STR_PAD_LEFT) }}/{{ $bill->billing_year }}</p>
            <p>{{ app()->getLocale() === 'es' ? 'Vencimiento:' : 'Due:' }} {{ $bill->due_date?->format('d/m/Y') }}</p>
            <p>
                <span class="status-badge status-{{ $bill->status }}">
                    @switch($bill->status)
                        @case('pending') {{ app()->getLocale() === 'es' ? 'Pendiente' : 'Pending' }} @break
                        @case('partial') {{ app()->getLocale() === 'es' ? 'Parcial' : 'Partial' }} @break
                        @case('paid') {{ app()->getLocale() === 'es' ? 'Pagada' : 'Paid' }} @break
                        @case('overdue') {{ app()->getLocale() === 'es' ? 'Vencida' : 'Overdue' }} @break
                        @default {{ ucfirst($bill->status) }}
                    @endswitch
                </span>
            </p>
        </div>
    </div>

    <div class="invoice-info">
        <div class="info-box">
            <h3>{{ app()->getLocale() === 'es' ? 'Propietario' : 'Owner' }}</h3>
            <p><strong>{{ $apartment->owner_name }}</strong></p>
            <p>{{ app()->getLocale() === 'es' ? 'Apartamento:' : 'Apartment:' }} {{ $apartment->number }}</p>
            @if($apartment->users->first())<p>{{ $apartment->users->first()->email }}</p>@endif
        </div>
        <div class="info-box" style="text-align: right;">
            <h3>{{ app()->getLocale() === 'es' ? 'Referencia' : 'Reference' }}</h3>
            <p>{{ app()->getLocale() === 'es' ? 'Factura:' : 'Bill:' }} #{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}</p>
            <p>{{ app()->getLocale() === 'es' ? 'Fecha emisión:' : 'Issue date:' }} {{ $bill->created_at?->format('d/m/Y') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ app()->getLocale() === 'es' ? 'Concepto' : 'Concept' }}</th>
                <th>{{ app()->getLocale() === 'es' ? 'Descripción' : 'Description' }}</th>
                <th>{{ app()->getLocale() === 'es' ? 'Monto' : 'Amount' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->billItems as $item)
            <tr>
                <td>
                    @switch($item->concept_type)
                        @case('maintenance') {{ app()->getLocale() === 'es' ? 'Mantenimiento' : 'Maintenance' }} @break
                        @case('gas') Gas @break
                        @case('extra_charge') {{ app()->getLocale() === 'es' ? 'Cuota Extra' : 'Extra Charge' }} @break
                        @default {{ ucfirst($item->concept_type) }}
                    @endswitch
                </td>
                <td>{{ $item->description }}</td>
                <td>RD${{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        @if($bill->previous_balance != 0)
        <div class="total-row">
            <span>{{ app()->getLocale() === 'es' ? 'Balance Anterior' : 'Previous Balance' }}</span>
            <span>RD${{ number_format(abs($bill->previous_balance), 2) }}</span>
        </div>
        @endif
        <div class="total-row">
            <span>{{ app()->getLocale() === 'es' ? 'Subtotal' : 'Subtotal' }}</span>
            <span>RD${{ number_format($bill->subtotal, 2) }}</span>
        </div>
        @if($bill->payments_applied > 0)
        <div class="total-row">
            <span>{{ app()->getLocale() === 'es' ? 'Pagos Aplicados' : 'Payments Applied' }}</span>
            <span>-RD${{ number_format($bill->payments_applied, 2) }}</span>
        </div>
        @endif
        <div class="total-row grand">
            <span>{{ app()->getLocale() === 'es' ? 'TOTAL A PAGAR' : 'TOTAL DUE' }}</span>
            <span class="amount">RD${{ number_format($bill->total - $bill->payments_applied, 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <p>{{ $condominium->name }} &mdash; {{ app()->getLocale() === 'es' ? 'Documento generado el' : 'Document generated on' }} {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>