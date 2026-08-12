<?php 
$action = $_GET['action'] ?? 'dashboard'; 
?>

<aside class="menu-sidebar">

    <div class="menu-logo-area" style="text-align: center; padding: 45px 20px; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
        <div class="menu-logo-icon" style="width: 100%;">
            <img src="/LOGIN_ORIGINAL/public/img/logo_odontologia.png" alt="Logo Odonto Estética" 
                 style="width: 100%; max-width: 190px; height: auto; object-fit: contain;">
        </div>
    </div>

    <nav class="menu-nav">
        <a href="/LOGIN_ORIGINAL/dashboard" class="menu-nav-item <?= ($action === 'dashboard') ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        
        <a href="/LOGIN_ORIGINAL/admin/agenda" class="menu-nav-item <?= ($action === 'admin/agenda') ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-days"></i> Agenda y citas
        </a>
        
        <a href="/LOGIN_ORIGINAL/admin/gestion_usuario" class="menu-nav-item <?= ($action === 'admin/gestion_usuario') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-group"></i> Gestión de Usuarios
        </a>
        
        <a href="/LOGIN_ORIGINAL/admin/consultorios" class="menu-nav-item <?= ($action === 'admin/consultorios') ? 'active' : '' ?>">
            <i class="fa-solid fa-clinic-medical"></i> Gestión de Consultorios
        </a>

        <a href="/LOGIN_ORIGINAL/admin/horarios" class="menu-nav-item <?= ($action === 'admin/horarios') ? 'active' : '' ?>">
            <i class="fa-regular fa-calendar-days"></i> Gestión de horarios
        </a>

        <a href="/LOGIN_ORIGINAL/admin/facturacion" class="menu-nav-item <?= ($action === 'admin/facturacion') ? 'active' : '' ?>">
            <i class="fa-solid fa-file-invoice-dollar"></i> Facturación
        </a>

        <a href="/LOGIN_ORIGINAL/admin/reportes" class="menu-nav-item <?= ($action === 'admin/reportes') ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-line"></i> Reportes y estadísticas
        </a>

        <a href="/LOGIN_ORIGINAL/admin/otros" class="menu-nav-item <?= ($action === 'admin/otros') ? 'active' : '' ?>">
            <i class="fa-solid fa-layer-group"></i> Otros
        </a>
    </nav>

    <div class="menu-sidebar-footer">
        <a href="/LOGIN_ORIGINAL/admin/perfil" class="menu-nav-item <?= ($action === 'admin/perfil') ? 'active' : '' ?>">
            <i class="fa-solid fa-user-tie"></i> Mi Perfil
        </a>
        
        <a href="/LOGIN_ORIGINAL/admin/notificaciones" class="menu-nav-item <?= ($action === 'admin/notificaciones') ? 'active' : '' ?>">
            <i class="fa-solid fa-bell"></i> Notificaciones
        </a>
        
<?php
$currentPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$puedeCargaMasiva = false;
$endpointCarga = "";
$endpointPlantilla = "";
$tituloCarga = "";

if (strpos($currentPage, 'gestion_usuario') !== false) {
    $puedeCargaMasiva = true;
    $tituloCarga = "Carga Masiva de Usuarios";
    $endpointCarga = "/LOGIN_ORIGINAL/admin/gestion_usuario/carga_masiva";
    $endpointPlantilla = "/LOGIN_ORIGINAL/admin/gestion_usuario/carga_masiva?descargar_plantilla=1";
} elseif (strpos($currentPage, 'agenda') !== false) {
    $puedeCargaMasiva = true;
    $tituloCarga = "Carga Masiva de Citas";
    $endpointCarga = "/LOGIN_ORIGINAL/admin/agenda/carga_masiva";
    $endpointPlantilla = "/LOGIN_ORIGINAL/admin/agenda/carga_masiva?descargar_plantilla=1";
} elseif (strpos($currentPage, 'facturacion') !== false) {
    $puedeCargaMasiva = true;
    $tituloCarga = "Carga Masiva de Facturas";
    $endpointCarga = "/LOGIN_ORIGINAL/admin/facturacion/carga_masiva";
    $endpointPlantilla = "/LOGIN_ORIGINAL/admin/facturacion/carga_masiva?descargar_plantilla=1";
} elseif (strpos($currentPage, 'horarios') !== false) {
    $puedeCargaMasiva = true;
    $tituloCarga = "Carga Masiva de Horarios";
    $endpointCarga = "/LOGIN_ORIGINAL/admin/horarios/carga_masiva";
    $endpointPlantilla = "/LOGIN_ORIGINAL/admin/horarios/carga_masiva?descargar_plantilla=1";
}
?>

        <?php if ($puedeCargaMasiva): ?>
        <button class="menu-nav-item" onclick="abrirModalCargaMasiva(event)" style="background-color: #10b981; color: white; border: none; width: calc(100% - 40px); margin: 10px 20px; border-radius: 8px; text-align: left; cursor: pointer;">
            <i class="fa-solid fa-file-csv" style="color: white;"></i> Carga Masiva
        </button>
        <?php endif; ?>

        <a href="/LOGIN_ORIGINAL/salirAdmin" class="menu-nav-item">
            <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
        </a>
    </div>
