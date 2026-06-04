/* =========================
   VARIABLES
========================= */

const teeth = document.querySelectorAll(".tooth-select");

const selectedToothInput =
document.getElementById("selectedTooth");

const toothImage =
document.getElementById("toothImage");

const saveButton =
document.getElementById("saveTreatment");

const successMessage =
document.getElementById("successMessage");

let currentTooth = null;

/* =========================
   CARGAR DIENTES GUARDADOS
========================= */

teeth.forEach(tooth => {

    const toothNumber =
    tooth.textContent.trim();

    const savedData =
    localStorage.getItem("pieza_" + toothNumber);

    if(savedData){

        tooth.classList.add("saved-tooth");

    }

});

/* =========================
   SELECCIONAR DIENTE
========================= */

teeth.forEach(tooth => {

    tooth.addEventListener("click", () => {

        currentTooth = tooth;

        teeth.forEach(t => {
            t.classList.remove("active-tooth");
        });

        tooth.classList.add("active-tooth");

        const toothNumber =
        tooth.textContent.trim();

        /* TEXTO */

        selectedToothInput.innerHTML =
        "Pieza Dental " + toothNumber;

        /* CAMBIAR IMAGEN */

        toothImage.src =
        "https://cdn-icons-png.flaticon.com/512/2966/2966486.png";

        /* ABRIR MODAL */

        window.location.hash =
        "modal-registrar-plan";

    });

});

/* =========================
   GUARDAR
========================= */

saveButton.addEventListener("click", () => {

    if(!currentTooth){

        alert("Selecciona una pieza dental");

        return;

    }

    const toothNumber =
    currentTooth.textContent.trim();

    const patient =
    document.getElementById("patientInput").value;

    const date =
    document.getElementById("dateInput").value;

    const data = {

        paciente: patient,
        fecha: date,
        pieza: toothNumber

    };

    /* GUARDAR */

    localStorage.setItem(
        "pieza_" + toothNumber,
        JSON.stringify(data)
    );

    /* PONER VERDE */

    currentTooth.classList.remove("active-tooth");

    currentTooth.classList.add("saved-tooth");

    /* ALERTA */

    successMessage.classList.add("show-message");

    setTimeout(() => {

        successMessage.classList.remove("show-message");

    }, 3000);

    /* CERRAR MODAL */

    window.location.hash = "";

});

/* =========================
   RESETEAR
========================= */

const resetButton =
document.getElementById("resetTeeth");

resetButton.addEventListener("click", () => {

    teeth.forEach(tooth => {

        tooth.classList.remove(
            "active-tooth",
            "saved-tooth"
        );

        const toothNumber =
        tooth.textContent.trim();

        localStorage.removeItem(
            "pieza_" + toothNumber
        );

    });

    selectedToothInput.innerHTML =
    "Selecciona una pieza dental";

    currentTooth = null;

});