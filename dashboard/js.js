// MODAL CITAS

const modalCitas = document.getElementById("modalCitas");
const openCitas = document.getElementById("openCitas");
const closeCitas = document.getElementById("closeCitas");

openCitas.addEventListener("click", () => {
    modalCitas.style.display = "flex";
});

closeCitas.addEventListener("click", () => {
    modalCitas.style.display = "none";
});

// MODAL PACIENTES

const modalPacientes = document.getElementById("modalPacientes");
const openPacientes = document.getElementById("openPacientes");
const closePacientes = document.getElementById("closePacientes");

openPacientes.addEventListener("click", () => {
    modalPacientes.style.display = "flex";
});

closePacientes.addEventListener("click", () => {
    modalPacientes.style.display = "none";
});

// MODAL INFORMACION PACIENTE

const modalInfo = document.getElementById("modalInfoPaciente");
const closeInfo = document.getElementById("closeInfo");

const btnAlejandra = document.getElementById("btnAlejandra");
const btnLuis = document.getElementById("btnLuis");

const patientImg = document.getElementById("patientImg");
const patientName = document.getElementById("patientName");
const patientEdad = document.getElementById("patientEdad");
const patientTelefono = document.getElementById("patientTelefono");
const patientTratamiento = document.getElementById("patientTratamiento");
const patientVisita = document.getElementById("patientVisita");

// ALEJANDRA

btnAlejandra.addEventListener("click", () => {

    patientImg.src = "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80";

    patientName.textContent = "Alejandra Torres";

    patientEdad.textContent = "27 años";

    patientTelefono.textContent = "+57 300 123 4567";

    patientTratamiento.textContent = "Ortodoncia";

    patientVisita.textContent = "15 Mayo 2026";

    modalInfo.style.display = "flex";

});

// LUIS

btnLuis.addEventListener("click", () => {

    patientImg.src = "https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=150&q=80";

    patientName.textContent = "Luis Eduardo Pérez";

    patientEdad.textContent = "35 años";

    patientTelefono.textContent = "+57 310 555 8899";

    patientTratamiento.textContent = "Evaluación dental";

    patientVisita.textContent = "15 Mayo 2026";

    modalInfo.style.display = "flex";

});
closeInfo.addEventListener("click", () => {
    modalInfo.style.display = "none";
});

// CERRAR MODALES AL DAR CLICK AFUERA

window.addEventListener("click", (e) => {

    if(e.target === modalCitas){
        modalCitas.style.display = "none";
    }

    if(e.target === modalPacientes){
        modalPacientes.style.display = "none";
    }

    if(e.target === modalInfo){
        modalInfo.style.display = "none";
    }

});

// INGRESOS MES / SEMANA

const selector = document.getElementById("incomeSelector");

const amount = document.querySelector(".income-main-amount");
const title = document.querySelector(".income-title");

selector.addEventListener("change", () => {

    if(selector.value === "mes"){

        amount.textContent = "$8,750.00";
        title.textContent = "Ingresos del mes";

    }else{

        amount.textContent = "$2,140.00";
        title.textContent = "Ingresos de la semana";

    }

});
openCitas.addEventListener("click", (e) => {
    e.preventDefault();
    modalCitas.style.display = "flex";
});