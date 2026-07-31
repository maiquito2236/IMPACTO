// =====================================================
// OTROS CATÁLOGOS ODONTO ESTÉTICA — Admin
// =====================================================

document.addEventListener("DOMContentLoaded", () => {
    bindTabs();
    initDataTables();
});

let currentActiveTab = 'procedimientos';

// ── 1. LÓGICA DE PESTAÑAS (TABS) ──────────────────
function activarTab(target) {
    const validTabs = ['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades'];
    if (!validTabs.includes(target)) {
        target = 'procedimientos';
    }

    currentActiveTab = target;

    const tabBtns = document.querySelectorAll('.tab-btn-otros');
    const tabContents = document.querySelectorAll('.tab-content-otros');

    tabBtns.forEach(b => {
        if (b.getAttribute('data-tab') === target) {
            b.classList.add('active');
        } else {
            b.classList.remove('active');
        }
    });

    tabContents.forEach(content => {
        if (content.id === target + 'Content') {
            content.style.setProperty('display', 'block', 'important');
        } else {
            content.style.setProperty('display', 'none', 'important');
        }
    });

    localStorage.setItem('activeOtrosTab', target);

    if (history.replaceState) {
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + target;
        window.history.replaceState({ path: newUrl }, '', newUrl);
    }

    if (window.jQuery && $.fn.dataTable) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    }
}

function bindTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn-otros');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-tab');
            activarTab(target);
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab') || window.location.hash.replace('#', '');
    const savedTab = localStorage.getItem('activeOtrosTab');

    let tabToActivate = 'procedimientos';
    if (tabParam && ['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades'].includes(tabParam.toLowerCase())) {
        tabToActivate = tabParam.toLowerCase();
    } else if (savedTab && ['procedimientos', 'especialidades', 'eps', 'alergias', 'enfermedades'].includes(savedTab)) {
        tabToActivate = savedTab;
    }

    activarTab(tabToActivate);
}

// ── 2. INICIALIZACIÓN DATATABLES ──────────────
function initDataTables() {
    const commonOptions = {
        "dom": '<"top">rt<"bottom"p><"clear">',
        "pageLength": 5,
        "autoWidth": false,
        "language": {
            "search": "Buscar:",
            "lengthMenu": "Mostrar _MENU_ registros",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 registros",
            "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" },
            "zeroRecords": "No se encontraron resultados"
        }
    };

    // Inicializar tabla Procedimientos (Nombre col 1, Acciones col 6)
    const tableProc = $('#procedimientosTable').DataTable({
        ...commonOptions,
        "columnDefs": [ { "orderable": false, "targets": [1, 6] } ]
    });

    // Inicializar tabla EPS (Nombre col 1, Acciones col 3)
    const tableEps = $('#epsTable').DataTable({
        ...commonOptions,
        "columnDefs": [ { "orderable": false, "targets": [1, 3] } ]
    });

    // Inicializar tabla Especialidades (Nombre col 1, Acciones col 3)
    const tableEsp = $('#especialidadesTable').DataTable({
        ...commonOptions,
        "columnDefs": [ { "orderable": false, "targets": [1, 3] } ]
    });

    // Inicializar tabla Alergias (Nombre col 1, Acciones col 4)
    const tableAlg = $('#alergiasTable').DataTable({
        ...commonOptions,
        "columnDefs": [ { "orderable": false, "targets": [1, 4] } ]
    });

    // Inicializar tabla Enfermedades (Nombre col 1, Acciones col 4)
    const tableEnf = $('#enfermedadesTable').DataTable({
        ...commonOptions,
        "columnDefs": [ { "orderable": false, "targets": [1, 4] } ]
    });

    // Conectar buscadores personalizados
    $('#searchInput').on('keyup', function() { tableProc.search(this.value).draw(); });
    $('#searchInputEps').on('keyup', function() { tableEps.search(this.value).draw(); });
    $('#searchInputEspecialidad').on('keyup', function() { tableEsp.search(this.value).draw(); });
    $('#searchInputAlergia').on('keyup', function() { tableAlg.search(this.value).draw(); });
    $('#searchInputEnfermedad').on('keyup', function() { tableEnf.search(this.value).draw(); });
}

