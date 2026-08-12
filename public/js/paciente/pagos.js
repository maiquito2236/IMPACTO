$(document).ready(function () {

    // 1. INICIALIZACIÓN DE DATATABLES 2.X
    const tabla = $('#tablaPagos').DataTable({
        responsive: true,
        pageLength: 10,
        lengthChange: false,
        ordering: true, // Flechas de ordenamiento globales activas
        order: [[1, 'desc']], // Ordenar inicialmente por Fecha Emisión (Columna 1) desc
        searching: true,
        info: true,
        language: {
            search: "",
            searchPlaceholder: "Buscar procedimiento...",
            zeroRecords: "No se encontraron actividades financieras",
            emptyTable: "No hay registros disponibles en tu bandeja",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 registros",
            paginate: { previous: "Anterior", next: "Siguiente" }
        },
        layout: {
            topStart: 'search', 
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        // Definición limpia de qué columnas se pueden ordenar
       // Definición limpia de qué columnas se pueden ordenar y cómo
        columnDefs: [
            { 
                targets: 0, // Columna: Factura
                orderable: true, 
                orderSequence: ['asc', 'desc'] // Alterna entre ascendente y descendente al dar clic
            },
            { 
                targets: 1, // Columna: Fecha Emisión
                orderable: true, 
                type: 'date', // Le indica a DataTables que procese fechas cronológicas
                orderSequence: ['asc', 'desc'] // Al dar clic, alterna: ascendente -> descendente
            },
            { 
                targets: '_all', 
                orderable: false // Mantiene bloqueado el ordenamiento en las demás columnas
            }
        ],
        initComplete: function () {
            // Buscamos el contenedor del buscador recién renderizado
            $('.dt-search').addClass('d-flex align-items-center gap-2');
            
            // Inyectamos de forma segura el selector de estados dentro del buscador
            if ($('#filtroEstado').length === 0) {
                $('.dt-search').append(`
                    <select id="filtroEstado" class="form-select form-select-sm ms-2" style="width:140px">
                        <option value="">Filtrar Estado</option>
                        <option value="Pagada">Pagada</option>
                        <option value="Abonando">Abonando</option>
                        <option value="Pendiente">Pendiente</option>
                    </select>
                `);
            }
        }
    });

    // 2. DISPARADOR DEL FILTRO DE ESTADO (Columna índice 6 en base a tu HTML)
    $(document).on('change', '#filtroEstado', function () {
        const valor = $(this).val();
        // Filtra directamente usando el API nativo en la columna del Estado (índice 6)
        tabla.column(6).search(valor).draw();
    });

    // 3. CAPTURA DE EVENTO CLICK - PANEL LATERAL INFORMATIVO
$(document).on('click', '.btn-ver-detalle', function (e) {
    e.preventDefault();

    const row = $(this).closest('tr');

    const concepto = row.find('td:eq(2)').text().trim();
    const fecha = row.find('td:eq(1)').text().trim();
    const total = row.find('td:eq(3)').text().trim();
    const abonado = row.find('td:eq(4)').text().trim();
    const deuda = row.find('td:eq(5)').text().trim();
    const estado = row.find('td:eq(6)').text().trim();

    $('#det-concepto').text(concepto);
    $('#det-fecha').text(fecha);
    const especialista = $(this).data('especialista');
    $('#det-especialista').text(especialista);
    $('#det-total').text(total);
    $('#det-abonado').text(abonado);
    $('#det-deuda').text(deuda);

    $('#det-estado')
        .text(estado)
        .attr('class', 'badge px-3 py-1 fs-7');

    if (estado.includes('Pagada')) {
        $('#det-estado').addClass('bg-success');
    } else if (estado.includes('Abonando')) {
        $('#det-estado').addClass('bg-warning text-dark');
    } else {
        $('#det-estado').addClass('bg-danger');
    }

    $('#overlay, #panel-detalle').addClass('active');
});

    // 4. DISPARADOR DE IMPRESORA (MODAL CENTRAL)
    $(document).on('click', '.btn-opciones-exportar', function (e) {
        e.preventDefault();
        const idFactura = $(this).data('id');

        $('#btn-modal-pdf').attr('href', '/LOGIN_ORIGINAL/exportarPdf?id=' + idFactura);
        $('#btn-modal-print').attr('href', '/LOGIN_ORIGINAL/imprimir?id=' + idFactura);
        $('#btn-modal-excel').attr('href', '/LOGIN_ORIGINAL/exportarExcel?id=' + idFactura);

        $('#overlay, #modal-exportar').addClass('active');
    });

    // 5. EVENTO UNIFICADO DE CIERRE
    $(document).on('click', '#cerrar-detalle, #cerrar-modal-exportar, #overlay', function (e) {
        e.preventDefault();
        $('#overlay, #panel-detalle, #modal-exportar').removeClass('active'); 
    });
});
    // 6. LOGICA MODAL DE PAGOS
    $(document).on("click", ".btn-pagar-paciente", function(e) {
        e.preventDefault();
        const idFactura = $(this).data("id");
        const deuda = parseFloat($(this).data("pendiente"));

        $("#pagoFacturaId").val(idFactura);
        $("#pagoPendienteMonto").val(deuda);
        $("#pagoPendienteDisplay").val("$" + deuda.toLocaleString("es-CO"));
        $("#pagoMonto").val(deuda).attr("max", deuda);

        $("#overlay").addClass("active");
        $("#modal-pago").fadeIn(200);
    });

    $(document).on("click", "#cerrar-modal-pago", function() {
        $("#modal-pago").fadeOut(200);
        $("#overlay").removeClass("active");
        $("#formPagoPaciente")[0].reset();
    });

    // Validar maximo antes de enviar
    $("#pagoMonto").on("input", function() {
        let val = parseFloat($(this).val());
        let max = parseFloat($("#pagoPendienteMonto").val());
        if(val > max) { $(this).val(max); }
    });

    $("#formPagoPaciente").on("submit", async function(e) {
        e.preventDefault();
        
        const btn = $(this).find("button[type=submit]");
        btn.prop("disabled", true).text("Procesando...");

        const data = {
            factura_id: parseInt($("#pagoFacturaId").val()),
            monto: parseFloat($("#pagoMonto").val()),
            metodo: $("#pagoMetodo").val()
        };

        try {
            const resp = await fetch("/LOGIN_ORIGINAL/paciente/pagos/guardar", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });
            const result = await resp.json();
            
            if(result.status === "success") {
                $("#modal-pago").fadeOut(200);
                $("#overlay").removeClass("active");
                Swal.fire({
                    icon: 'success',
                    title: 'Pago enviado',
                    text: 'Pago realizado con éxito.',
                    confirmButtonColor: '#198754',
                    scrollbarPadding: false,
                    heightAuto: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || "Error al procesar pago.",
                    confirmButtonColor: '#dc3545'
                });
                btn.prop("disabled", false).text("Confirmar Pago");
            }
        } catch(error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'Hubo un problema al conectar con el servidor.',
                confirmButtonColor: '#dc3545'
            });
            btn.prop("disabled", false).text("Confirmar Pago");
        }
    });
