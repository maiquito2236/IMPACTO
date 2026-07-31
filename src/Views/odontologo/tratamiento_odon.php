<!DOCTYPE html>
<html lang="es">

<head>
    <base href="/LOGIN_ORIGINAL/">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odonto Estética - Registro de Historias Clínicas</title>

    <link rel="stylesheet" href="public/css/odontologo/menu.css">
    <link rel="stylesheet" href="public/css/odontologo/tratamiento_odon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="menu-layout">
        <?php require_once __DIR__ . '/layouts/menu.php'; ?>

        <div class="main-content">
            <section class="content-layout">
                <div class="data-card table-section">
                    <div class="table-header-actions">
                        <h3>Registro General de Historias Clínicas</h3>
                        <div class="search-bar">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input id="searchTreatment" type="text" placeholder="Buscar por paciente o documento...">
                        </div>
                    </div>

                    <table class="treatment-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Fecha de Registro</th>
                                <th>Paciente</th>
                                <th>Documento</th>
                                <th>Motivo de Consulta</th>
                                <th>Diagnóstico Principal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>

                <!-- KPIs DE RESUMEN (Para igualar layout de Pacientes) -->
                <aside class="sidebar-metrics">
                    <div class="data-card" style="padding: 24px;">
                        <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 24px; color: #0f172a;">Resumen Clínico</h3>
                        
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 18px; padding-bottom: 18px; border-bottom: 1px solid #e2e8f0;">
                            <div style="width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; background: #eff6ff; color: #1a56db;">
                                <i class="fa-solid fa-file-medical"></i>
                            </div>
                            <div>
                                <span style="display: block; font-size: 12px; color: #64748b; margin-bottom: 4px;">Historias Registradas</span>
                                <h2 id="total-registros" style="font-size: 24px; font-weight: 700; color: #0f172a; margin: 0;">--</h2>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; background: #ecfdf5; color: #10b981;">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div>
                                <span style="display: block; font-size: 12px; color: #64748b; margin-bottom: 4px;">Pacientes Atendidos</span>
                                <h2 id="pacientes-unicos" style="font-size: 24px; font-weight: 700; color: #0f172a; margin: 0;">--</h2>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" defer></script>

<div id="modal-detalle-historia" class="modal-backdrop" style="display:none; align-items:flex-start; justify-content:center; background-color: rgba(15, 23, 42, 0.75); position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1000; overflow-y: auto; padding: 40px 0;">
    
    <div class="modal-window" style="background: white; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 800px; width: 95%; margin: auto; position: relative;">
        
        <div class="modal-top-bar" data-html2canvas-ignore style="background: #f8fafc; padding: 15px 25px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; border-radius: 12px 12px 0 0; position: sticky; top: 0; z-index: 10;">
            <a href="#" class="close-modal-x" id="closeDetailModal" style="font-size: 1.8rem; color: #94a3b8; text-decoration: none; line-height: 1;">&times;</a>
        </div>
        
        <div id="print-area-historia" style="padding: 40px;">
            <div style="text-align: center; margin-bottom: 30px; border-bottom: 3px solid #3b82f6; padding-bottom: 15px;">
                <img src="public/img/logo_odontologia.png" alt="Logo Odonto Estética" style="max-width: 200px; margin-bottom: 10px;">
                <h1 style="color: #1e293b; margin: 0; font-size: 1.8rem;">Historia Clínica Odontológica</h1>
                <p style="color: #64748b; margin: 5px 0 0 0;">Odonto Estética - Salud y Bienestar</p>
            </div>

            <section style="display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 25px;">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div id="avatarInitialsModal" style="width: 65px; height: 65px; background-color: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.4rem; text-transform: uppercase; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">--</div>
                    <div>
                        <h2 id="modal-paciente" style="margin: 0; color: #1e293b; font-size: 1.5rem;">Cargando paciente...</h2>
                        <p style="margin: 5px 0 0 0; color: #64748b; font-size: 0.95rem;">Identificación: <span id="modal-documento">--</span></p>
                    </div>
                </div>
                <div style="text-align: right; color: #475569; font-size: 0.95rem;">
                    <p style="margin: 0 0 8px 0;"><i class="fa-solid fa-user-doctor" style="color: #3b82f6;"></i> <strong>Doctor Asignado:</strong> <span id="modal-doctor">--</span></p>
                    <p style="margin: 0;"><i class="fa-regular fa-calendar" style="color: #3b82f6;"></i> <strong>Fecha:</strong> <span id="modal-fecha-impresion">--</span></p>
                </div>
            </section>

            <div style="background: #fff; padding: 20px; border-radius: 12px; border-left: 5px solid #3b82f6; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border-top: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
                <h3 style="margin-top:0; color: #1e293b; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                    <i class="fa-solid fa-tooth" style="color: #3b82f6;"></i> Detalle del Procedimiento
                </h3>
                <div style="margin: 15px 0 0 0; color: #334155; font-size: 1.05rem; line-height: 1.6;">
                    <p><strong>Motivo de Consulta:</strong> <span id="modal-motivo"></span></p>
                    <p><strong>Diagnóstico Principal:</strong> <span id="modal-diagnostico"></span></p>
                    <p><strong>Detalle del Tratamiento:</strong> <span id="modal-tratamiento"></span></p>
                    <p><strong>Dientes Tratados:</strong> <span id="modal-dientes-tratados"></span></p>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="public/js/odontologo/tratamiento_odon.js?v=<?= time() ?>" defer></script>
</body>
</html>