// ── 3. MODAL: NUEVO PROCEDIMIENTO ─────────────────
const btnNuevo = document.getElementById("btnNuevoProcedimiento");
const modalProc = document.getElementById("modalProcedimiento");
const closeModal = document.getElementById("closeModalProcedimiento");
const formProc = document.getElementById("formProcedimiento");

if (btnNuevo) {
    btnNuevo.addEventListener("click", () => {
        document.getElementById("formProcId").value = "";
        document.getElementById("formProcedimiento").reset();
        document.querySelector("#modalProcTitle span").textContent = "Nuevo Procedimiento";
        document.querySelector("#modalProcTitle i").className = "fa-solid fa-plus-circle";
        document.getElementById("modalProcSubmitBtn").innerHTML = '<i class="fa-solid fa-check"></i> Guardar Procedimiento';
        modalProc.style.display = "flex";
    });
}
if (closeModal) closeModal.addEventListener("click", () => modalProc.style.display = "none");

window.addEventListener("click", e => {
    if (e.target === modalProc) modalProc.style.display = "none";
});

// Función para abrir el modal en modo edición
window.abrirModalEditar = function(id, nombre, descripcion, costo, tiempo, estado) {
    document.getElementById("formProcId").value = id;
    document.getElementById("formProcNombre").value = nombre;
    document.getElementById("formProcDesc").value = descripcion;
    document.getElementById("formProcCosto").value = costo;
    document.getElementById("formProcTiempo").value = tiempo;
    
    // El estado viene en mayúsculas desde el HTML (ej: ACTIVO, INACTIVO)
    document.getElementById("formProcEstado").value = estado;

    document.querySelector("#modalProcTitle span").textContent = "Editar Procedimiento";
    document.querySelector("#modalProcTitle i").className = "fa-solid fa-pen";
    document.getElementById("modalProcSubmitBtn").innerHTML = '<i class="fa-solid fa-save"></i> Actualizar Procedimiento';
    
    modalProc.style.display = "flex";
};

// ── 4. GUARDAR / EDITAR PROCEDIMIENTO EN BD (FETCH) ────────
if (formProc) {
    formProc.addEventListener("submit", e => {
        e.preventDefault();
        
        const id = document.getElementById("formProcId").value;
        const nombre = document.getElementById("formProcNombre")?.value;
        const descripcion = document.getElementById("formProcDesc")?.value;
        const costo = document.getElementById("formProcCosto")?.value;
        const tiempo = document.getElementById("formProcTiempo")?.value; 
        const estado = document.getElementById("formProcEstado")?.value;
        
        if(!nombre || !costo) {
            mostrarToast("Completa los campos obligatorios", "error");
            return;
        }

        const endpoint = id ? '/LOGIN_ORIGINAL/admin/otros/editar_procedimiento' : '/LOGIN_ORIGINAL/admin/otros/guardar_procedimiento';
        const swalTitle = id ? '¡Actualizado!' : '¡Añadido!';
        const swalText = id ? 'Procedimiento actualizado exitosamente' : 'Procedimiento añadido exitosamente';
        
        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id_procedimiento: id,
                nombre_procedimiento: nombre, 
                descripcion: descripcion,
                costo: costo,
                tiempo_estimado: tiempo,
                estado: estado
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === "success") {
                modalProc.style.display = "none";
                formProc.reset();
                Swal.fire({
                    title: swalTitle,
                    text: swalText,
                    icon: 'success',
                    confirmButtonColor: '#3b82f6',
                    timer: 2000
                }).then(() => {
                    window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                });
            } else {
                mostrarToast("Error: " + (res.message || "Intenta de nuevo"), "error");
            }
        })
        .catch(() => mostrarToast("No se pudo conectar con el servidor", "error"));
    });
}

