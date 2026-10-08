// País (REST Countries) y ciudad (CountriesNow) traídos de internet: no se guarda ninguna lista en la base de datos.
// - El país se muestra y se guarda en español (ej. "México"); la API de ciudades recibe el nombre en inglés.
// - La ciudad es un campo con sugerencias: si la API falla, se puede escribir a mano y el formulario sigue funcionando.
(function () {
    const PARES = [
        { pais: 'select-pais', ciudad: 'select-ciudad' },  // formulario de alta
        { pais: 'edit_pais',   ciudad: 'edit_ciudad' }     // modal de edición
    ];
    const CACHE_KEY = 'gsb_paises_v2';
    const CACHE_MS  = 7 * 24 * 60 * 60 * 1000;

    const normalizar = (t) => (t || '').toString()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase();

    // ---------- Países ----------
    let paisesPromise = null;

    function cargarPaises() {
        if (paisesPromise) return paisesPromise;

        paisesPromise = new Promise((resolve) => {
            try {
                const c = JSON.parse(localStorage.getItem(CACHE_KEY) || 'null');
                if (c && Array.isArray(c.data) && c.data.length && (Date.now() - c.t) < CACHE_MS) {
                    resolve(c.data);
                    return;
                }
            } catch (e) { /* sin caché */ }

            fetch('https://restcountries.com/v3.1/all?fields=name,cca2,translations')
                .then((r) => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then((data) => {
                    const lista = data.map((p) => ({
                        es: (p.translations && p.translations.spa && p.translations.spa.common) || p.name.common,
                        en: p.name.common
                    })).sort((a, b) => a.es.localeCompare(b.es, 'es'));

                    try { localStorage.setItem(CACHE_KEY, JSON.stringify({ t: Date.now(), data: lista })); } catch (e) {}
                    resolve(lista);
                })
                .catch((err) => {
                    console.error('Error al cargar países:', err);
                    resolve([]);
                });
        });
        return paisesPromise;
    }

    function llenarSelectPaises(select, lista) {
        const placeholder = select.querySelector('option[value=""]');
        select.innerHTML = '';
        select.appendChild(placeholder || new Option('Selecciona un País', ''));
        lista.forEach((p) => {
            const opt = new Option(p.es, p.es);
            opt.dataset.en = p.en;
            select.appendChild(opt);
        });
    }

    // Si la API de países falla, el campo pasa a texto libre para no bloquear el formulario
    function convertirEnTexto(select) {
        const input = document.createElement('input');
        input.type = 'text';
        input.id = select.id;
        input.name = select.name;
        input.className = 'form-control';
        input.required = select.required;
        input.maxLength = 100;
        input.placeholder = 'Escribe el país (no se pudo cargar la lista)';
        select.replaceWith(input);
        return input;
    }

    // ---------- Ciudades ----------
    const cacheCiudades = new Map();

    function cargarCiudades(nombreEn) {
        if (cacheCiudades.has(nombreEn)) return cacheCiudades.get(nombreEn);

        const p = fetch('https://countriesnow.space/api/v0.1/countries/cities', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ country: nombreEn })
        })
            .then((r) => r.json())
            .then((d) => (!d.error && Array.isArray(d.data))
                ? d.data.slice().sort((a, b) => a.localeCompare(b, 'es'))
                : [])
            .catch(() => [])
            .then((lista) => {
                if (!lista.length) cacheCiudades.delete(nombreEn);   // no se cachean los fallos
                return lista;
            });

        cacheCiudades.set(nombreEn, p);
        return p;
    }

    function llenarCiudades(ciudadInput, ciudades, mensaje) {
        const dl = document.getElementById(ciudadInput.getAttribute('list'));
        if (dl) {
            dl.innerHTML = '';
            ciudades.forEach((c) => {
                const o = document.createElement('option');
                o.value = c;
                dl.appendChild(o);
            });
        }
        ciudadInput.placeholder = mensaje || (ciudades.length ? 'Escribe o elige una ciudad' : 'Escribe la ciudad');
    }

    // Ajusta mayúsculas/acentos al nombre oficial de la lista ("ciudad de mexico" -> "Ciudad de México")
    function canonizarCiudad(input) {
        const dl = document.getElementById(input.getAttribute('list'));
        if (!dl || !input.value) return;
        const buscado = normalizar(input.value);
        const hit = Array.from(dl.options).find((o) => normalizar(o.value) === buscado);
        if (hit) input.value = hit.value;
    }

    async function alCambiarPais(par, limpiarCiudad) {
        const paisEl   = document.getElementById(par.pais);
        const ciudadEl = document.getElementById(par.ciudad);
        if (!paisEl || !ciudadEl) return;

        if (limpiarCiudad) ciudadEl.value = '';

        const opt = (paisEl.tagName === 'SELECT') ? paisEl.options[paisEl.selectedIndex] : null;
        const en  = (opt && opt.dataset) ? opt.dataset.en : '';

        if (!en) {
            llenarCiudades(ciudadEl, [], paisEl.value ? 'Escribe la ciudad' : 'Primero elige un país');
            return;
        }

        llenarCiudades(ciudadEl, [], 'Cargando ciudades...');
        const ciudades = await cargarCiudades(en);

        // Si mientras cargaba el usuario cambió de país, se descarta este resultado
        const actual = paisEl.options[paisEl.selectedIndex];
        if (!actual || actual.dataset.en !== en) return;

        llenarCiudades(ciudadEl, ciudades);
    }

    // ---------- Inicio ----------
    document.addEventListener('DOMContentLoaded', async () => {
        const presentes = PARES.filter((p) => document.getElementById(p.pais) && document.getElementById(p.ciudad));
        if (!presentes.length) return;

        const lista = await cargarPaises();

        presentes.forEach((par) => {
            const paisEl   = document.getElementById(par.pais);
            const ciudadEl = document.getElementById(par.ciudad);

            if (!lista.length) {
                convertirEnTexto(paisEl);
                llenarCiudades(ciudadEl, [], 'Escribe la ciudad');
                return;
            }

            llenarSelectPaises(paisEl, lista);
            paisEl.addEventListener('change', () => alCambiarPais(par, true));
            ciudadEl.addEventListener('change', () => canonizarCiudad(ciudadEl));
        });
    });

    // ---------- Precarga para el modal de edición ----------
    window.precargarUbicacion = async function (idPais, idCiudad, paisGuardado, ciudadGuardada) {
        await cargarPaises();   // espera a que el selector ya tenga sus opciones

        const paisEl   = document.getElementById(idPais);
        const ciudadEl = document.getElementById(idCiudad);
        if (!paisEl || !ciudadEl) return;

        // País escrito a mano (la lista no cargó)
        if (paisEl.tagName !== 'SELECT') {
            paisEl.value = paisGuardado || '';
            ciudadEl.value = ciudadGuardada || '';
            return;
        }

        paisEl.querySelectorAll('option[data-legacy="1"]').forEach((o) => o.remove());

        const buscado = normalizar(paisGuardado);
        const coincide = buscado
            ? Array.from(paisEl.options).find((o) => o.value && (normalizar(o.value) === buscado || normalizar(o.dataset.en) === buscado))
            : null;

        if (coincide) {
            paisEl.value = coincide.value;                 // además corrige "Mexico" -> "México"
        } else if (paisGuardado) {
            // Valor antiguo que no está en la lista: se conserva para no perder el dato al guardar
            const extra = new Option(paisGuardado + ' (valor actual)', paisGuardado);
            extra.dataset.legacy = '1';
            paisEl.insertBefore(extra, paisEl.options[1] || null);
            paisEl.value = paisGuardado;
        } else {
            paisEl.value = '';
        }

        await alCambiarPais({ pais: idPais, ciudad: idCiudad }, false);
        ciudadEl.value = ciudadGuardada || '';
    };
})();