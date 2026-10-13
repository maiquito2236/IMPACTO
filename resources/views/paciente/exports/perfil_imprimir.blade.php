<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha del Paciente</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; margin: 30px; color: #1e293b; }
        .header { text-align: center; border-bottom: 2px solid #0F62FE; padding-bottom: 15px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td, th { padding: 8px 12px; border: 1px solid #cbd5e1; }
        th { background: #f8fafc; text-align: left; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>Odonto Estética - Ficha Médica de Paciente</h2>
        <p>Fecha de emisión: {{ date('d/m/Y H:i') }}</p>
    </div>
    <table>
        <tr><th>Nombres y Apellidos</th><td>{{ $u->NOMBRES }} {{ $u->APELLIDOS }}</td></tr>
        <tr><th>Documento</th><td>{{ $u->TIPO_DOCUMENTO }} {{ $u->NUMERO_DOCUMENTO }}</td></tr>
        <tr><th>Correo</th><td>{{ $u->CORREO }}</td></tr>
        <tr><th>Teléfono</th><td>{{ $u->TELEFONO }}</td></tr>
        <tr><th>EPS</th><td>{{ $u->NOMBRE_EPS }}</td></tr>
        <tr><th>Dirección</th><td>{{ $u->DIRECCION }}</td></tr>
    </table>
</body>
</html>