// ─── Estado global ───────────────────────────────────────────
let usuarios           = [];      // arreglo maestro desde la BD
let usuarioSeleccionadoId = null;
let filtroActual       = "Todos";
let especialidades     = [];      // catálogo cargado desde la BD
let consultorios       = [];      // catálogo cargado desde la BD

const API = '/LOGIN_ORIGINAL/api_gestion_usuario';

// Rol que obliga a tener especialidad + consultorio asignados
const ROL_ODONTOLOGO = 2;

const nombresRoles = {
    1: "Administrador",
    2: "Odontólogo",
    3: "Paciente",
    4: "Administrador Jefe"
};

// Colores consistentes con las variables CSS (--blue, --purple, --green, --yellow)
const coloresRoles = {
    1: "#3b82f6", // Administrador
    2: "#a855f7", // Odontólogo
    3: "#22c55e", // Paciente
    4: "#f59e0b"  // Administrador Jefe
};

/**
 * Dibuja el donut chart como SVG real (no conic-gradient) para poder
 * mostrar un tooltip nativo con el nombre del rol al pasar el cursor
 * sobre cada segmento de color.
 */
function dibujarDonut(conteoPorRol, total) {
    const donutChart = document.getElementById('donutChart');
    const radio = 60;
    const grosor = 20;
    const circunferencia = 2 * Math.PI * radio;
    const totalSeguro = total || 1;

    let acumulado = 0;
    let segmentosSvg = '';

    // Orden fijo: Paciente, Odontólogo, Administrador, Administrador Jefe
    [3, 2, 1, 4].forEach(rolId => {
        const valor = conteoPorRol[rolId] || 0;
        if (valor <= 0) return;

        const fraccion   = valor / totalSeguro;
        const largoTrazo = fraccion * circunferencia;
        const huecoTrazo = circunferencia - largoTrazo;

        segmentosSvg += `
            <circle
                cx="70" cy="70" r="${radio}"
                fill="none"
                stroke="${coloresRoles[rolId]}"
                stroke-width="${grosor}"
                stroke-dasharray="${largoTrazo} ${huecoTrazo}"
                stroke-dashoffset="${-acumulado}"
                transform="rotate(-90 70 70)"
            ><title>${nombresRoles[rolId]}: ${valor} usuario${valor === 1 ? '' : 's'}</title></circle>`;

        acumulado += largoTrazo;
    });

    donutChart.innerHTML = `
        <svg viewBox="0 0 140 140" width="140" height="140" style="position:absolute; top:0; left:0;">
            ${segmentosSvg}
        </svg>
        <div class="donut-inner">
            <h2 id="chartTotal">${total}</h2>
            <span>Total</span>
        </div>`;
}

// ─── Helpers de fetch ────────────────────────────────────────
async function apiGet(op) {
    const res  = await fetch(`${API}&op=${op}`);
    return res.json();
}

async function apiPost(op, datos) {
    const body = new URLSearchParams({ op, ...datos });
    const res  = await fetch(API, { method: 'POST', body });
    return res.json();
}

// ─── Carga inicial desde la BD ───────────────────────────────
async function cargarUsuarios() {
    try {
        const resp = await apiGet('listar');
        if (resp.ok) {
            usuarios = resp.datos;
            actualizarDashboard();
        } else {
            console.error('Error al listar usuarios:', resp.mensaje);
        }
    } catch (err) {
        console.error('Error de red al cargar usuarios:', err);
    }
}

// Carga el catálogo de especialidades para los contenedores de checkboxes.
async function cargarEspecialidades() {
    try {
        const resp = await apiGet('listar_especialidades');
        if (resp.ok) {
            especialidades = resp.datos;

            const container = document.getElementById('containerEspecialidades');
            const containerCrear = document.getElementById('containerEspecialidadesCrear');
            
            if(container) container.innerHTML = '';
            if(containerCrear) containerCrear.innerHTML = '';
            
            especialidades.forEach(esp => {
                const label = document.createElement('label');
                label.style.display = 'block';
                label.style.marginBottom = '5px';
                label.style.cursor = 'pointer';
                
                const input = document.createElement('input');
                input.type = 'checkbox';
                input.value = esp.id;
                input.className = 'especialidad-checkbox';
                input.style.marginRight = '8px';
                
                label.appendChild(input);
                label.appendChild(document.createTextNode(esp.nombre));
                
                if(container) container.appendChild(label);
                
                if(containerCrear) {
                    const labelCrear = label.cloneNode(true);
                    labelCrear.querySelector('input').className = 'especialidad-checkbox-crear';
                    containerCrear.appendChild(labelCrear);
                }
            });
        } else {
            console.error('Error al listar especialidades:', resp.mensaje);
        }
    } catch (err) {
        console.error('Error de red al cargar especialidades:', err);
    }
}

