// Variable global para mantener los datos originales
let todosLosPacientes = [];

document.addEventListener("DOMContentLoaded", () => {
    const tabla = document.getElementById("tabla");
    const inputBuscar = document.getElementById("buscar");
    const filtroEstado = document.getElementById("filtroEstado");
    
    // Elementos del KPI de resumen
    const totalEl = document.getElementById("total");
    const activosEl = document.getElementById("activos");
    const inactivosEl = document.getElementById("inactivos");

    // 1. FUNCIÓN PARA RENDERIZAR LA TABLA Y ACTUALIZAR KPIS
    const renderizarTabla = (datos) => {
        tabla.innerHTML = "";
        
        let countActivos = 0;
        let countInactivos = 0;

        if (!datos || datos.length === 0) {
            tabla.innerHTML = "<tr><td colspan='6' style='text-align:center; padding: 20px; color: var(--text-muted);'>No se encontraron pacientes.</td></tr>";
            
            // Si no hay datos, todos los contadores quedan en 0
            if (totalEl) totalEl.textContent = 0;
            if (activosEl) activosEl.textContent = 0;
            if (inactivosEl) inactivosEl.textContent = 0;
            return;
        }

        datos.forEach(p => {
            // Manejo seguro de nombres e iniciales
            const nombre = p.NOMBRES || 'Sin';
            const apellido = p.APELLIDOS || 'Nombre';
            const iniciales = (nombre.charAt(0) + apellido.charAt(0)).toUpperCase();
            
            // Preparar el objeto para enviarlo al modal
            const pacienteJSON = encodeURIComponent(JSON.stringify(p));
            
            // Validación de estado para asignar estilos y sumar a KPIs
            const estado = p.ESTADO || 'Activo';
            const estadoClase = estado.toLowerCase() === 'activo' ? 'active' : 'inactive';
            
            if (estado.toLowerCase() === 'activo') {
                countActivos++;
            } else {
                countInactivos++;
            }
            
            const fila = `<tr>
                <td style="display: flex; align-items: center;">
                    <div class="avatar-circle">${iniciales}</div>
                    <div style="display: flex; flex-direction: column; margin-left: 12px;">
                        <span style="font-weight: 700; color: var(--text-main);">${nombre} ${apellido}</span>
                    </div>
                </td>
                <td>${p.NUMERO_DOCUMENTO || 'N/A'}</td>
                <td>${p.CORREO || 'N/A'}</td>
                <td>${p.TELEFONO || 'N/A'}</td>
                <td><span class="status ${estadoClase}">${estado}</span></td>
                <td>
                    <button onclick="abrirModal('${pacienteJSON}')" class="btn-action" title="Ver Detalles">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </td>
            </tr>`;
            tabla.innerHTML += fila;
        });
        
        // Actualizar KPIs de la derecha
        if (totalEl) totalEl.textContent = datos.length;
        if (activosEl) activosEl.textContent = countActivos;
        if (inactivosEl) inactivosEl.textContent = countInactivos;
    };

    // 2. FUNCIÓN DE FILTRADO (Buscador y Select)
    const aplicarFiltros = () => {
        const texto = inputBuscar.value.toLowerCase().trim();
        const estadoFiltro = filtroEstado.value;

        const filtrados = todosLosPacientes.filter(p => {
            // Convertimos valores a minúsculas de forma segura
            const nombre = (p.NOMBRES || "").toLowerCase();
            const apellido = (p.APELLIDOS || "").toLowerCase();
            const doc = (p.NUMERO_DOCUMENTO || "").toString().toLowerCase();

            const coincideBusqueda = nombre.includes(texto) || 
                                     apellido.includes(texto) || 
                                     doc.includes(texto);
            
            const pEstado = p.ESTADO || 'Activo';
            const coincideEstado = (estadoFiltro === "todos" || (pEstado.toLowerCase() === estadoFiltro.toLowerCase())); 
            
            return coincideBusqueda && coincideEstado;
        });

        renderizarTabla(filtrados);
    };

    // 3. ASIGNAR EVENTOS DE ESCUCHA
    if (inputBuscar) inputBuscar.addEventListener("input", aplicarFiltros);
    if (filtroEstado) filtroEstado.addEventListener("change", aplicarFiltros);

    // 4. PETICIÓN INICIAL DE DATOS AL BACKEND
    fetch('/LOGIN_ORIGINAL/odontologo/pacientes/listar')
        .then(response => response.json())
        .then(data => {
            todosLosPacientes = data;
            renderizarTabla(data);
        })
        .catch(err => {
            console.error("Error al cargar los pacientes:", err);
            tabla.innerHTML = "<tr><td colspan='6' style='text-align:center; color: var(--red);'>Ocurrió un error al cargar la lista de pacientes.</td></tr>";
        });
});

// 5. FUNCIÓN GLOBAL PARA MANEJAR EL MODAL
window.abrirModal = function(encodedData) {
    const paciente = JSON.parse(decodeURIComponent(encodedData));
    
    const nombre = paciente.NOMBRES || 'Sin';
    const apellido = paciente.APELLIDOS || 'Nombre';
    
    document.getElementById("modalNombre").innerText = `${nombre} ${apellido}`;
    document.getElementById("modalDocumento").innerText = "DNI: " + (paciente.NUMERO_DOCUMENTO || 'N/A');
    document.getElementById("modalEmail").innerText = paciente.CORREO || 'N/A';
    document.getElementById("modalTelefono").innerText = paciente.TELEFONO || 'N/A';
    document.getElementById("modalIniciales").innerText = (nombre.charAt(0) + apellido.charAt(0)).toUpperCase();
    
    document.getElementById("ventanaModal").style.display = "flex";
};