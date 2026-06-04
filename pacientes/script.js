// =========================
// DATOS
// =========================

let pacientes = [

    {
        nombre: "Juan Pérez",
        doc: "10001234",
        correo: "juan@gmail.com",
        telefono: "300111222",
        estado: "Activo"
    },

    {
        nombre: "Ana Torres",
        doc: "998877",
        correo: "ana@gmail.com",
        telefono: "311444555",
        estado: "Inactivo"
    },

    {
        nombre: "Carlos Ruiz",
        doc: "456789",
        correo: "carlos@gmail.com",
        telefono: "320111333",
        estado: "Activo"
    }

]

// =========================
// ELEMENTOS
// =========================

const tabla =
document.getElementById("tabla")

const buscar =
document.getElementById("buscar")

const filtro =
document.getElementById("filtroEstado")

const nuevo =
document.getElementById("nuevo")

// =========================
// PINTAR TABLA
// =========================

function pintar(lista){

    tabla.innerHTML = ""

    lista.forEach((p, i) => {

        tabla.innerHTML += `

        <tr>

            <td>

                <div class="patient-info">

                    <img
                        src="https://i.pravatar.cc/150?u=${p.doc}"
                        alt="Paciente"
                    >

                    <div>

                        <strong>${p.nombre}</strong>

                        <br>

                        <span>ID: ${p.doc}</span>

                    </div>

                </div>

            </td>

            <td>${p.doc}</td>

            <td>${p.correo}</td>

            <td>${p.telefono}</td>

            <td>

                <span class="status ${
                    p.estado === "Activo"
                    ? "active"
                    : "inactive"
                }">

                    ${p.estado}

                </span>

            </td>

            <td>

                <button
                    class="btn-ver"
                    onclick="ver(${i})"
                >

                    <i class="fa-solid fa-eye"></i>

                    Ver

                </button>

            </td>

        </tr>

        `

    })

    actualizar()

}

// =========================
// KPIs
// =========================

function actualizar(){

    let total =
    pacientes.length

    let activos =
    pacientes.filter(
        x => x.estado === "Activo"
    ).length

    let inactivos =
    pacientes.filter(
        x => x.estado === "Inactivo"
    ).length

    document.getElementById(
        "total"
    ).textContent = total

    document.getElementById(
        "activos"
    ).textContent = activos

    document.getElementById(
        "inactivos"
    ).textContent = inactivos

}

// =========================
// BUSCAR
// =========================

buscar.addEventListener("keyup", () => {

    let texto =
    buscar.value.toLowerCase()

    let resultado =
    pacientes.filter(x =>

        x.nombre
        .toLowerCase()
        .includes(texto)

        ||

        x.doc
        .includes(texto)

    )

    pintar(resultado)

})

// =========================
// FILTRO
// =========================

filtro.addEventListener("change", () => {

    if(
        filtro.value === "Todos"
    ){

        pintar(pacientes)

        return

    }

    let resultado =
    pacientes.filter(
        x => x.estado === filtro.value
    )

    pintar(resultado)

})

// =========================
// NUEVO PACIENTE
// =========================

nuevo.addEventListener("click", () => {

    let modal =
    document.createElement("div")

    modal.classList.add(
        "modal-overlay"
    )

    modal.innerHTML = `

    <div class="modal-box">

        <!-- HEADER -->

        <div class="modal-header">

            <div class="modal-user">

                <div class="modal-icon-create">

                    <i class="fa-solid fa-user-plus"></i>

                </div>

                <div>

                    <h2>Nuevo Paciente</h2>

                    <span>Registrar información</span>

                </div>

            </div>

            <button
                id="cerrarNuevoX"
                class="btn-close-x"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>

        <!-- BODY -->

        <div class="modal-body">

            <div class="form-grid">

                <!-- NOMBRE -->

                <div class="form-group">

                    <label>Nombre completo</label>

                    <input
                        type="text"
                        id="nuevoNombre"
                        class="modal-input"
                        placeholder="Ingrese nombre"
                    >

                </div>

                <!-- DOCUMENTO -->

                <div class="form-group">

                    <label>Documento</label>

                    <input
                        type="text"
                        id="nuevoDoc"
                        class="modal-input"
                        placeholder="Ingrese documento"
                    >

                </div>

                <!-- CORREO -->

                <div class="form-group">

                    <label>Correo</label>

                    <input
                        type="email"
                        id="nuevoCorreo"
                        class="modal-input"
                        placeholder="Ingrese correo"
                    >

                </div>

                <!-- TELEFONO -->

                <div class="form-group">

                    <label>Teléfono</label>

                    <input
                        type="text"
                        id="nuevoTelefono"
                        class="modal-input"
                        placeholder="Ingrese teléfono"
                    >

                </div>

                <!-- ESTADO -->

                <div class="form-group full">

                    <label>Estado</label>

                    <select
                        id="nuevoEstado"
                        class="modal-select"
                    >

                        <option value="Activo">
                            Activo
                        </option>

                        <option value="Inactivo">
                            Inactivo
                        </option>

                    </select>

                </div>

            </div>

        </div>

        <!-- FOOTER -->

        <div class="modal-footer">

            <button
                id="guardarNuevo"
                class="btn-guardar"
            >

                <i class="fa-solid fa-floppy-disk"></i>

                Guardar Paciente

            </button>

            <button
                id="cerrarNuevo"
                class="btn-cerrar"
            >

                Cancelar

            </button>

        </div>

    </div>

    `

    document.body.appendChild(modal)

    // =========================
    // GUARDAR PACIENTE
    // =========================

    document.getElementById(
        "guardarNuevo"
    ).onclick = () => {

        let nombre =
        document.getElementById(
            "nuevoNombre"
        ).value

        let doc =
        document.getElementById(
            "nuevoDoc"
        ).value

        let correo =
        document.getElementById(
            "nuevoCorreo"
        ).value

        let telefono =
        document.getElementById(
            "nuevoTelefono"
        ).value

        let estado =
        document.getElementById(
            "nuevoEstado"
        ).value

        // VALIDACION

        if(

            nombre === "" ||
            doc === "" ||
            correo === "" ||
            telefono === ""

        ){

            alert(
                "Completa todos los campos"
            )

            return

        }

        // NUEVO PACIENTE

        pacientes.push({

            nombre,
            doc,
            correo,
            telefono,
            estado

        })

        pintar(pacientes)

        // ALERTA

        mostrarAlerta(
            "Paciente agregado correctamente"
        )

        document.body.removeChild(modal)

    }

    // =========================
    // CERRAR
    // =========================

    document.getElementById(
        "cerrarNuevo"
    ).onclick = () => {

        document.body.removeChild(modal)

    }

    document.getElementById(
        "cerrarNuevoX"
    ).onclick = () => {

        document.body.removeChild(modal)

    }

})

