<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Rechazado</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: 'Inter', Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f5f7; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <tr>
                        <td style="background-color: #003d9b; padding: 24px 32px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 700;">CondoPro</h1>
                            <p style="margin: 4px 0 0; color: #dae2ff; font-size: 14px;">Administración de Condominios</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <div style="text-align: center; margin-bottom: 24px;">
                                <div style="width: 56px; height: 56px; background-color: #ffdad6; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 28px; color: #ba1a1a;">✗</span>
                                </div>
                            </div>
                            <h2 style="margin: 0 0 8px; color: #091c35; font-size: 20px; font-weight: 600; text-align: center;">Pago Rechazado</h2>
                            <p style="margin: 0 0 24px; color: #434654; font-size: 14px; text-align: center;">Su pago no pudo ser verificado. Por favor, contacte a la administración.</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f0f3ff; border-radius: 8px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 16px 24px; border-bottom: 1px solid #e7eeff;">
                                        <span style="color: #434654; font-size: 12px; font-weight: 700; letter-spacing: 0.05em;">APARTAMENTO</span><br>
                                        <span style="color: #091c35; font-size: 14px; font-weight: 500;">{{ $apartment->number }}</span>
                                    </td>
                                    <td style="padding: 16px 24px; border-bottom: 1px solid #e7eeff;">
                                        <span style="color: #434654; font-size: 12px; font-weight: 700; letter-spacing: 0.05em;">CONDOMINIO</span><br>
                                        <span style="color: #091c35; font-size: 14px; font-weight: 500;">{{ $condominium->name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 16px 24px; border-bottom: 1px solid #e7eeff;">
                                        <span style="color: #434654; font-size: 12px; font-weight: 700; letter-spacing: 0.05em;">MONTO</span><br>
                                        <span style="color: #091c35; font-size: 18px; font-weight: 700; font-family: 'JetBrains Mono', monospace;">RD${{ number_format($payment->amount, 2) }}</span>
                                    </td>
                                    <td style="padding: 16px 24px; border-bottom: 1px solid #e7eeff;">
                                        <span style="color: #434654; font-size: 12px; font-weight: 700; letter-spacing: 0.05em;">FECHA DE PAGO</span><br>
                                        <span style="color: #091c35; font-size: 14px; font-weight: 500;">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '—' }}</span>
                                    </td>
                                </tr>
                                @if($payment->admin_observation)
                                <tr>
                                    <td style="padding: 16px 24px;" colspan="2">
                                        <span style="color: #ba1a1a; font-size: 12px; font-weight: 700; letter-spacing: 0.05em;">OBSERVACIÓN</span><br>
                                        <span style="color: #091c35; font-size: 14px;">{{ $payment->admin_observation }}</span>
                                    </td>
                                </tr>
                                @endif
                            </table>

                            <div style="margin-top: 24px; padding: 16px; background-color: #ffdad6; border-radius: 8px; border-left: 4px solid #ba1a1a;">
                                <span style="color: #93000a; font-size: 12px; font-weight: 700;">ACCIÓN REQUERIDA</span><br>
                                <span style="color: #091c35; font-size: 14px;">Por favor, contacte a la administración o suba un nuevo voucher de pago.</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f0f3ff; padding: 16px 32px; text-align: center;">
                            <p style="margin: 0; color: #434654; font-size: 12px;">Este es un correo automático de CondoPro. No responda a este mensaje.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>