// ── 5. ELIMINAR PROCEDIMIENTO (SWEETALERT2) ───────
window.eliminarProcedimiento = function(id) {
    Swal.fire({
        title: '¿Inactivar procedimiento?',
        text: "El procedimiento pasará a estado Inactivo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: '<i class="fa-solid fa-power-off"></i> Sí, inactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/LOGIN_ORIGINAL/admin/otros/eliminar_procedimiento', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_procedimiento: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === "success") {
                    Swal.fire({
                        title: '¡Deshabilitado!',
                        text: 'El procedimiento ha sido deshabilitado correctamente.',
                        icon: 'success',
                        confirmButtonColor: '#3b82f6',
                        timer: 2000
                    }).then(() => {
                        window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                    });
                } else {
                    mostrarToast("Error al inactivar", "error");
                }
            })
            .catch(() => mostrarToast("Error de conexión", "error"));
        }
    });
};

// ── 5.5 LÓGICA ESPECIALIDADES ─────────
const modalEsp = document.getElementById("modalEspecialidad");
const btnNuevaEsp = document.getElementById("btnNuevaEspecialidad");
const closeEsp = document.getElementById("closeModalEspecialidad");
const formEsp = document.getElementById("formEspecialidad");

if (btnNuevaEsp) {
    btnNuevaEsp.addEventListener("click", () => {
        document.getElementById("formEspId").value = "";
        formEsp.reset();
        document.getElementById("modalEspTitle").innerHTML = '<i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva Especialidad</span>';
        document.getElementById("modalEspSubmitBtn").innerHTML = '<i class="fa-solid fa-check"></i> Guardar Especialidad';
        modalEsp.style.display = "flex";
    });
}

if (closeEsp) {
    closeEsp.addEventListener("click", () => modalEsp.style.display = "none");
}

window.abrirModalEditarEspecialidad = function(id, nombre, estado) {
    document.getElementById("formEspId").value = id;
    document.getElementById("formEspNombre").value = nombre;
    document.getElementById("formEspEstado").value = estado;

    document.getElementById("modalEspTitle").innerHTML = '<i class="fa-solid fa-pen" style="color:#0d6efd;margin-right:8px;"></i><span>Editar Especialidad</span>';
    document.getElementById("modalEspSubmitBtn").innerHTML = '<i class="fa-solid fa-save"></i> Actualizar Especialidad';
    
    modalEsp.style.display = "flex";
};

if (formEsp) {
    formEsp.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const data = {
            id_especialidad: document.getElementById("formEspId").value,
            nombre_especialidad: document.getElementById("formEspNombre").value.trim(),
            estado: document.getElementById("formEspEstado").value
        };

        const isEdit = data.id_especialidad !== "";
        const url = isEdit ? '/LOGIN_ORIGINAL/admin/otros/editar_especialidad' : '/LOGIN_ORIGINAL/admin/otros/guardar_especialidad';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                modalEsp.style.display = "none";
                Swal.fire({
                    title: isEdit ? '¡Actualizada!' : '¡Éxito!',
                    text: isEdit ? 'Especialidad actualizada correctamente.' : 'Especialidad guardada correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#3b82f6',
                    timer: 2000
                }).then(() => {
                    window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                });
            } else {
                mostrarToast("Error: " + (res.message || "Error desconocido"), "error");
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast("Error de conexión al servidor", "error");
        });
    });
}