function getCheckedEspecialidades(className) {
    const checkboxes = document.querySelectorAll('.' + className + ':checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Carga el catálogo de consultorios para el <select id="formConsultorio">.
async function cargarConsultorios() {
    try {
        const resp = await apiGet('listar_consultorios');
        if (resp.ok) {
            consultorios = resp.datos;

            const select = document.getElementById('formConsultorio');
            const selectCrear = document.getElementById('formConsultorioCrear');
            select.innerHTML = '<option value="">Seleccione un consultorio...</option>';
            if(selectCrear) selectCrear.innerHTML = '<option value="">Seleccione un consultorio...</option>';
            consultorios.forEach(cons => {
                const opt = document.createElement('option');
                opt.value = cons.id;
                opt.textContent = cons.nombre;
                select.appendChild(opt);
                if(selectCrear) selectCrear.appendChild(opt.cloneNode(true));
            });
        } else {
            console.error('Error al listar consultorios:', resp.mensaje);
        }
    } catch (err) {
        console.error('Error de red al cargar consultorios:', err);
    }
}

// ─── Renderizado y métricas ──────────────────────────────────
function actualizarDashboard() {
    const tableBody = document.getElementById('tableBody');
    const busqueda  = document.getElementById('searchInput').value.toLowerCase();

    tableBody.innerHTML = '';

    const filtrados = usuarios.filter(u => {
        let cumpleFiltro = false;
        if (filtroActual === "Todos")      cumpleFiltro = true;
        else if (filtroActual === "Paciente")   cumpleFiltro = (parseInt(u.rol_id) === 3);
        else if (filtroActual === "Odontólogo") cumpleFiltro = (parseInt(u.rol_id) === 2);
        else if (filtroActual === "Personal")   cumpleFiltro = (parseInt(u.rol_id) === 1 || parseInt(u.rol_id) === 4);

        const cumpleBusqueda = u.nombre.toLowerCase().includes(busqueda)
                            || u.email.toLowerCase().includes(busqueda);
        return cumpleFiltro && cumpleBusqueda;
    });

    if (filtrados.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="4" style="text-align:center; padding:32px; color:#94a3b8; font-size:14px;">
                    <i class="fa-solid fa-user-slash" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                    No se encontraron usuarios
                </td>
            </tr>`;
    } else {
        filtrados.forEach(u => {
            const rolId = parseInt(u.rol_id);
            let roleBadgeClass = 'yellow-bg';
            if (rolId === 3) roleBadgeClass = 'green-bg';
            if (rolId === 2) roleBadgeClass = 'purple-bg';
            if (rolId === 1) roleBadgeClass = 'blue-bg';

            const estadoBadgeClass = (u.estado === 'Activo') ? 'green-bg' : 'red-bg';
            const rolNombre = u.rol_nombre || nombresRoles[rolId] || 'Sin Rol';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="user-cell">
                        <img src="${u.img}" alt="${u.nombre}">
                        <div>
                            <strong>${u.nombre}</strong>
                            <span>${u.email}</span>
                        </div>
                    </div>
                </td>
                <td><span class="status-badge ${roleBadgeClass}">${rolNombre}</span></td>
                <td><span class="status-badge ${estadoBadgeClass}">${u.estado}</span></td>
                <td>
                    <span class="action-icon" onclick="mostrarMenuAcciones(event, ${u.id})">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </span>
                </td>`;
            tableBody.appendChild(tr);
        });
    }

    // Métricas KPI
    const nPacientes   = usuarios.filter(u => parseInt(u.rol_id) === 3).length;
    const nOdontologos = usuarios.filter(u => parseInt(u.rol_id) === 2).length;
    const nAdmins      = usuarios.filter(u => parseInt(u.rol_id) === 1).length;
    const nJefes       = usuarios.filter(u => parseInt(u.rol_id) === 4).length;

    document.getElementById('kpi-totales').textContent     = usuarios.length;
    document.getElementById('kpi-pacientes').textContent   = nPacientes;
    document.getElementById('kpi-odontologos').textContent = nOdontologos;
    document.getElementById('legPacientes').textContent    = nPacientes;
    document.getElementById('legOdontologos').textContent  = nOdontologos;
    document.getElementById('legAdmins').textContent       = nAdmins;
    document.getElementById('legJefes').textContent        = nJefes;
    document.getElementById('roleCountJefe').textContent   = nJefes;
    document.getElementById('roleCountAdmin').textContent  = nAdmins;
    document.getElementById('roleCountOdon').textContent   = nOdontologos;
    document.getElementById('roleCountPac').textContent    = nPacientes;

    dibujarDonut(
        { 1: nAdmins, 2: nOdontologos, 3: nPacientes, 4: nJefes },
        usuarios.length
    );
}

