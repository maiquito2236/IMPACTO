<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Factura</title>
    <style>
        @page {
            size: auto;
            margin: 0mm;
        }
        body { font-family: Arial, sans-serif; font-size: 14px; padding: 35px 30px; margin: 0; color: #1e293b; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #0F62FE; padding-bottom: 15px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td, th { padding: 8px 12px; border: 1px solid #cbd5e1; }
        th { background: #f8fafc; text-align: left; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>Odonto Estética - Comprobante de Facturación</h2>
        <p>Factura: FAC-{{ str_pad($factura->ID_FACTURA, 4, '0', STR_PAD_LEFT) }} | Fecha: {{ date('d/m/Y', strtotime($factura->FECHA_EMISION)) }}</p>
    </div>
    <p><b>Total Facturado:</b> ${{ number_format($factura->TOTAL, 0, ',', '.') }}</p>
    <h3>Historial de Abonos</h3>
    <table>
        <thead>
            <tr><th>Monto</th><th>Fecha</th><th>Método</th></tr>
        </thead>
        <tbody>
            @foreach ($pagos as $p)
                <tr>
                    <td>${{ number_format($p->MONTO, 0, ',', '.') }}</td>
                    <td>{{ date('d/m/Y', strtotime($p->FECHA_PAGO)) }}</td>
                    <td>{{ $p->METODO_PAGO }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>