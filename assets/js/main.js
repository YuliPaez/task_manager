// Validación del formulario de create y edit de tareas
function validarFormulario() {
    var title = document.querySelector('input[name="title"]').value.trim();
    var user = document.querySelector('select[name="user_id"]').value;
    var priority = document.querySelector('select[name="priority"]').value;
    var deadline = document.querySelector('input[name="deadline"]').value;

    if (title === "") { alert("Ingrese el título"); return false; }
    if (user === "") { alert("Seleccione un responsable"); return false; }
    if (priority === "") { alert("Seleccione la prioridad"); return false; }
    if (deadline === "") { alert("Seleccione la fecha límite"); return false; }

    return true;
}
// Validación para la fecha límite no debe ser anterior a hoy
function validarFechaLimite() {
    const deadline = document.querySelector('input[name="deadline"]').value;
    const hoy = new Date().toISOString().split('T')[0];

    if (deadline < hoy) {
        alert("La fecha límite no puede ser anterior a hoy.");
        return false;
    }
    return true;
}