// ─── Eventos de filtro y búsqueda ────────────────────────────
document.getElementById('searchInput').addEventListener('input', actualizarDashboard);

document.querySelectorAll('.tab-btn').forEach(tab => {
    tab.addEventListener('click', (e) => {
        document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
        e.target.classList.add('active');
        filtroActual = e.target.getAttribute('data-filter');
        actualizarDashboard();
    });
});


// ─── Modal: Nuevo Usuario ─────────────────────────────────────
document.getElementById('btnNuevoUsuario').addEventListener('click', () => {
    // Leemos quién está usando el sistema
    const rolActual = parseInt(document.getElementById('rolSesionActual').value) || 1;
    const opcionAdmin = document.querySelector('#formRol option[value="1"]');

    // Si es Administrador normal (1), bloqueamos la opción de crear administradores
    if (rolActual === 1) {
        opcionAdmin.disabled = true;
        opcionAdmin.hidden = true;
    } else {
        // Si es Administrador Jefe (4), habilitamos la opción
        opcionAdmin.disabled = false;
        opcionAdmin.hidden = false;
    }

    document.getElementById('modalUsuario').style.display = 'flex';
});
document.getElementById('closeModal').addEventListener('click', () => {
    document.getElementById('modalUsuario').style.display = 'none';
});
document.getElementById('formRol').addEventListener('change', function() {
    const esOdon = (parseInt(this.value) === 2);
    document.getElementById('camposOdontologoCrear').style.display = esOdon ? 'block' : 'none';
    document.getElementById('formConsultorioCrear').required = esOdon;
});

document.getElementById('formUsuario').addEventListener('submit', async (e) => {
    e.preventDefault();

    

    const nombre   = document.getElementById('formNombre').value.trim();
    const email    = document.getElementById('formEmail').value.trim();
    const telefono = document.getElementById('formTelefono').value.trim();
    const rol_id   = document.getElementById('formRol').value;

    if (!nombre || !email || !telefono) {
        Swal.fire('Atención', 'Nombre, correo y teléfono son requeridos.', 'warning');
        return;
    }
    
    if (telefono.length !== 10) {
        Swal.fire('Atención', 'El teléfono debe tener exactamente 10 dígitos.', 'warning');
        return;
    }

    const datosCrear = { nombre, email, telefono, rol_id };
    if (parseInt(rol_id) === 2) {
        const especialidades = getCheckedEspecialidades('especialidad-checkbox-crear');
        const consultorio = document.getElementById('formConsultorioCrear').value;
        if (especialidades.length === 0 || !consultorio) {
            Swal.fire('Atención', 'Debe seleccionar al menos una especialidad y consultorio para el Odontólogo.', 'warning');
            return;
        }
        
        const formData = new URLSearchParams();
        for (const key in datosCrear) formData.append(key, datosCrear[key]);
        especialidades.forEach(eId => formData.append('especialidad_id[]', eId));
        formData.append('consultorio', consultorio);
        formData.append('op', 'crear');

        try {
            const res  = await fetch(API, { method: 'POST', body: formData });
            const resp = await res.json();
            if (resp.ok) {
                usuarios.unshift(resp.usuario);      
                actualizarDashboard();
                cargarConsultorios();
                document.getElementById('formUsuario').reset();
                document.querySelectorAll('.especialidad-checkbox-crear').forEach(cb => cb.checked = false);
                document.getElementById('modalUsuario').style.display = 'none';
                if (resp.pass_temp) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Usuario Creado',
                        html: `✅ El usuario ha sido creado con éxito.<br><br>📋 <b>Contraseña temporal:</b> ${resp.pass_temp}<br><br><small>Comunícasela al usuario para que cambie su contraseña al ingresar.</small>`
                    });
                } else {
                    Swal.fire('¡Éxito!', 'Usuario creado correctamente', 'success');
                }
            } else {
                Swal.fire('Error', resp.mensaje, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Error de conexión al crear el usuario.', 'error');
            console.error(err);
        }
        return;
    }

    try {
        const resp = await apiPost('crear', datosCrear);
        if (resp.ok) {
            usuarios.unshift(resp.usuario);      
            actualizarDashboard();
            cargarConsultorios();
            document.getElementById('formUsuario').reset();
            document.getElementById('modalUsuario').style.display = 'none';
            if (resp.pass_temp) {
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario Creado',
                    html: `✅ El usuario ha sido creado con éxito.<br><br>📋 <b>Contraseña temporal:</b> ${resp.pass_temp}<br><br><small>Comunícasela al usuario para que cambie su contraseña al ingresar.</small>`
                });
            } else {
                Swal.fire('¡Éxito!', 'Usuario creado correctamente', 'success');
            }
        } else {
            Swal.fire('Error', resp.mensaje, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Error de conexión al crear el usuario.', 'error');
        console.error(err);
    }
});

