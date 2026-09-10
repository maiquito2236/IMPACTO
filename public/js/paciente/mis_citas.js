$(document).ready(function () {

    // =========================================================================
    // 1. INICIALIZACIÓN DE DATATABLES (V2.3 NATIVO)
    // =========================================================================
    const tabla = $('#tablaCitas').DataTable({
        responsive: true,
        pageLength: 5,
        lengthChange: true, 
        
        // En la versión 2.3+, aquí solo se ponen los valores numéricos
        lengthMenu: [ 5, 10, 25, -1 ], 
        
        ordering: true,
        info: true, 
        searching: true,
        order: [[0, 'desc']], 
        columns: [
            { orderable: true, orderSequence: ['desc', 'asc'] },  
            { orderable: true, orderSequence: ['desc', 'asc'] },  
            { orderable: false },                                 
            { orderable: false },                                 
            { orderable: true, orderSequence: ['asc', 'desc'] },  
            { orderable: true, orderSequence: ['asc', 'desc'] },  
            { orderable: false }                                  
        ],
        language: {
            lengthMenu: "_MENU_", 
            
            // ¡NUEVO EN DATATABLES 2.3! Aquí se traducen los valores del menú
            lengthLabels: {
                '-1': 'Todos'
            },
            
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros", 
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "", 
            zeroRecords: "No se encontraron citas coincidentes",
            emptyTable: "No hay citas disponibles en tu historial",
            paginate: { previous: "←", next: "→" }
        },
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: ['info', 'pageLength'], 
            bottomEnd: 'paging'
        }
    });

    // =========================================================================
    // 2. BUSCADOR Y FILTROS POR PESTAÑAS
    // =========================================================================
    $('#search-appointment').on('keyup', function () {
        tabla.search($(this).val()).draw();
    });

    $('.btn-tab-filter').on('click', function () {
        $('.btn-tab-filter').removeClass('active');
        $(this).addClass('active');

        const filterValue = $(this).data('filter');
        
        // Validamos contra 'Todas' en lugar de 'all'
        if (filterValue === 'Todas') {
            tabla.column(5).search('').draw();
        } else {
            tabla.column(5).search('^' + filterValue + '$', true, false).draw();
        }
    });

    // =========================================================================
    // 3. CONTROLADORES DE MODALES DE ACCIÓN (REPROGRAMAR Y CANCELAR EN TIEMPO REAL)
    // =========================================================================
    $(document).on('click', '.btn-reschedule', function () {
        const idCita = $(this).data('id');
        const $fila = $(this).closest('tr');
        
        const idOdontologo = $fila.find('.btn-view').data('id-odontologo') || 1;

        const hora = $fila.find('td:nth-child(2)').text().trim();
        const doctor = $fila.find('.card-doctor-name').text().trim();
        const tratamiento = $fila.find('.col-tratamiento').text().trim();
        const consultorio = $fila.find('.col-consultorio').text().trim();
        
        const dia = $fila.find('.date-badge-day').text().trim();
        const mes = $fila.find('.date-badge-month').text().trim();
        const anio = $fila.find('.date-badge-year').text().trim();
        const fechaFormateada = `${dia}/${dateToMonthNumber(mes)}/${anio}`;

        $('#rep-id-cita').val(idCita);
        $('#rep-id-odontologo').val(idOdontologo);
        $('#rep-card-fecha-hora').text(`${fechaFormateada} - ${hora}`);
        $('#rep-card-doctor-info').text(doctor);
        $('#rep-card-tratamiento-info').text(`${tratamiento} - ${consultorio}`);

        // Resetear campos de selección
        $('#rep-fecha').val('');
        $('#rep-hora').html('<option value="" disabled selected>Primero elige fecha</option>').prop('disabled', true);
        $('#rep-odontologo').html('<option value="" disabled selected>Primero elige hora</option>').prop('disabled', true);
    });

    let horariosDisponiblesPorHora = {};

    $('#rep-fecha').on('change', function() {
        const fechaSeleccionada = $(this).val();
        const $selectHora = $('#rep-hora');
        const $selectDoctor = $('#rep-odontologo');

        if (!fechaSeleccionada) return;

        $selectHora.html('<option value="" disabled selected>Buscando horas...</option>').prop('disabled', true);
        $selectDoctor.html('<option value="" disabled selected>Primero elige hora</option>').prop('disabled', true);

        const formData = new FormData();
        formData.append('fecha', fechaSeleccionada);

        fetch('/LOGIN_ORIGINAL/paciente/cita/horas-disponibles-ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data.length > 0) {
                
                // Filtrar horas que ya pasaron si es hoy
                const bogotaString = new Date().toLocaleString("en-US", { timeZone: "America/Bogota" });
                const realBogotaDate = new Date(bogotaString);
                const horaActualStr = realBogotaDate.getHours().toString().padStart(2, '0') + ':' + realBogotaDate.getMinutes().toString().padStart(2, '0') + ':00';
                
                const [yearSel, monthSel, daySel] = fechaSeleccionada.split('-');
                const esHoy = (parseInt(yearSel) === realBogotaDate.getFullYear() && (parseInt(monthSel) - 1) === realBogotaDate.getMonth() && parseInt(daySel) === realBogotaDate.getDate());
                
                let horariosDia = res.data;
                if (esHoy) {
                    horariosDia = horariosDia.filter(h => h.HORA_INICIO > horaActualStr);
                }

                if (horariosDia.length === 0) {
                    $selectHora.html('<option value="" disabled selected>No hay turnos libres para este día o ya pasaron</option>').prop('disabled', true);
                    return;
                }

                horariosDisponiblesPorHora = {};
                const horasUnicas = [];

                horariosDia.forEach(horario => {
                    if (!horariosDisponiblesPorHora[horario.HORA_INICIO]) {
                        horariosDisponiblesPorHora[horario.HORA_INICIO] = [];
                        horasUnicas.push(horario.HORA_INICIO);
                    }
                    horariosDisponiblesPorHora[horario.HORA_INICIO].push(horario);
                });

                horasUnicas.sort();
                
                let options = '<option value="" disabled selected>Seleccionar hora</option>';
                horasUnicas.forEach(horaInicio => {
                    const horaAmigable = formatMilitaryTimeToAmPm(horaInicio);
                    options += `<option value="${horaInicio}">${horaAmigable}</option>`;
                });
                
                $selectHora.html(options).prop('disabled', false);

            } else {
                $selectHora.html('<option value="" disabled selected>No hay turnos libres para este día</option>').prop('disabled', true);
            }
        })
        .catch(error => {
            console.error('Error al cargar horas:', error);
            $selectHora.html('<option value="" disabled selected>Error de conexión</option>');
        });
    });

    $('#rep-hora').on('change', function() {
        const horaSeleccionada = $(this).val();
        const doctoresArray = horariosDisponiblesPorHora[horaSeleccionada] || [];
        const $selectDoctor = $('#rep-odontologo');
        
        $selectDoctor.empty();
        
        if (doctoresArray.length > 0) {
            let options = '<option value="" disabled selected>Selecciona odontólogo</option>';
            doctoresArray.forEach(docHorario => {
                options += `<option value="${docHorario.ID_HORARIO}">Dr(a). ${docHorario.NOMBRE_DOCTOR}</option>`;
            });
            
            $selectDoctor.html(options).prop('disabled', false);
            
            // Auto-seleccionar si solo hay 1 doctor
            if (doctoresArray.length === 1) {
                $selectDoctor.prop('selectedIndex', 1);
            }
        } else {
            $selectDoctor.html('<option value="" disabled selected>Primero elige hora</option>').prop('disabled', true);
        }
    });

    function formatMilitaryTimeToAmPm(timeString) {
        const [hours, minutes] = timeString.split(':');
        let hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12;
        hour = hour ? hour : 12;
        return `${hour}:${minutes} ${ampm}`;
    }

    $(document).on('click', '.btn-cancel', function () {
        const idCita = $(this).data('id');
        const $fila = $(this).closest('tr');
        
        const hora = $fila.find('td:nth-child(2)').text().trim();
        const doctor = $fila.find('.card-doctor-name').text().trim();
        const tratamiento = $fila.find('.col-tratamiento').text().trim();
        const consultorio = $fila.find('.col-consultorio').text().trim();
        
        const dia = $fila.find('.date-badge-day').text().trim();
        const mes = $fila.find('.date-badge-month').text().trim();
        const anio = $fila.find('.date-badge-year').text().trim();
        const fechaFormateada = `${dia}/${dateToMonthNumber(mes)}/${anio}`;

        $('#canc-id-cita').val(idCita);
        $('#canc-card-fecha-hora').text(`${fechaFormateada} - ${hora}`);
        $('#canc-card-doctor-info').text(doctor);
        $('#canc-card-tratamiento-info').text(`${tratamiento} - ${consultorio}`);
        
        $('#motivo1').prop('checked', true);
        $('#canc-observaciones').val('');
    });

    function dateToMonthNumber(mesCorto) {
        const meses = { 'Jun': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04', 'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08', 'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12' };
        return meses[mesCorto] || '01';
    }

    // =========================================================================
    // 4. FLUJO DINÁMICO: DETALLES DE CITA (VER DETALLE VIA AJAX)
    // =========================================================================
    $(document).on('click', '.btn-view', function (e) {
        e.preventDefault();
        const idCita = $(this).data('id'); 
        
        if (idCita) {
            cargarDetalleCita(idCita);
        } else {
            alert('Error: No se detectó el identificador de la cita.');
        }
    });

    function cargarDetalleCita(idCita) {
        const formData = new FormData();
        formData.append('id_cita', idCita);

        fetch('/LOGIN_ORIGINAL/paciente/cita/detalle-ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                pintarModalDinamico(res.data);
            } else {
                alert(res.message || 'Error al cargar los detalles.');
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            alert('Ocurrió un error de red al intentar conectar con el servidor.');
        });
    }

    function pintarModalDinamico(cita) {
        document.getElementById('txtDetalleFecha').textContent = cita.SOLO_FECHA || '--/--/----';
        document.getElementById('txtDetalleHora').textContent = cita.SOLO_HORA || '--:-- --';
        document.getElementById('txtDetalleDoctor').textContent = cita.NOMBRE_DOCTOR ? `Dr(a). ${cita.NOMBRE_DOCTOR}` : 'Por asignar';
        document.getElementById('txtDetalleEspecialidad').textContent = cita.ESPECIALIDAD || 'General'; 
        document.getElementById('txtDetalleTratamiento').textContent = cita.LISTA_TRATAMIENTOS || 'No se registraron procedimientos';
        document.getElementById('txtDetalleConsultorio').textContent = cita.CONSULTORIO ? `Consultorio ${cita.CONSULTORIO}` : '--';
        document.getElementById('txtDetalleDientes').textContent = cita.DIENTES_TRATADOS || 'General / No especificado';
        
        const filasDinamicas = ['rowFechaAtencion', 'rowObservacionesDoc', 'rowRecomendaciones', 'rowFechaCancelacion', 'rowMotivoCancelacion', 'rowCreadaEl'];
        filasDinamicas.forEach(id => document.getElementById(id).classList.add('d-none'));

        const header = document.getElementById('wrapperEstadoHeader');
        const badge = document.getElementById('badgeEstadoCita');
        const banner = document.getElementById('bannerInfoModal');
        
        header.className = 'p-3 mb-4 d-flex align-items-center justify-content-between';
        badge.className = 'status-badge';
        banner.className = 'mt-4 p-3 d-flex align-items-start gap-3';

        badge.textContent = cita.ESTADO;

        switch (cita.ESTADO) {
            case 'Pendiente': 
            case 'Programada':
                header.classList.add('header-bg-programada');
                badge.classList.add('status-pending');
                banner.classList.add('banner-bg-programada');
                
                document.getElementById('rowCreadaEl').classList.remove('d-none');
                document.getElementById('txtDetalleCreadaEl').textContent = cita.CREADA_EL_FORMATO || '--/--/----';

                document.getElementById('bannerIcono').className = 'fas fa-info-circle style-icon-banner';
                document.getElementById('bannerTextoTitle').textContent = 'Recuerda asistir a tiempo';
                document.getElementById('bannerTextoDesc').textContent = 'Si necesitas reprogramar o cancelar tu cita, por favor hazlo con un mínimo de 2 horas de anticipación.';
                break;

            case 'Completada':
                header.classList.add('header-bg-completada');
                badge.classList.add('status-completed');
                banner.className = 'mt-4 p-3 d-flex align-items-start gap-3 banner-bg-completada';
                
                document.getElementById('rowFechaAtencion').classList.remove('d-none');
                document.getElementById('txtDetalleFechaAtencion').textContent = cita.FECHA_ATENCION_FORMATO || cita.SOLO_FECHA;
                
                document.getElementById('rowObservacionesDoc').classList.remove('d-none');
                document.getElementById('txtDetalleObservaciones').textContent = cita.OBSERVACIONES_DOCTOR || 'Sin observaciones adicionales.';
                
                document.getElementById('rowRecomendaciones').classList.remove('d-none');
                document.getElementById('txtDetalleRecomendaciones').textContent = cita.RECOMENDACIONES || 'Ninguna receta o cuidado especial asignado.';

                document.getElementById('bannerIcono').className = 'fas fa-check-circle text-success';
                document.getElementById('bannerTextoTitle').textContent = 'Cita atendida con éxito';
                document.getElementById('bannerTextoDesc').textContent = 'Tu registro clínico ha sido actualizado correctamente por el profesional médico.';
                break;

            case 'Cancelada':
                header.className = 'p-3 mb-4 d-flex align-items-center justify-content-between header-bg-cancelada';
                badge.classList.add('status-cancelled');
                banner.className = 'mt-4 p-3 d-flex align-items-start gap-3 banner-bg-cancelada';
                
                document.getElementById('rowFechaCancelacion').classList.remove('d-none');
                document.getElementById('txtDetalleFechaCancelacion').textContent = cita.FECHA_CANCELACION_FORMATO || 'No registrada';
                
                document.getElementById('rowMotivoCancelacion').classList.remove('d-none');
                document.getElementById('txtDetalleMotivoCancelacion').textContent = cita.MOTIVO_CANCELACION || 'Cancelada por el paciente';

                document.getElementById('bannerIcono').className = 'fas fa-exclamation-circle text-danger';
                document.getElementById('bannerTextoTitle').textContent = 'Esta cita fue cancelada';
                document.getElementById('bannerTextoDesc').textContent = 'Puedes agendar una nueva cita en cualquier momento seleccionando un horario disponible.';
                break;

            case 'No asistió':
                header.classList.add('header-bg-no-asistio');
                badge.classList.add('status-no-asistio');
                banner.classList.add('banner-bg-no-asistio');

                document.getElementById('bannerIcono').className = 'fas fa-user-times text-secondary';
                document.getElementById('bannerTextoTitle').textContent = 'No se registró asistencia';
                document.getElementById('bannerTextoDesc').textContent = 'El paciente no se presentó en la fecha y hora agendadas en las instalaciones.';
                break;
        }

        // =========================================================================
        // RENDERIZADO DINÁMICO DEL HISTORIAL (EXCLUSIVO REPROGRAMACIONES)
        // =========================================================================
        const wrapperHistorial = document.getElementById('wrapperHistorialReprogramaciones');
        const timeline = document.getElementById('timelineHistorial');
        
        timeline.innerHTML = ''; // Limpiar renders antiguos
        
        // Filtrar para asegurar que solo procese si hay reprogramaciones reales en la bitácora
        const reprogramaciones = cita.HISTORIAL ? cita.HISTORIAL.filter(h => h.ACCION === 'REPROGRAMACION') : [];
        
        if (reprogramaciones.length > 0) {
            wrapperHistorial.classList.remove('d-none'); // Mostrar contenedor si existen
            
            reprogramaciones.forEach(hist => {
                const item = document.createElement('div');
                item.className = 'mb-3 position-relative ps-3';
                item.style.fontSize = '13px';
                
                item.innerHTML = `
                    <div style="position: absolute; left: -23px; top: 3px; width: 10px; height: 10px; background-color: #0b57d0; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 2px #eff6ff;"></div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark-blue uppercase tracking-wider" style="font-size: 11px; color: #0b57d0;">REPROGRAMACIÓN</strong>
                        <span class="text-muted font-xs">${hist.REGISTRO_FMT}</span>
                    </div>
                    <div class="p-2 rounded-3 bg-light border border-soft text-dark-blue">
                        <div class="font-xs"><span class="text-muted-gray">Fecha anterior:</span> <del class="text-danger">${hist.FECHA_ANTERIOR_FMT}</del></div>
                        <div class="font-xs"><span class="text-muted-gray">Nueva fecha asignada:</span> <span class="text-success fw-bold">${hist.FECHA_NUEVA_FMT}</span></div>
                        <div class="font-sm mt-1 border-top border-soft pt-1" style="font-style: italic; color: #475569;">
                            " ${hist.MOTIVO || 'Sin motivo específico.'} "
                        </div>
                    </div>
                `;
                timeline.appendChild(item);
            });
        } else {
            wrapperHistorial.classList.add('d-none'); // Ocultar por completo si no hay reprogramaciones
        }
        
        $('#modalDetalleCita').modal('show');
    }
});