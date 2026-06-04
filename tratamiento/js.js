document.addEventListener("DOMContentLoaded", () => {

    const searchInput = document.getElementById("searchTreatment");
    const tableBody = document.querySelector(".treatment-table tbody");

    const patientInput = document.getElementById("patientInput");
    const treatmentType = document.getElementById("treatmentType");
    const costInput = document.getElementById("costInput");
    const timeInput = document.getElementById("timeInput");

    const modal = document.getElementById("modal-nuevo-procedimiento");

    const openModalBtn = document.getElementById("openModalBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const closeModalX = document.querySelector(".close-modal-x");

    const saveBtn = document.getElementById("saveTreatmentBtn");

    let treatments = [
        { nombre: "Limpieza Dental", categoria: "Preventivo", tiempo: "15m", costo: 15 },
        { nombre: "Ajuste de Ortodoncia", categoria: "Restaurador", tiempo: "17m", costo: 10 },
        { nombre: "Obturación Resina", categoria: "Ortodoncia", tiempo: "30m", costo: 10 },
        { nombre: "Endodoncia", categoria: "Preventivo", tiempo: "15m", costo: 30 }
    ];

    function renderTable(data){

        tableBody.innerHTML = "";

        data.forEach(item => {

            const row = document.createElement("tr");

            row.innerHTML = `
                <td>
                    <i class="fa-solid fa-circle-plus icon-row-add"></i>
                    ${item.nombre}
                </td>

                <td class="cat-tag">${item.categoria}</td>
                <td>${item.tiempo}</td>
                <td class="price-lbl">$${item.costo}</td>

                <td>
                    <label class="switch-toggle">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </td>

                <td>
                    <i class="fa-solid fa-ellipsis-vertical action-dots"></i>
                </td>
            `;

            tableBody.appendChild(row);
        });
    }

    renderTable(treatments);

    searchInput.addEventListener("input", e => {

        const value = e.target.value.toLowerCase();

        const filtered = treatments.filter(t =>
            t.nombre.toLowerCase().includes(value) ||
            t.categoria.toLowerCase().includes(value)
        );

        renderTable(filtered);
    });

    openModalBtn.addEventListener("click", () => {
        modal.classList.add("active");
    });

    closeModalBtn.addEventListener("click", () => {
        modal.classList.remove("active");
    });

    closeModalX.addEventListener("click", e => {
        e.preventDefault();
        modal.classList.remove("active");
    });

    saveBtn.addEventListener("click", e => {

        e.preventDefault();

        if(
            patientInput.value.trim() === "" ||
            treatmentType.value === ""
        ){
            alert("Complete los campos requeridos");
            return;
        }

        treatments.push({
            nombre: patientInput.value,
            categoria: treatmentType.value,
            tiempo: timeInput.value || "No definido",
            costo: costInput.value || 0
        });

        renderTable(treatments);

        patientInput.value = "";
        treatmentType.value = "";
        costInput.value = "";
        timeInput.value = "";

        modal.classList.remove("active");

        alert("Tratamiento registrado correctamente");
    });

});