$(document).ready(function () {
    let paginaActual = 1;

    // Componentes de escucha de filtrado
    $('#registrosPorPagina').on('change', function() { paginaActual = 1; procesarFiltrosClinicos(); });
    $('#buscarHistoria').on('keyup', function() { paginaActual = 1; procesarFiltrosClinicos(); });

    // Alternar ordenamiento síncrono (Ascendente / Descendente)
    $('#btnAlternarOrden').on('click', function() {
        const ordenActual = $(this).attr('data-orden');
        if (ordenActual === 'desc') {
            $(this).attr('data-orden', 'asc');
            $(this).find('i').attr('class', 'fa-solid fa-sort-amount-up');
            $('#txtOrden').text('Ascendente');
        } else {
            $(this).attr('data-orden', 'desc');
            $(this).find('i').attr('class', 'fa-solid fa-sort-amount-down');
            $('#txtOrden').text('Descendente');
        }
        procesarFiltrosClinicos();
    });

    function procesarFiltrosClinicos() {
        const $contenedor = $('#contenedorHistorias');
        const $items = $('.historia-item');
        const textoBusqueda = $('#buscarHistoria').val().toLowerCase().trim();
        const limitePorPagina = parseInt($('#registrosPorPagina').val());
        const orden = $('#btnAlternarOrden').attr('data-orden');

        if ($items.length === 0) return;

        const itemsOrdenados = $items.toArray().sort(function(a, b) {
            const fechaA = parseInt($(a).attr('data-fecha'));
            const fechaB = parseInt($(b).attr('data-fecha'));
            return orden === 'desc' ? (fechaB - fechaA) : (fechaA - fechaB);
        });

        $contenedor.append(itemsOrdenados);

        let itemsFiltrados = itemsOrdenados.filter(function(item) {
            const contenidoBusqueda = $(item).attr('data-search');
            if (textoBusqueda === '') return true;
            return contenidoBusqueda.indexOf(textoBusqueda) !== -1;
        });

        $items.addClass('d-none');

        const totalRegistrosFiltrados = itemsFiltrados.length;
        const totalPaginas = Math.ceil(totalRegistrosFiltrados / limitePorPagina) || 1;

        if (paginaActual > totalPaginas) paginaActual = totalPaginas;

        const indiceInicio = (paginaActual - 1) * limitePorPagina;
        const indiceFin = Math.min(indiceInicio + limitePorPagina, totalRegistrosFiltrados);

        for (let i = indiceInicio; i < indiceFin; i++) {
            $(itemsFiltrados[i]).removeClass('d-none');
        }

        renderizarControlesPaginacion(totalPaginas, totalRegistrosFiltrados, indiceInicio, indiceFin);
    }

    function renderizarControlesPaginacion(totalPaginas, totalFiltrados, inicio, fin) {
        const $ul = $('#ulPaginacion');
        $ul.empty();

        if (totalFiltrados === 0) {
            $('#txtContadorPaginacion').text('Mostrando 0 a 0 de 0 registros');
            return;
        }

        $('#txtContadorPaginacion').text(`Mostrando ${inicio + 1} a ${fin} de ${totalFiltrados} registros`);

        const $btnPrev = $(`<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}"><a class="page-link text-dark border-0 bg-light rounded-2" href="#">←</a></li>`);
        $btnPrev.on('click', function(e) { e.preventDefault(); if(paginaActual > 1) { paginaActual--; procesarFiltrosClinicos(); } });
        $ul.append($btnPrev);

        for (let i = 1; i <= totalPaginas; i++) {
            const $pageItem = $(`<li class="page-item ${i === paginaActual ? 'active' : ''}"></li>`);
            const $pageLink = $(`<a class="page-link border-0 rounded-2 fw-bold" href="#">${i}</a>`);
            
            if (i === paginaActual) {
                $pageLink.css({ 'background-color': '#0b57d0', 'color': '#ffffff', 'padding': '6px 12px' });
            } else {
                $pageLink.addClass('text-dark bg-light').css({ 'padding': '6px 12px' });
            }

            $pageItem.on('click', function(e) { e.preventDefault(); paginaActual = i; procesarFiltrosClinicos(); });
            $pageItem.append($pageLink);
            $ul.append($pageItem);
        }

        const $btnNext = $(`<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}"><a class="page-link text-dark border-0 bg-light rounded-2" href="#">→</a></li>`);
        $btnNext.on('click', function(e) { e.preventDefault(); if(paginaActual < totalPaginas) { paginaActual++; procesarFiltrosClinicos(); } });
        $ul.append($btnNext);
    }

    procesarFiltrosClinicos();

    $(document).on('click', '.btn-view-hc', function (e) {
        e.preventDefault();
        const idCita = $(this).data('id'); 
        
        if (idCita) {
            const formData = new FormData();
            formData.append('id_cita', idCita);

            fetch('/LOGIN_ORIGINAL/paciente/cita/detalle-historial-ajax', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const cita = res.data;

                    const fechaFmt = cita.SOLO_FECHA || '--/--/----';
                    const horaFmt = cita.SOLO_HORA || '--:-- --';
                    $('#txtDetalleFechaAtencion').text(`${fechaFmt} a las ${horaFmt}`);
                    
                    $('#txtDetallePaciente').text(cita.NOMBRE_PACIENTE || 'No asignado');
                    $('#txtDetalleDocumento').text(cita.DOC_PACIENTE ? `${cita.TIPO_DOC_PACIENTE || ''} ${cita.DOC_PACIENTE}` : 'No registrado');
                    
                    $('#txtDetalleDoctor').text(cita.NOMBRE_DOCTOR ? `Dr(a). ${cita.NOMBRE_DOCTOR}` : 'No registrado');
                    $('#txtDetalleEspecialidad').text(cita.ESPECIALIDAD || 'General'); 
                    $('#txtDetalleConsultorio').text(cita.CONSULTORIO ? `Consultorio ${cita.CONSULTORIO}` : '--');
                    
                    let tratamientoHTML = (cita.TRATAMIENTO || 'Valoración / Control General').replace(/\n/g, '<br>');
                    $('#txtDetalleTratamiento').html(tratamientoHTML);
                    
                    let obs = cita.OBSERVACIONES_DOCTOR || '';
                    $('#txtDetalleObservaciones').text(obs.trim() !== "" ? obs : 'Sin observaciones.');
                    
                    let rec = cita.RECOMENDACIONES || '';
                    $('#txtDetalleRecomendaciones').text(rec.trim() !== "" ? rec : 'Sin recomendaciones.');

                    $('#modalDetalleCita').modal('show');
                } else {
                    alert("No se pudo cargar el resumen clínico de la atención.");
                }
            })
            .catch(error => {
                console.error("Error cargando los datos médicos:", error);
            });
        }
    });

    $(document).on('click', '.btn-print-hc', function (e) {
        e.preventDefault();
        const idCita = $(this).data('id'); 
        if (idCita) {
            window.open(`/LOGIN_ORIGINAL/imprimir_historial?id_cita=${idCita}&mode=print`, '_blank');
        }
    });

    $(document).on('click', '.btn-download-hc', function (e) {
        e.preventDefault();
        const idCita = $(this).data('id'); 
        if (idCita) {
            window.open(`/LOGIN_ORIGINAL/imprimir_historial?id_cita=${idCita}&mode=pdf`, '_blank');
        }
    });
});