</aside>

<?php if($puedeCargaMasiva): ?>
<!-- Modal Carga Masiva (Inline styles para forzar visualización) -->
<div id="modal-carga-masiva" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center; backdrop-filter: blur(2px);" onclick="if(event.target === this) cerrarModalCargaMasiva()">
  <div style="background-color: #fff; padding: 25px 30px; border-radius: 12px; width: 450px; max-width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
      <h3 style="color:#1e293b; margin:0; font-size: 1.3rem; display:flex; align-items:center; gap:10px;">
          <i class="fa-solid fa-cloud-arrow-up" style="color:#10b981;"></i> <?= $tituloCarga ?>
      </h3>
      <button type="button" onclick="cerrarModalCargaMasiva()" style="background:none; border:none; font-size:1.5rem; color:#94a3b8; cursor:pointer;">&times;</button>
    </div>
    
    <div>
      <p style="color:#64748b; font-size:0.95rem; margin-bottom: 20px; line-height: 1.5;">Sube tu archivo CSV correspondiente a <strong><?= $tituloCarga ?></strong>. Por favor, asegúrate de descargar y usar el formato de la plantilla.</p>
      
      <div style="text-align: center; margin-bottom: 25px;">
        <a href="<?= $endpointPlantilla ?>" style="display:inline-flex; align-items:center; gap:8px; background-color: #f1f5f9; color: #3b82f6; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600; border: 1px solid #cbd5e1;">
            <i class="fa-solid fa-download"></i> Descargar Plantilla CSV
        </a>
      </div>

      <form id="formCargaMasiva" onsubmit="procesarCargaMasiva(event)">
        <div style="margin-bottom: 20px;">
            <label style="display:block; font-weight: 600; color:#334155; margin-bottom: 8px; font-size:0.9rem;">Seleccionar Archivo (CSV):</label>
            <input type="file" id="archivo_csv" name="archivo_csv" accept=".csv" required style="width:100%; padding:10px; border: 2px dashed #cbd5e1; border-radius: 8px; background-color: #f8fafc; cursor:pointer;">
        </div>
        
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:25px;">
            <button type="button" onclick="cerrarModalCargaMasiva()" style="padding: 10px 16px; border: none; background-color: #e2e8f0; color: #475569; border-radius: 6px; cursor: pointer; font-weight: 600;">Cancelar</button>
            <button type="submit" id="btnSubmitCarga" style="padding: 10px 18px; border: none; background-color: #10b981; color: white; border-radius: 6px; cursor: pointer; font-weight: 600; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-upload"></i> Subir Archivo
            </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function abrirModalCargaMasiva(e) {
    if(e) e.preventDefault();
    document.getElementById('modal-carga-masiva').style.display = 'flex';
}
function cerrarModalCargaMasiva() {
    document.getElementById('modal-carga-masiva').style.display = 'none';
    document.getElementById('formCargaMasiva').reset();
}

function procesarCargaMasiva(e) {
    e.preventDefault();
    const fileInput = document.getElementById('archivo_csv');
    if (!fileInput.files.length) return;

    const formData = new FormData();
    formData.append('archivo_csv', fileInput.files[0]);

    const btnSubmit = document.getElementById('btnSubmitCarga');
    const originalText = btnSubmit.innerHTML;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Subiendo...';
    btnSubmit.disabled = true;

    fetch("<?= $endpointCarga ?>", { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        btnSubmit.innerHTML = originalText;
        btnSubmit.disabled = false;
        
        if (data.success) {
            cerrarModalCargaMasiva();
            let msg = `Proceso completado. Éxitos: ${data.successCount}, Errores: ${data.errorCount}.`;
            if (data.errorCount > 0) msg += ` Detalles: ${data.erroresDetalle.join(' | ')}`;
            
            if (typeof Swal !== 'undefined') {
                Swal.fire('¡Éxito!', msg, 'success').then(() => window.location.reload());
            } else { alert('¡Éxito! ' + msg); window.location.reload(); }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', data.message || 'Error al procesar el archivo', 'error');
            } else { alert('Error: ' + (data.message || 'Error al procesar el archivo')); }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btnSubmit.innerHTML = originalText;
        btnSubmit.disabled = false;
        cerrarModalCargaMasiva();
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error de Conexión', 'Ocurrió un problema en el servidor al procesar el archivo.', 'error');
        } else { alert('Error de conexión'); }
    });
}
</script>
<?php endif; ?>