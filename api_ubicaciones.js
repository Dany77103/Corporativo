document.addEventListener("DOMContentLoaded", () => {
    const selectPais = document.getElementById("select-pais");
    const selectCiudad = document.getElementById("select-ciudad");

    if (!selectPais || !selectCiudad) return;

    // 1. Cargar Países desde la API de REST Countries
    fetch("https://restcountries.com/v3.1/all?fields=name,cca2")
        .then(res => res.json())
        .then(data => {
            // Ordenar países alfabéticamente
            data.sort((a, b) => a.name.common.localeCompare(b.name.common));

            data.forEach(pais => {
                const opt = document.createElement("option");
                opt.value = pais.name.common;
                opt.dataset.code = pais.cca2;
                opt.textContent = pais.name.common;
                selectPais.appendChild(opt);
            });
        })
        .catch(err => console.error("Error al cargar países:", err));

    // 2. Cargar Ciudades al seleccionar un País
    selectPais.addEventListener("change", (e) => {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const paisNombre = selectedOption.value;

        // Limpiar select de ciudad si no hay país seleccionado
        if (!paisNombre) {
            selectCiudad.innerHTML = '<option value="">Selecciona una Ciudad</option>';
            return;
        }

        selectCiudad.innerHTML = '<option value="">Cargando ciudades...</option>';

        fetch("https://countriesnow.space/api/v0.1/countries/cities", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ country: paisNombre })
        })
        .then(res => res.json())
        .then(resData => {
            selectCiudad.innerHTML = '<option value="">Selecciona una Ciudad</option>';

            if (!resData.error && resData.data && resData.data.length > 0) {
                // Ordenar ciudades alfabéticamente
                resData.data.sort().forEach(ciudad => {
                    const opt = document.createElement("option");
                    opt.value = ciudad;
                    opt.textContent = ciudad;
                    selectCiudad.appendChild(opt);
                });
            } else {
                selectCiudad.innerHTML = '<option value="">No se encontraron ciudades</option>';
            }
        })
        .catch(err => {
            console.error("Error al cargar ciudades:", err);
            selectCiudad.innerHTML = '<option value="">Error al cargar ciudades</option>';
        });
    });
});