window.eliminarEspecialidad = function(id) {
    Swal.fire({
        title: '¿Inactivar Especialidad?',
        text: "La especialidad pasará a estado Inactivo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: '<i class="fa-solid fa-power-off"></i> Sí, inactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/LOGIN_ORIGINAL/admin/otros/eliminar_especialidad', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_especialidad: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === "success") {
                    Swal.fire({
                        title: '¡Deshabilitada!',
                        text: 'La especialidad ha sido deshabilitada correctamente.',
                        icon: 'success',
                        confirmButtonColor: '#3b82f6',
                        timer: 2000
                    }).then(() => {
                        window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                    });
                } else {
                    mostrarToast("Error al inactivar", "error");
                }
            })
            .catch(() => mostrarToast("Error de conexión", "error"));
        }
    });
};

// ── 5.6 LÓGICA EPS ─────────
const modalEps = document.getElementById("modalEps");
const btnNuevaEps = document.getElementById("btnNuevaEps");
const closeEps = document.getElementById("closeModalEps");
const formEps = document.getElementById("formEps");

if (btnNuevaEps) {
    btnNuevaEps.addEventListener("click", () => {
        document.getElementById("formEpsId").value = "";
        formEps.reset();
        document.getElementById("modalEpsTitle").innerHTML = '<i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva EPS</span>';
        document.getElementById("modalEpsSubmitBtn").innerHTML = '<i class="fa-solid fa-check"></i> Guardar EPS';
        modalEps.style.display = "flex";
    });
}

if (closeEps) {
    closeEps.addEventListener("click", () => modalEps.style.display = "none");
}

if (formEps) {
    formEps.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const data = {
            id_eps: document.getElementById("formEpsId").value,
            nombre_eps: document.getElementById("formEpsNombre").value.trim(),
            estado: document.getElementById("formEpsEstado").value
        };

        const isEdit = data.id_eps !== "";
        const url = isEdit ? '/LOGIN_ORIGINAL/admin/otros/editar_eps' : '/LOGIN_ORIGINAL/admin/otros/guardar_eps';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                modalEps.style.display = "none";
                Swal.fire({
                    title: isEdit ? '¡Actualizada!' : '¡Éxito!',
                    text: isEdit ? 'EPS actualizada correctamente.' : 'EPS guardada correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#3b82f6',
                    timer: 2000
                }).then(() => {
                    window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                });
            } else {
                mostrarToast("Error: " + (res.message || "Error desconocido"), "error");
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast("Error de conexión al servidor", "error");
        });
    });
}

window.abrirModalEditarEps = function(id, nombre, estado) {
    document.getElementById("formEpsId").value = id;
    document.getElementById("formEpsNombre").value = nombre;
    document.getElementById("formEpsEstado").value = estado;

    document.getElementById("modalEpsTitle").innerHTML = '<i class="fa-solid fa-pen" style="color:#0d6efd;margin-right:8px;"></i><span>Editar EPS</span>';
    document.getElementById("modalEpsSubmitBtn").innerHTML = '<i class="fa-solid fa-save"></i> Actualizar EPS';
    
    modalEps.style.display = "flex";
};

window.eliminarEps = function(id) {
    Swal.fire({
        title: '¿Inactivar EPS?',
        text: "La EPS pasará a estado Inactivo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: '<i class="fa-solid fa-power-off"></i> Sí, inactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/LOGIN_ORIGINAL/admin/otros/eliminar_eps', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_eps: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === "success") {
                    Swal.fire({
                        title: '¡Deshabilitada!',
                        text: 'La EPS ha sido deshabilitada correctamente.',
                        icon: 'success',
                        confirmButtonColor: '#3b82f6',
                        timer: 2000
                    }).then(() => {
                        window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                    });
                } else {
                    mostrarToast("Error al inactivar", "error");
                }
            })
            .catch(() => mostrarToast("Error de conexión", "error"));
        }
    });
};

// ── 5.7 LÓGICA ALERGIAS ─────────
const modalAlg = document.getElementById("modalAlergia");
const btnNuevaAlg = document.getElementById("btnNuevaAlergia");
const closeAlg = document.getElementById("closeModalAlergia");
const formAlg = document.getElementById("formAlergia");

