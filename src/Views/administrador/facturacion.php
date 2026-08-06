<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Facturación - Odonto Estética</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/facturacion.css">
    <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/global.css">
</head>
<body>

<div class="menu-layout">

    <?php require_once __DIR__ . '/layouts/menu.php'; ?>

    <main class="menu-main-content">

        <?php require_once __DIR__ . '/layouts/header.php'; ?>
        
        <header class="header">
            <div class="header-title">
                <h1>Facturación y Caja</h1>
                <p>Gestión de ingresos y cobros</p>
            </div>
        </header>

        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon icon-green"><i class="fa-solid fa-cash-register"></i></div>
                <div class="kpi-info">
                    <h3>Ingresos del Día</h3>
                    <div class="number">$ 2,850.00</div>
                    <div class="trend green"><i class="fa-solid fa-arrow-trend-up"></i> +18% vs ayer</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-blue"><i class="fa-solid fa-wallet"></i></div>
                <div class="kpi-info">
                    <h3>Ingresos del Mes</h3>
                    <div class="number">$ 38,650.00</div>
                    <div class="trend green"><i class="fa-solid fa-arrow-trend-up"></i> +12% este mes</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-purple"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                <div class="kpi-info">
                    <h3>Abonos del Mes</h3>
                    <div class="number">$ 15,320.00</div>
                    <div class="trend purple">Cobros parciales registrados</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon icon-orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="kpi-info">
                    <h3>Deudas Pendientes</h3>
                    <div class="number">$ 22,180.00</div>
                    <div class="trend orange">Por gestionar cobranza</div>
                </div>
            </div>
        </section>

        <div class="dashboard-grid">
            
            <div class="left-col">
                <div class="panel">
                    <div class="panel-navbar">
                        <div class="tabs">
                            <span class="tab-btn active" data-tab="ingresos">Facturación e Ingresos</span>
                            <span class="tab-btn" data-tab="configuracion">Configuración %</span>
                            <span class="tab-btn" data-tab="egresos">Liquidación (Egresos)</span>
                        </div>
                    </div>

                    <div id="content-ingresos" class="tab-content">
                        <div style="overflow-x: auto; min-height: 280px; background: white; padding: 15px; border-radius: 8px;">
                            <table id="tablaFacturacion" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>N° Factura</th>
                                        <th>Paciente</th>
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th>Total</th>
                                        <th>Pagado</th>
                                        <th>Pendiente</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="mainTableBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="content-configuracion" class="tab-content" style="display: none;">
                        <h3 style="margin-bottom: 15px; color: var(--text-main); font-size: 15px;">Odontólogos y Comisiones</h3>
                        <div style="overflow-x: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Odontólogo</th>
                                        <th>Especialidad</th>
                                        <th>Porcentaje Actual</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="configTableBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="content-egresos" class="tab-content" style="display: none;">
                        <h3 style="margin-bottom: 15px; color: var(--text-main); font-size: 15px;">Generar Liquidación</h3>
                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px;">
                            
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label>Seleccionar Odontólogo</label>
                                <select id="selectOdontologoLiq" class="w-100" style="padding: 10px;">
                                    <option value="">Seleccione un doctor...</option>
                                    <option value="1">Dra. Martha Romero</option>
                                    <option value="2">Dr. Andrés Díaz</option>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 20px;">
                                <label>Mes a Liquidar</label>
                                <input type="month" id="mesLiquidacion" class="w-100" style="padding: 10px;">
                            </div>

                            <button class="btn-outline-action w-100" id="btnCalcular" style="margin-bottom: 20px;">
                                <i class="fa-solid fa-calculator"></i> Calcular Producción
                            </button>

                            <div id="resultadoLiquidacion" style="display: none; border-top: 1px dashed var(--border-color); padding-top: 15px;">
                                <div class="cash-row" style="margin-bottom: 8px;">
                                    <span>Procedimientos Realizados:</span>
                                    <strong id="liqCantidad">0</strong>
                                </div>
                                <div class="cash-row" style="margin-bottom: 8px;">
                                    <span>Producción Total:</span>
                                    <strong id="liqTotalProd">$ 0.00</strong>
                                </div>
                                <div class="cash-row" style="margin-bottom: 8px; color: var(--primary-blue);">
                                    <span>Porcentaje de Comisión:</span>
                                    <strong id="liqPorcentaje">40.00 %</strong>
                                </div>
                                <div class="cash-total-row" style="font-size: 16px; margin-top: 10px;">
                                    <span>Total a Pagar:</span>
                                    <strong id="liqTotalPagar" style="color: var(--c-green);">$ 0.00</strong>
                                </div>

                                <button id="btnRegistrarEgreso" class="btn-primary w-100" style="justify-content: center; margin-top: 20px;">
                                    <i class="fa-solid fa-check"></i> Registrar Egreso y Pagar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="right-col">
                <div class="panel">
                    <div class="panel-header-flex">
                        <h2 class="panel-title">Cierre de Caja (Hoy)</h2>
                    </div>
                    <div class="cash-control-list">
                        <div class="cash-row">
                            <span><i class="fa-solid fa-money-bill-wave" style="color:#22c55e;"></i> Efectivo</span>
                            <strong id="cajaEfectivo">$ 0.00</strong>
                        </div>
                        <div class="cash-row">
                            <span><i class="fa-solid fa-credit-card" style="color:#3b82f6;"></i> Tarjeta</span>
                            <strong id="cajaTarjeta">$ 0.00</strong>
                        </div>
                        <div class="cash-row">
                            <span><i class="fa-solid fa-money-bill-transfer" style="color:#a855f7;"></i> Transferencia</span>
                            <strong id="cajaTransferencia">$ 0.00</strong>
                        </div>
                        <div class="cash-total-row">
                            <span>Total en Caja</span>
                            <strong id="cajaTotal">$ 0.00</strong>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header-flex">
                        <h2 class="panel-title">Exportar Datos</h2>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>Mes a Exportar</label>
                        <input type="month" id="mesExportar" class="w-100" style="padding: 8px; border: 1px solid var(--border-color); border-radius: 6px;">
                    </div>

                    <div class="form-group">
                        <label>Formato de descarga</label>
                        <select id="formatoExportar" class="w-100" style="padding: 8px; border: 1px solid var(--border-color); border-radius: 6px;">
                            <option value="pdf">Documento PDF (.pdf)</option>
                            <option value="excel">Hoja de Excel (.xlsx)</option>
                            <option value="imprimir">Imprimir Reporte</option>
                        </select>
                    </div>
                    
                    <button id="btnDescargarDatos" class="btn-primary w-100" style="justify-content: center; margin-top: 10px;">
                        <i class="fa-solid fa-file-export"></i> Descargar Datos
                    </button>
                </div>
            </div>
        </div> 
    </main> 
        
    </div>
    <div id="modalPago" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeModalPago">&times;</span>
            <h2>Registrar Pago / Abono</h2>
            <form id="formPago">
                <input type="hidden" id="pagoNumFactura">
                
                <div class="form-group">
                    <label>Factura y Paciente</label>
                    <input type="text" id="pagoDetalle" readonly style="background: #f1f5f9; cursor: not-allowed; font-weight: bold; color: var(--text-main);">
                </div>
                
                <div class="form-group">
                    <label>Deuda Pendiente ($)</label>
                    <input type="text" id="pagoPendienteVisual" readonly style="background: #fef2f2; color: var(--c-red); font-weight: bold; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label>Método de Pago</label>
                    <select id="pagoMetodo" required>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta (Datáfono)</option>
                        <option value="Transferencia">Transferencia (Nequi/Bancos)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Monto a Abonar ($)</label>
                    <input type="number" id="pagoMonto" required min="1" placeholder="Ej. 50000">
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 15px;">
                    <i class="fa-solid fa-cash-register"></i> Guardar Pago
                </button>
            </form>
        </div>
    </div>

    <!-- Modal de Exportación Individual -->
    <div id="modalExportIndividual" class="modal">
        <div class="modal-content modal-export">
            <span class="close-modal" id="closeModalExport">&times;</span>
            <div class="modal-export-header">
                <div class="modal-export-icon">
                    <i class="fa-solid fa-file-arrow-down"></i>
                </div>
                <h2>Descargar Factura</h2>
                <p class="modal-export-subtitle" id="exportFacturaInfo">FAC-0000 - Paciente</p>
            </div>
            <input type="hidden" id="exportFacturaNum">
            <div class="export-options">
                <button class="export-option-btn" onclick="exportarIndividual('pdf')">
                    <div class="export-option-icon pdf-icon">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div class="export-option-info">
                        <strong>Documento PDF</strong>
                        <span>Descargar como archivo .pdf</span>
                    </div>
                    <i class="fa-solid fa-chevron-right export-arrow"></i>
                </button>
                <button class="export-option-btn" onclick="exportarIndividual('excel')">
                    <div class="export-option-icon excel-icon">
                        <i class="fa-solid fa-file-excel"></i>
                    </div>
                    <div class="export-option-info">
                        <strong>Hoja de Excel</strong>
                        <span>Descargar como archivo .xls</span>
                    </div>
                    <i class="fa-solid fa-chevron-right export-arrow"></i>
                </button>
                <button class="export-option-btn" onclick="exportarIndividual('imprimir')">
                    <div class="export-option-icon print-icon">
                        <i class="fa-solid fa-print"></i>
                    </div>
                    <div class="export-option-info">
                        <strong>Imprimir</strong>
                        <span>Enviar a la impresora</span>
                    </div>
                    <i class="fa-solid fa-chevron-right export-arrow"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script src="/LOGIN_ORIGINAL/public/js/administrador/facturacion.js"></script>
</body>
</html>