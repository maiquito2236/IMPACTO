<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gestión de Horarios</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/global.css">
  <link rel="stylesheet" href="/LOGIN_ORIGINAL/public/css/administrador/gestion_horario.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css">
</head>
<body>
 
  <div class="menu-layout">
    <?php require_once __DIR__ . '/layouts/menu.php'; ?>

    <main class="menu-main-content">
      <?php require_once __DIR__ . '/layouts/header.php'; ?>

      <div class="gh-wrapper">
        <div class="gh-header">
          <div class="gh-header__left">
            <h1 class="gh-title">Gestión de Horarios</h1>
            <p class="gh-subtitle">Administración de jornadas laborales y disponibilidad de odontólogos</p>
          </div>
          <div class="gh-header__actions">
            <button class="btn btn--primary" onclick="abrirModal('nuevo-horario')">
              <i class="fa-solid fa-plus"></i> Nuevo Horario
            </button>
            <button class="btn btn--outline" onclick="exportarDatos()">
              <i class="fa-solid fa-file-export"></i> Exportar
            </button>
          </div>
        </div>
 
        <div class="gh-kpis">
          <div class="kpi-card">
            <div class="kpi-card__icon kpi-card__icon--blue"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="kpi-card__body">
              <span class="kpi-card__label">Horarios Configurados</span>
              <span class="kpi-card__value" id="kpi-horarios-total">--</span>
              <span class="kpi-card__sub kpi-card__sub--blue">Horarios activos</span>
            </div>
          </div>
          
          <div class="kpi-card">
            <div class="kpi-card__icon kpi-card__icon--green"><i class="fa-solid fa-user-doctor"></i></div>
            <div class="kpi-card__body">
              <span class="kpi-card__label">Odontólogos Activos</span>
              <span class="kpi-card__value" id="kpi-odontologos-activos">--</span>
              <span class="kpi-card__sub kpi-card__sub--green">Profesionales en sistema</span>
            </div>
          </div> 

          <div class="kpi-card">
            <div class="kpi-card__icon kpi-card__icon--purple"><i class="fa-solid fa-clock"></i></div>
            <div class="kpi-card__body">
              <span class="kpi-card__label">Turnos Disponibles</span>
              <span class="kpi-card__value" id="kpi-turnos-disponibles">--</span>
              <span class="kpi-card__sub kpi-card__sub--purple">Listos para asignar</span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-card__icon kpi-card__icon--orange"><i class="fa-solid fa-calendar-xmark"></i></div>
            <div class="kpi-card__body">
              <span class="kpi-card__label">Turnos Ocupados</span>
              <span class="kpi-card__value" id="kpi-turnos-ocupados">--</span>
              <span class="kpi-card__sub kpi-card__sub--orange">Horarios reservados</span>
            </div>
          </div>
        </div> 

        <div class="gh-grid">
          
          <div class="gh-card">
            <div class="gh-card__head">
              <h2 class="gh-card__title">Calendario de Horarios</h2>
              <div class="calendar-nav">
                <button class="nav-btn" onclick="cambiarMesPrincipal(-1)">&#8249;</button>
                <span class="calendar-range">
                  <i class="fa-regular fa-calendar"></i>
                  <span id="mes-actual-texto">Cargando...</span>
                </span>
                <button class="nav-btn" onclick="cambiarMesPrincipal(1)">&#8250;</button>
              </div>
              <div class="view-tabs">
                <button class="view-tab view-tab--active" onclick="irAHoyPrincipal()">Ir a Hoy</button>
              </div>
            </div>
            <div class="calendar-scroll">
              <div id="calendario-mensual-container" class="calendario-mensual"></div>
            </div>
          </div>
 
          <div class="gh-card">
            <div class="gh-card__head">
              <h2 class="gh-card__title">Horarios Configurados</h2>
            </div>
            <div class="tabla-filters">
              <div class="filter-tabs" id="filter-tabs">
                <button class="filter-tab filter-tab--active" onclick="filtrarTabla('todos', this)">Todos</button>
                <button class="filter-tab" onclick="filtrarTabla('mañana', this)">Mañana</button>
                <button class="filter-tab" onclick="filtrarTabla('tarde', this)">Tarde</button>
                <button class="filter-tab" onclick="filtrarTabla('completa', this)">Jornada Completa</button>
              </div>
              <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="search-input" placeholder="Buscar odontólogo..." oninput="buscarOdontologo(this.value)" />
              </div>
            </div>
            <div class="table-scroll">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Odontólogo</th>
                    <th>Procedimiento</th>
                    <th>Consultorio</th>
                    <th>Horario</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="tabla-body">
                  </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>
    
  <div class="modal-overlay" id="modal-nuevo-horario" onclick="cerrarModalOverlay(event, 'nuevo-horario')">
    <div class="modal">
      <div class="modal__head">
        <h3 id="modal-horario-titulo">Nuevo Horario</h3>
        <button type="button" class="modal__close" onclick="cerrarModal('nuevo-horario')">&times;</button>
      </div>
      
      <form id="formHorario" class="modal__form">
        <input type="hidden" name="id_horario" id="id_horario">
        
        <div class="modal__body">
            <div class="form-group" style="margin-top: 15px;">
                <label style="font-weight: 600; color: var(--primary-600, #2b4c7e); font-size: 1.05rem; display: block; margin-bottom: 10px;">Odontólogo</label>
                <select class="form-control" name="odontologo_id" id="odontologo_id"></select>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label style="font-weight: 600; color: var(--primary-600, #2b4c7e); font-size: 1.05rem; display: block; margin-bottom: 10px;">Fecha del Horario</label>
                <input type="hidden" id="fecha_horario" name="fecha_horario">
                <div id="calendar-selector-container" style="margin-top:10px;"></div>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label style="font-weight: 600; color: var(--primary-600, #2b4c7e); font-size: 1.05rem; display: block; margin-bottom: 10px;">Jornada</label>
                <select class="form-control" name="jornada" id="jornada">
                    <option value="Mañana">Mañana</option>
                    <option value="Tarde">Tarde</option>
                    <option value="Jornada Completa">Jornada Completa</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
              <label style="font-weight: 600; color: var(--primary-600, #2b4c7e); font-size: 1.05rem; display: block; margin-bottom: 10px;">Consultorio Asignado</label>
              <input type="text" class="form-control" name="consultorio" id="consultorio" readonly style="background-color: var(--gray-100);" placeholder="Se asigna automáticamente">
          </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label style="font-weight: 600; color: var(--primary-600, #2b4c7e); font-size: 1.05rem; display: block; margin-bottom: 10px;">Estado del Horario</label>
                <select id="estado" name="estado" class="form-control">
                    <option value="Disponible">Disponible</option>
                    <option value="Ocupado">Ocupado</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label style="font-weight: 600; color: var(--primary-600, #2b4c7e); font-size: 1.05rem; display: block; margin-bottom: 10px;">Horario laboral</label>
                <div class="form-row">
                    <div class="form-group">
                        <label>Hora inicio</label>
                        <input type="time" class="form-control" name="hora_inicio" id="hora_inicio">
                    </div>
                    <div class="form-group">
                        <label>Hora fin</label>
                        <input type="time" class="form-control" name="hora_fin" id="hora_fin">
                    </div>
                </div>
            </div>

            <div class="form-group" id="grupo-descanso" style="display: none; margin-top: 15px;">
                <label style="font-weight: 600; color: var(--primary-600, #2b4c7e); font-size: 1.05rem; display: block; margin-bottom: 10px;">Seleccione el descanso</label>
                <div class="form-row">
                    <div class="form-group">
                        <label>Hora inicio</label>
                        <input type="time" class="form-control" name="descanso_inicio" id="descanso_inicio">
                    </div>
                    <div class="form-group">
                        <label>Hora fin</label>
                        <input type="time" class="form-control" name="descanso_fin" id="descanso_fin">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal__foot">
            <button type="button" id="btn-guardar-horario" class="btn btn--primary" onclick="guardarHorario()">
                Guardar Horario
            </button>
        </div>
      </form>
    </div>
  </div>
    
  <div class="modal-overlay" id="modal-detalle-dia" onclick="cerrarModalOverlay(event,'detalle-dia')">
      <div class="modal">
          <div class="modal__head">
              <h3 id="detalle-dia-fecha">Horarios del día</h3>
              <button type="button" class="modal__close" onclick="cerrarModal('detalle-dia')">&times;</button>
          </div>
          <div class="modal__body" id="detalle-dia-body">
              </div>
      </div>
  </div>
 
  <div class="toast" id="toast"></div>
 
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
  <script src="/LOGIN_ORIGINAL/public/js/administrador/gestion_horario.js?v=<?= time(); ?>"></script>
</body>
</html>