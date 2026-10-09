function abrirModalEditar(id) {
    if (!PUEDO_MODIFICAR) {
        alert("Atención: Cuenta con permisos de solo lectura.");
        return;
    }

    const boton = document.querySelector('.btn-editar-usuario[data-id="' + Number(id) + '"]');
    if (!boton) return;

    const datos = boton.dataset;
    document.getElementById("userId").value = datos.id;
    document.getElementById("userUsername").value = datos.usuario;
    document.getElementById("userName").value = datos.nombre;

    const password = document.getElementById("userPass");
    password.value = "";
    password.required = false;
    password.placeholder = "Dejar en blanco para no cambiarla";
    document.getElementById("passHelp").textContent = "Si la dejas vacía se conserva la actual.";

    const rol = document.getElementById("userRol");
    rol.value = datos.rolId;
    const esMio = datos.esMio === "1";
    rol.disabled = esMio;
    document.getElementById("rolHelp").classList.toggle("d-none", !esMio);

    document.getElementById("formTitle").innerHTML =
        '<i class="bi bi-pencil-square me-1"></i> Editar Usuario #' + datos.id;
    document.getElementById("btnCancelar").classList.remove("d-none");
    window.scrollTo({ top: 0, behavior: "smooth" });
}

function eliminarUsuario(id) {
    if (!PUEDO_MODIFICAR) {
        alert("Atención: Cuenta con permisos de solo lectura.");
        return false;
    }

    const formulario = document.querySelector('form[data-eliminar-usuario="' + Number(id) + '"]');
    if (!formulario) return false;

    return window.confirm("¿Seguro que deseas eliminar a este usuario?");
}

function limpiarFormulario() {
    const formulario = document.getElementById("formUsuario");
    formulario.reset();
    document.getElementById("userId").value = "0";

    const password = document.getElementById("userPass");
    password.required = true;
    password.placeholder = "Mínimo 10 caracteres";
    document.getElementById("passHelp").textContent = "Obligatoria para usuarios nuevos.";

    const rol = document.getElementById("userRol");
    rol.disabled = false;
    if (ROL_POR_DEFECTO !== "0") rol.value = ROL_POR_DEFECTO;
    document.getElementById("rolHelp").classList.add("d-none");

    document.getElementById("formTitle").innerHTML =
        '<i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario';
    document.getElementById("btnCancelar").classList.add("d-none");
}