// ─── Modal: Editar Rol/Estado ─────────────────────────────────
document.getElementById('closeModalEditar').addEventListener('click', () => {
    document.getElementById('modalEditarRol').style.display = 'none';
});

window.addEventListener('click', (e) => {
    const modalCrear  = document.getElementById('modalUsuario');
    const modalEditar = document.getElementById('modalEditarRol');
    if (e.target === modalCrear)  modalCrear.style.display  = 'none';
    if (e.target === modalEditar) modalEditar.style.display = 'none';
});

function abrirModalEditarRol(id) {
    const usuario = usuarios.find(u => parseInt(u.id) === id);
    const rolActual = parseInt(document.getElementById('rolSesionActual').value) || 1;
    if (!usuario) return;

    document.getElementById('editUsuarioId').value       = usuario.id;
    document.getElementById('editNombreUsuario').value   = usuario.nombre;
    document.getElementById('formNuevoRol').value        = usuario.rol_id;
    document.getElementById('formNuevoEstado').value     = usuario.estado;

    // Si el usuario ya es (o fue) Odontólogo, precarga su especialidad y
    // consultorio actuales para que el admin solo los confirme o cambie.
    document.querySelectorAll('.especialidad-checkbox').forEach(cb => cb.checked = false);
    if (usuario.especialidad_id) {
        const userEspArr = usuario.especialidad_id.toString().split(',');
        userEspArr.forEach(eId => {
            const cb = document.querySelector(`.especialidad-checkbox[value="${eId}"]`);
            if (cb) cb.checked = true;
        });
    }
    const selectConsultorio = document.getElementById('formConsultorio');
    if (usuario.consultorio) {
        let optionExists = false;
        Array.from(selectConsultorio.options).forEach(opt => {
            if (parseInt(opt.value) === parseInt(usuario.consultorio)) {
                optionExists = true;
            }
        });
        if (!optionExists) {
            const opt = document.createElement('option');
            opt.value = usuario.consultorio;
            opt.textContent = `${usuario.consultorio_nombre || 'Consultorio ' + usuario.consultorio} (Actual)`;
            selectConsultorio.appendChild(opt);
        }
    }
    selectConsultorio.value = usuario.consultorio || '';

    const alertaJefe   = document.getElementById('alertaAdminJefe');
    const alertaNormal = document.getElementById('alertaAdminNormal'); // Instanciamos la nueva alerta
    const selectRol    = document.getElementById('formNuevoRol');
    const selectEst    = document.getElementById('formNuevoEstado');
    const btnGuardar   = document.getElementById('btnGuardarRol');

    // Deshabilitar la opción de Administrador (1) si quien edita es un Administrador Normal (1)
    const opcionAdminEdit = document.querySelector('#formNuevoRol option[value="1"]');
    if (rolActual === 1) {
        if (opcionAdminEdit) {
            opcionAdminEdit.disabled = true;
            opcionAdminEdit.hidden = true;
        }
    } else {
        if (opcionAdminEdit) {
            opcionAdminEdit.disabled = false;
            opcionAdminEdit.hidden = false;
        }
    }

   if (parseInt(usuario.rol_id) === 4) {   
        // 1. BLOQUEO: Nadie toca al Administrador Jefe
        alertaJefe.style.display   = 'block';
        alertaNormal.style.display = 'none';
        
        selectRol.disabled       = true;
        selectEst.disabled       = true;
        btnGuardar.disabled      = true;
        btnGuardar.style.opacity = '0.5';
        btnGuardar.style.cursor  = 'not-allowed';
    } else if (parseInt(usuario.rol_id) === 1 && rolActual === 1) {
        // 2. BLOQUEO: Admin normal intentando editar a otro Admin normal
        alertaJefe.style.display   = 'none';
        alertaNormal.style.display = 'block'; // Mostramos la nueva alerta naranja
        
        selectRol.disabled       = true;
        selectEst.disabled       = true;
        btnGuardar.disabled      = true;
        btnGuardar.style.opacity = '0.5';
        btnGuardar.style.cursor  = 'not-allowed';
    }else {
        // 3. PERMITIDO: Editar pacientes, odontólogos, o si eres el Jefe editando a un admin
        alertaJefe.style.display   = 'none';
        alertaNormal.style.display = 'none'; // Ocultamos ambas alertas
        
        selectRol.disabled       = false;
        selectEst.disabled       = false;
        btnGuardar.disabled      = false;
        btnGuardar.style.opacity = '1';
        btnGuardar.style.cursor  = 'pointer';
    }

    actualizarVisibilidadCamposOdontologo();

    document.getElementById('modalEditarRol').style.display = 'flex';
    document.getElementById('contextMenu').style.display    = 'none';
}

