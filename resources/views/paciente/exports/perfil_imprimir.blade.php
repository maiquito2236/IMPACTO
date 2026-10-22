<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Datos Personales</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* TRUCO: Margen de página a cero elimina la URL, fecha, título y paginación predeterminados del navegador */
        @page {
            size: auto;
            margin: 0mm;
        }

        body {
            font-family: Arial, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 30px 40px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        }

        .logo-wrapper {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-img {
            width: 42%;
            max-width: 240px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .doc-title {
            text-align: center;
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #1e293b;
            margin-bottom: 24px;
        }

        .data-list {
            display: flex;
            flex-direction: column;
        }

        .data-row {
            display: flex;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.88rem;
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .data-label {
            width: 220px;
            min-width: 220px;
            font-weight: 700;
            color: #1e293b;
        }

        .data-value {
            color: #475569;
            font-weight: 500;
            word-break: break-word;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 35px 20px;
                display: block;
            }

            .container {
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                max-width: 600px;
                margin: 0 auto;
                padding: 30px 40px;
                border-radius: 8px;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="logo-wrapper">
            <img src="{{ asset('img/logo_odontologia.png') }}" alt="Odonto Estética" class="logo-img">
        </div>

        <h1 class="doc-title">Comprobante de datos de paciente</h1>

        <div class="data-list">
            <div class="data-row">
                <span class="data-label">Tipo de Documento:</span>
                <span class="data-value">{{ $u->TIPO_DOCUMENTO ?? 'C.C' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Número de Documento:</span>
                <span class="data-value">{{ $u->NUMERO_DOCUMENTO ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Nombres:</span>
                <span class="data-value">{{ $u->NOMBRES ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Apellidos:</span>
                <span class="data-value">{{ $u->APELLIDOS ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Teléfono:</span>
                <span class="data-value">{{ $u->TELEFONO ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Correo Electrónico:</span>
                <span class="data-value">{{ $u->CORREO ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Género:</span>
                <span class="data-value">{{ $u->GENERO ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Fecha de Nacimiento:</span>
                <span class="data-value">{{ $u->FECHA_NACIMIENTO ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Dirección:</span>
                <span class="data-value">{{ $u->DIRECCION ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Factor de sangre (RH):</span>
                <span class="data-value">{{ $u->RH ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">EPS:</span>
                <span class="data-value">{{ $u->NOMBRE_EPS ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Contacto de Emergencia:</span>
                <span class="data-value">{{ $u->NOMBRE_CONTACTO_EMERGENCIA ?? '' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Teléfono de Emergencia:</span>
                <span class="data-value">{{ $u->NUMERO_CONTACTO_EMERGENCIA ?? '' }}</span>
            </div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 25px; text-align: center;">
        <button onclick="window.close()" style="padding: 8px 24px; font-weight: 600; cursor: pointer; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-size: 0.85rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f1f5f9'; this.style.color='#1e293b';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.color='#475569';">
            Cerrar Ventana
        </button>
    </div>
</body>
</html>