if (btnNuevaAlg) {
    btnNuevaAlg.addEventListener("click", () => {
        document.getElementById("formAlgId").value = "";
        formAlg.reset();
        document.getElementById("modalAlgTitle").innerHTML = '<i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva Alergia</span>';
        document.getElementById("modalAlgSubmitBtn").innerHTML = '<i class="fa-solid fa-check"></i> Guardar Alergia';
        modalAlg.style.display = "flex";
    });
}

if (closeAlg) {
    closeAlg.addEventListener("click", () => modalAlg.style.display = "none");
}

if (formAlg) {
    formAlg.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const data = {
            id_alergia: document.getElementById("formAlgId").value,
            nombre_alergia: document.getElementById("formAlgNombre").value.trim(),
            descripcion: document.getElementById("formAlgDesc").value.trim(),
            estado: document.getElementById("formAlgEstado").value
        };

        const isEdit = data.id_alergia !== "";
        const url = isEdit ? '/LOGIN_ORIGINAL/admin/otros/editar_alergia' : '/LOGIN_ORIGINAL/admin/otros/guardar_alergia';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                modalAlg.style.display = "none";
                Swal.fire({
                    title: isEdit ? '¡Actualizada!' : '¡Éxito!',
                    text: isEdit ? 'Alergia actualizada correctamente.' : 'Alergia guardada correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#3b82f6',
                    timer: 2000
                }).then(() => {
                    window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                });
            } else {
                mostrarToast("Error: " + (res.message || "Error desconocido"), "error");
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast("Error de conexión al servidor", "error");
        });
    });
}

window.abrirModalEditarAlergia = function(id, nombre, descripcion, estado) {
    document.getElementById("formAlgId").value = id;
    document.getElementById("formAlgNombre").value = nombre;
    document.getElementById("formAlgDesc").value = descripcion;
    document.getElementById("formAlgEstado").value = estado;

    document.getElementById("modalAlgTitle").innerHTML = '<i class="fa-solid fa-pen" style="color:#0d6efd;margin-right:8px;"></i><span>Editar Alergia</span>';
    document.getElementById("modalAlgSubmitBtn").innerHTML = '<i class="fa-solid fa-save"></i> Actualizar Alergia';
    
    modalAlg.style.display = "flex";
};

window.eliminarAlergia = function(id) {
    Swal.fire({
        title: '¿Inactivar Alergia?',
        text: "La alergia pasará a estado Inactivo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: '<i class="fa-solid fa-power-off"></i> Sí, inactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/LOGIN_ORIGINAL/admin/otros/eliminar_alergia', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_alergia: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === "success") {
                    Swal.fire({
                        title: '¡Deshabilitada!',
                        text: 'La alergia ha sido deshabilitada correctamente.',
                        icon: 'success',
                        confirmButtonColor: '#3b82f6',
                        timer: 2000
                    }).then(() => {
                        window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                    });
                } else {
                    mostrarToast("Error al inactivar", "error");
                }
            })
            .catch(() => mostrarToast("Error de conexión", "error"));
        }
    });
};

// ── 5.8 LÓGICA ENFERMEDADES ─────────
const modalEnf = document.getElementById("modalEnfermedad");
const btnNuevaEnf = document.getElementById("btnNuevaEnfermedad");
const closeEnf = document.getElementById("closeModalEnfermedad");
const formEnf = document.getElementById("formEnfermedad");

if (btnNuevaEnf) {
    btnNuevaEnf.addEventListener("click", () => {
        document.getElementById("formEnfId").value = "";
        formEnf.reset();
        document.getElementById("modalEnfTitle").innerHTML = '<i class="fa-solid fa-plus-circle" style="color:#0d6efd;margin-right:8px;"></i><span>Nueva Enfermedad</span>';
        document.getElementById("modalEnfSubmitBtn").innerHTML = '<i class="fa-solid fa-check"></i> Guardar Enfermedad';
        modalEnf.style.display = "flex";
    });
}

