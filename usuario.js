document.addEventListener("DOMContentLoaded", () => {
    const formUsuario = document.getElementById("formUsuario");

    if (formUsuario) {
        formUsuario.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch("guardar_usuario.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert(data.mensaje);
                    location.reload(); // Recarga para ver los cambios
                } else {
                    alert("Error: " + data.mensaje);
                }
            })
            .catch(error => {
                console.error("Error al procesar la solicitud:", error);
                alert("Ocurrió un error al guardar el usuario.");
            });
        });
    }
});