/**
 * Muestra/oculta (y marca como obligatorios) los campos de Especialidad y
 * Consultorio según el rol seleccionado en el modal de edición. Cuando el
 * rol elegido es Odontólogo, ambos campos se vuelven visibles y required;
 * para cualquier otro rol quedan ocultos y se limpia su "required" para
 * no bloquear el envío del formulario.
 */
function actualizarVisibilidadCamposOdontologo() {
    const rolSeleccionado = parseInt(document.getElementById('formNuevoRol').value);
    const contenedor      = document.getElementById('camposOdontologo');
    const inputConsultorio = document.getElementById('formConsultorio');

    const esOdontologo = (rolSeleccionado === ROL_ODONTOLOGO);

    contenedor.style.display = esOdontologo ? 'block' : 'none';
    inputConsultorio.required = esOdontologo;
}

document.getElementById('formNuevoRol').addEventListener('change', actualizarVisibilidadCamposOdontologo);

document.getElementById('formEditarRol').addEventListener('submit', async (e) => {
    e.preventDefault();

    const id     = parseInt(document.getElementById('editUsuarioId').value);
    const rol_id = document.getElementById('formNuevoRol').value;
    const estado = document.getElementById('formNuevoEstado').value;

    const datosEnvio = { id, rol_id, estado };

    // Si el rol elegido es Odontólogo, especialidad y consultorio son
    // obligatorios: se validan aquí para una respuesta inmediata en el
    // front, pero la validación vinculante real ocurre en el backend.
    if (parseInt(rol_id) === ROL_ODONTOLOGO) {
        const especialidades = getCheckedEspecialidades('especialidad-checkbox');
        const consultorio     = document.getElementById('formConsultorio').value;

        if (especialidades.length === 0 || !consultorio) {
            Swal.fire('Atención', 'Para asignar el rol de Odontólogo debes seleccionar al menos una especialidad y un consultorio.', 'warning');
            return;
        }

        const formData = new URLSearchParams();
        for (const key in datosEnvio) formData.append(key, datosEnvio[key]);
        especialidades.forEach(eId => formData.append('especialidad_id[]', eId));
        formData.append('consultorio', consultorio);
        formData.append('op', 'actualizar_rol');
        
        try {
            const res  = await fetch(API, { method: 'POST', body: formData });
            const resp = await res.json();
            if (resp.ok) {
                const index = usuarios.findIndex(u => parseInt(u.id) === id);
                if (index !== -1) {
                    usuarios[index].rol_id    = parseInt(rol_id);
                    usuarios[index].rol_nombre = resp.rol_nombre || nombresRoles[parseInt(rol_id)];
                    usuarios[index].estado    = estado;

                    const nombresArr = especialidades.map(eId => {
                        const esp = especialidades.find(e => parseInt(e.id) === parseInt(eId));
                        return esp ? esp.nombre : null;
                    }).filter(n => n);

                    usuarios[index].especialidad_id     = especialidades.join(',');
                    usuarios[index].especialidad_nombre = nombresArr.join(', ');
                    usuarios[index].consultorio         = parseInt(consultorio);
                }
                actualizarDashboard();
                cargarConsultorios();
                document.getElementById('modalEditarRol').style.display = 'none';
                Swal.fire('¡Actualizado!', 'El usuario ha sido actualizado correctamente.', 'success');
            } else {
                Swal.fire('Error', resp.mensaje, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Error de conexión al actualizar el usuario.', 'error');
            console.error(err);
        }
        return;
    }

    try {
        const resp = await apiPost('actualizar_rol', datosEnvio);
        if (resp.ok) {
            const index = usuarios.findIndex(u => parseInt(u.id) === id);
            if (index !== -1) {
                usuarios[index].rol_id    = parseInt(rol_id);
                usuarios[index].rol_nombre = resp.rol_nombre || nombresRoles[parseInt(rol_id)];
                usuarios[index].estado    = estado;
            }
            actualizarDashboard();
            cargarConsultorios();
            document.getElementById('modalEditarRol').style.display = 'none';
            Swal.fire('¡Actualizado!', 'El usuario ha sido actualizado correctamente.', 'success');
        } else {
            Swal.fire('Error', resp.mensaje, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Error de conexión al actualizar el usuario.', 'error');
        console.error(err);
    }
});

// ─── Menú contextual ─────────────────────────────────────────
function mostrarMenuAcciones(event, id) {
    event.stopPropagation();
    usuarioSeleccionadoId = id;
    const menu = document.getElementById('contextMenu');

    menu.innerHTML = `
        <button onclick="abrirModalEditarRol(${id})" style="border-bottom: 1px solid #e2e8f0; border-radius: 4px 4px 0 0;">
            <i class="fa-solid fa-user-pen" style="color: #3b82f6;"></i> Editar Usuario
        </button>
        <button onclick="eliminarUsuarioActual()" style="color: #ef4444; border-radius: 0 0 4px 4px;">
            <i class="fa-solid fa-user-slash"></i> Desactivar usuario
        </button>
    `;

    menu.style.display = 'block';
    menu.style.position = 'fixed';

    let left = event.clientX - 120;
    let top = event.clientY + 8;

    if (left < 10) left = 10;
    if (top + 100 > window.innerHeight) top = event.clientY - 90;

    menu.style.left = `${left}px`;
    menu.style.top  = `${top}px`;
}

async function eliminarUsuarioActual() {
    const usuario = usuarios.find(u => parseInt(u.id) === usuarioSeleccionadoId);
    document.getElementById('contextMenu').style.display = 'none';

    if (!usuario) return;

    const rolActual = parseInt(document.getElementById('rolSesionActual').value) || 1;

    if (parseInt(usuario.rol_id) === 4) {
        Swal.fire({
            icon: 'error',
            title: 'Acción Inválida',
            text: 'El Administrador Jefe del consultorio no puede ser removido del sistema.'
        });
        return;
    }
    // Bloqueo 2: Admin no elimina Admin
    if (parseInt(usuario.rol_id) === 1 && rolActual === 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Acción Restringida',
            text: 'No tienes permisos para desactivar a otros administradores. Solo el Administrador Jefe puede hacerlo.'
        });
        return;
    }

    const result = await Swal.fire({
        title: `¿Desactivar al usuario "${usuario.nombre}"?`,
        text: 'No podrá iniciar sesión, pero su historial (citas, facturas, etc.) se conserva. Podrás reactivarlo luego desde "Editar Usuario".',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    try {
        const resp = await apiPost('eliminar', { id: usuarioSeleccionadoId });
        if (resp.ok) {
            const index = usuarios.findIndex(u => parseInt(u.id) === usuarioSeleccionadoId);
            if (index !== -1) {
                usuarios[index].estado = 'Inactivo';
            }
            actualizarDashboard();
            cargarConsultorios();
            Swal.fire('¡Desactivado!', 'El usuario ha sido desactivado.', 'success');
        } else {
            Swal.fire('Error', resp.mensaje, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Error de conexión al desactivar el usuario.', 'error');
        console.error(err);
    }
}

document.addEventListener('click', () => {
    document.getElementById('contextMenu').style.display = 'none';
});

// ─── Inicialización ──────────────────────────────────────────
cargarUsuarios();
cargarEspecialidades();
cargarConsultorios();