// =========================
// VER PACIENTE
// =========================

function ver(i){

    let p =
    pacientes[i]

    let modal =
    document.createElement("div")

    modal.classList.add(
        "modal-overlay"
    )

    modal.innerHTML = `

    <div class="modal-box">

        <!-- HEADER -->

        <div class="modal-header">

            <div class="modal-user">

                <img
                    src="https://i.pravatar.cc/150?u=${p.doc}"
                    alt="Paciente"
                    class="modal-avatar"
                >

                <div>

                    <h2>${p.nombre}</h2>

                    <span>ID: ${p.doc}</span>

                </div>

            </div>

            <button
                id="cerrarX"
                class="btn-close-x"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>

        <!-- BODY -->

        <div class="modal-body">

            <div class="info-grid">

                <div class="info-card">

                    <i class="fa-solid fa-envelope"></i>

                    <div>

                        <small>Correo</small>

                        <p>${p.correo}</p>

                    </div>

                </div>

                <div class="info-card">

                    <i class="fa-solid fa-phone"></i>

                    <div>

                        <small>Teléfono</small>

                        <p>${p.telefono}</p>

                    </div>

                </div>

            </div>

            <!-- FORM -->

            <div class="form-section">

                <label>Estado del Paciente</label>

                <select
                    id="estadoSelect"
                    class="modal-select"
                >

                    <option value="Activo">
                        Activo
                    </option>

                    <option value="Inactivo">
                        Inactivo
                    </option>

                </select>

            </div>

        </div>

        <!-- FOOTER -->

        <div class="modal-footer">

            <button
                id="guardar"
                class="btn-guardar"
            >

                <i class="fa-solid fa-floppy-disk"></i>

                Guardar

            </button>

            <button
                id="cerrar"
                class="btn-cerrar"
            >

                Cancelar

            </button>

        </div>

    </div>

    `

    document.body.appendChild(modal)

    // ESTADO ACTUAL

    document.getElementById(
        "estadoSelect"
    ).value = p.estado

    // GUARDAR

    document.getElementById(
        "guardar"
    ).onclick = () => {

        p.estado =
        document.getElementById(
            "estadoSelect"
        ).value

        pintar(pacientes)

        mostrarAlerta(
            "Paciente actualizado correctamente"
        )

        document.body.removeChild(modal)

    }

    // CERRAR

    document.getElementById(
        "cerrar"
    ).onclick = () => {

        document.body.removeChild(modal)

    }

    document.getElementById(
        "cerrarX"
    ).onclick = () => {

        document.body.removeChild(modal)

    }

}

// =========================
// ALERTA
// =========================

function mostrarAlerta(texto){

    let alerta =
    document.createElement("div")

    alerta.classList.add(
        "alerta-guardado"
    )

    alerta.innerHTML = `

    <i class="fa-solid fa-circle-check"></i>

    ${texto}

    `

    document.body.appendChild(alerta)

    setTimeout(() => {

        alerta.classList.add(
            "show-alert"
        )

    }, 100)

    setTimeout(() => {

        alerta.remove()

    }, 3000)

}

// =========================
// INICIO
// =========================

pintar(pacientes)