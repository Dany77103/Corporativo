document.addEventListener('DOMContentLoaded', () => {
    const selectPais = document.getElementById('select_pais');
    const selectCiudad = document.getElementById('select_ciudad');

    if (!selectPais || !selectCiudad) return;

    // 1. Cargar lista global de países
    fetch('https://countriesnow.space/api/v0.1/countries/positions')
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                selectPais.innerHTML = '<option value="">-- Seleccionar País --</option>';
                data.data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.name;
                    opt.textContent = item.name;
                    selectPais.appendChild(opt);
                });
            }
        })
        .catch(err => console.error("Error cargando países:", err));

    // 2. Cargar ciudades según el país seleccionado
    selectPais.addEventListener('change', function () {
        const pais = this.value;
        selectCiudad.innerHTML = '<option value="">Cargando ciudades...</option>';

        if (!pais) {
            selectCiudad.innerHTML = '<option value="">-- Seleccionar Ciudad --</option>';
            return;
        }

        fetch('https://countriesnow.space/api/v0.1/countries/cities', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ country: pais })
        })
            .then(response => response.json())
            .then(data => {
                selectCiudad.innerHTML = '<option value="">-- Seleccionar Ciudad --</option>';
                if (!data.error && data.data) {
                    data.data.forEach(ciudad => {
                        const opt = document.createElement('option');
                        opt.value = ciudad;
                        opt.textContent = ciudad;
                        selectCiudad.appendChild(opt);
                    });
                }
            })
            .catch(err => {
                console.error("Error cargando ciudades:", err);
                selectCiudad.innerHTML = '<option value="">Sin datos disponibles</option>';
            });
    });
});