if (closeEnf) {
    closeEnf.addEventListener("click", () => modalEnf.style.display = "none");
}

if (formEnf) {
    formEnf.addEventListener("submit", (e) => {
        e.preventDefault();
        
        const data = {
            id_enfermedad: document.getElementById("formEnfId").value,
            nombre_enfermedad: document.getElementById("formEnfNombre").value.trim(),
            descripcion: document.getElementById("formEnfDesc").value.trim(),
            estado: document.getElementById("formEnfEstado").value
        };

        const isEdit = data.id_enfermedad !== "";
        const url = isEdit ? '/LOGIN_ORIGINAL/admin/otros/editar_enfermedad' : '/LOGIN_ORIGINAL/admin/otros/guardar_enfermedad';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                modalEnf.style.display = "none";
                Swal.fire({
                    title: isEdit ? '¡Actualizada!' : '¡Éxito!',
                    text: isEdit ? 'Enfermedad actualizada correctamente.' : 'Enfermedad guardada correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#3b82f6',
                    timer: 2000
                }).then(() => {
                    window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                });
            } else {
                mostrarToast("Error: " + (res.message || "Error desconocido"), "error");
            }
        })
        .catch(err => {
            console.error(err);
            mostrarToast("Error de conexión al servidor", "error");
        });
    });
}

window.abrirModalEditarEnfermedad = function(id, nombre, descripcion, estado) {
    document.getElementById("formEnfId").value = id;
    document.getElementById("formEnfNombre").value = nombre;
    document.getElementById("formEnfDesc").value = descripcion;
    document.getElementById("formEnfEstado").value = estado;

    document.getElementById("modalEnfTitle").innerHTML = '<i class="fa-solid fa-pen" style="color:#0d6efd;margin-right:8px;"></i><span>Editar Enfermedad</span>';
    document.getElementById("modalEnfSubmitBtn").innerHTML = '<i class="fa-solid fa-save"></i> Actualizar Enfermedad';
    
    modalEnf.style.display = "flex";
};

window.eliminarEnfermedad = function(id) {
    Swal.fire({
        title: '¿Inactivar Enfermedad?',
        text: "La enfermedad pasará a estado Inactivo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: '<i class="fa-solid fa-power-off"></i> Sí, inactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/LOGIN_ORIGINAL/admin/otros/eliminar_enfermedad', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_enfermedad: id })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === "success") {
                    Swal.fire({
                        title: '¡Deshabilitada!',
                        text: 'La enfermedad ha sido deshabilitada correctamente.',
                        icon: 'success',
                        confirmButtonColor: '#3b82f6',
                        timer: 2000
                    }).then(() => {
                        window.location.href = '/LOGIN_ORIGINAL/admin/otros?tab=' + currentActiveTab;
                    });
                } else {
                    mostrarToast("Error al inactivar", "error");
                }
            })
            .catch(() => mostrarToast("Error de conexión", "error"));
        }
    });
};

// ── 6. TOASTS ────
function mostrarToast(mensaje, tipo) {
    let cont = document.getElementById("toastCont");
    if (!cont) {
        cont = document.createElement("div"); cont.id = "toastCont";
        cont.style.cssText = "position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;";
        document.body.appendChild(cont);
    }
    const colors = { success:"#22c55e", error:"#ef4444", info:"#3b82f6" };
    const t = document.createElement("div");
    t.style.cssText = `background:white;border-left:4px solid ${colors[tipo]||colors.info};padding:12px 18px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);font-size:13.5px;color:#1e293b;max-width:340px;`;
    t.textContent = mensaje;
    cont.appendChild(t);
    setTimeout(() => { t.style.opacity="0"; t.style.transition="opacity .3s"; setTimeout(()=>t.remove(),300); }, 3500);
}