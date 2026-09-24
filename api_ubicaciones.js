document.addEventListener("DOMContentLoaded", () => {
    const selectPais = document.getElementById("select-pais");
    const selectCiudad = document.getElementById("select-ciudad");

    // 1. Cargar Países desde API gratuita
    fetch("https://restcountries.com/v3.1/all?fields=name,cca2")
        .then(res => res.json())
        .then(data => {
            data.sort((a, b) => a.name.common.localeCompare(b.name.common));
            data.forEach(pais => {
                const opt = document.createElement("option");
                opt.value = pais.name.common; // Se guarda el nombre del país directamente
                opt.dataset.code = pais.cca2;
                opt.textContent = pais.name.common;
                selectPais.appendChild(opt);
            });
        });

    // 2. Cargar Ciudades según el país elegido
    selectPais.addEventListener("change", (e) => {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const countryCode = selectedOption.dataset.code;

        selectCiudad.innerHTML = '<option value="">Cargando ciudades...</option>';

        if (!countryCode) return;

        fetch(`https://countriesnow.space/api/v0.1/countries/cities`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ country: selectedOption.value })
        })
        .then(res => res.json())
        .then(data => {
            selectCiudad.innerHTML = '<option value="">Selecciona una Ciudad</option>';
            if (data.data) {
                data.data.sort().forEach(ciudad => {
                    const opt = document.createElement("option");
                    opt.value = ciudad;
                    opt.textContent = ciudad;
                    selectCiudad.appendChild(opt);
                });
            }
        });
    });
});