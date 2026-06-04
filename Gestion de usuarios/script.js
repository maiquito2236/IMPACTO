// ================= BASE DE DATOS SIMULADA =================
let usuarios = [
    { id: 1, nombre: "Alejandra Torres",  email: "alejandra.torres@email.com",   tipo: "Paciente",   rol: "Paciente",   estado: "Activo", img: "https://i.pravatar.cc/150?img=5"  },
    { id: 2, nombre: "Dr. Andrés Díaz",   email: "andres.diaz@odontologia.com",  tipo: "Odontólogo", rol: "Odontólogo", estado: "Activo", img: "https://i.pravatar.cc/150?img=12" },
    { id: 3, nombre: "Carlos Mendoza",    email: "carlos.m@email.com",           tipo: "Paciente",   rol: "Paciente",   estado: "Activo", img: "https://i.pravatar.cc/150?img=8"  }
];
 
let usuarioSeleccionadoId = null;
let filtroActual = "Todos";
 
// ================= RENDERIZADO =================
function actualizarDashboard() {
    const tableBody   = document.getElementById('tableBody');
    const busqueda    = document.getElementById('searchInput').value.toLowerCase();
 
    tableBody.innerHTML = '';
 
    const filtrados = usuarios.filter(u => {
        const cumpleFiltro  = (filtroActual === "Todos" || u.tipo === filtroActual);
        const cumpleBusqueda = u.nombre.toLowerCase().includes(busqueda) || u.email.toLowerCase().includes(busqueda);
        return cumpleFiltro && cumpleBusqueda;
    });
 
    if (filtrados.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; padding:32px; color:#94a3b8; font-size:14px;">
                    <i class="fa-solid fa-user-slash" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                    No se encontraron usuarios
                </td>
            </tr>`;
    } else {
        filtrados.forEach(u => {
            const badgeClass = u.tipo === 'Paciente' ? 'green-bg' : (u.tipo === 'Odontólogo' ? 'purple-bg' : 'yellow-bg');
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
                <td><span class="status-badge ${badgeClass}">${u.tipo}</span></td>
                <td>${u.rol}</td>
                <td><span class="status-badge green-bg">${u.estado}</span></td>
                <td>
                    <span class="action-icon" onclick="mostrarMenuAcciones(event, ${u.id})">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </span>
                </td>`;
            tableBody.appendChild(tr);
        });
    }
 
    // Actualizar KPIs
    const nPacientes   = usuarios.filter(u => u.tipo === 'Paciente').length;
    const nOdontologos = usuarios.filter(u => u.tipo === 'Odontólogo').length;
 
    document.getElementById('kpi-totales').textContent    = usuarios.length;
    document.getElementById('kpi-pacientes').textContent  = nPacientes;
    document.getElementById('kpi-odontologos').textContent = nOdontologos;
    document.getElementById('chartTotal').textContent     = usuarios.length;
    document.getElementById('legPacientes').textContent   = nPacientes;
    document.getElementById('legOdontologos').textContent = nOdontologos;
    document.getElementById('roleCountOdon').textContent  = nOdontologos;
    document.getElementById('roleCountPac').textContent   = nPacientes;
 
    // Actualizar donut
    const total = usuarios.length || 1;
    const pct   = Math.round((nPacientes / total) * 100);
    document.getElementById('donutChart').style.background =
        `conic-gradient(var(--green) ${pct}%, var(--purple) ${pct}% 100%)`;
}
 
// ================= EVENTOS =================
 
// Buscador
document.getElementById('searchInput').addEventListener('input', actualizarDashboard);
 
// Tabs
document.querySelectorAll('.tab-btn').forEach(tab => {
    tab.addEventListener('click', (e) => {
        document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
        e.target.classList.add('active');
        filtroActual = e.target.getAttribute('data-filter');
        actualizarDashboard();
    });
});
 
// Modal — abrir
document.getElementById('btnNuevoUsuario').addEventListener('click', () => {
    document.getElementById('modalUsuario').style.display = 'flex';
});
 
// Modal — cerrar
document.getElementById('closeModal').addEventListener('click', () => {
    document.getElementById('modalUsuario').style.display = 'none';
});
 
document.getElementById('modalUsuario').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) e.currentTarget.style.display = 'none';
});
 
// Crear usuario
document.getElementById('formUsuario').addEventListener('submit', (e) => {
    e.preventDefault();
 
    const tipo = document.getElementById('formTipo').value;
    const nuevoUser = {
        id:     Date.now(),
        nombre: document.getElementById('formNombre').value,
        email:  document.getElementById('formEmail').value,
        tipo,
        rol:    tipo,
        estado: "Activo",
        img:    `https://i.pravatar.cc/150?img=${Math.floor(Math.random() * 70)}`
    };
 
    usuarios.push(nuevoUser);
    actualizarDashboard();
    document.getElementById('formUsuario').reset();
    document.getElementById('modalUsuario').style.display = 'none';
});
 
// Menú contextual
function mostrarMenuAcciones(event, id) {
    event.stopPropagation();
    usuarioSeleccionadoId = id;
    const menu = document.getElementById('contextMenu');
    menu.style.display = 'block';
    menu.style.left    = `${event.pageX - 10}px`;
    menu.style.top     = `${event.pageY + 8}px`;
}
 
function eliminarUsuarioActual() {
    usuarios = usuarios.filter(u => u.id !== usuarioSeleccionadoId);
    actualizarDashboard();
    document.getElementById('contextMenu').style.display = 'none';
}
 
document.addEventListener('click', () => {
    document.getElementById('contextMenu').style.display = 'none';
});
 
// Carga inicial
actualizarDashboard();
 