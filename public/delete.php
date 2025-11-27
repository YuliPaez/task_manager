<?php

require_once("../db/config.php");

// Clase Task permite la conexión y operaciones con tareas
class Task {
    private $conn;
    // Constructor permite inicializar la conexión a la base de datos
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Obtener una tarea por su ID
    public function getById(int $id_tasks): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE id_tasks = ?");
        $stmt->bind_param("i", $id_tasks);
        $stmt->execute();
        $result = $stmt->get_result();
        $task = $result->fetch_assoc();
        $stmt->close();
        return $task ?: null;
    }
}

// Validación del ID de tarea
$id_tasks = isset($_GET['id']) ? intval($_GET['id']) : 0;
$taskObj = new Task($conn);
$task = $taskObj->getById($id_tasks);

// Si la tarea no existe, redirigir a index.php
if (!$task) {
    header("Location: ../index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Eliminar Tarea</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Modal de confirmación -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
      </div>
      <div class="modal-body">
        ¿Está seguro que desea eliminar la tarea:
        <strong><?= htmlspecialchars($task['title']) ?></strong>?
      </div>
      <div class="modal-footer">
        <a href="../src/controllers/delete_actions.php?id=<?= $id_tasks ?>" class="btn btn-danger">Eliminar</a>
        <a href="../index.php" class="btn btn-secondary">Cancelar</a>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mostrar modal automáticamente al cargar la página
window.addEventListener('load', () => {
    const modalEl = document.getElementById('deleteModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
});
</script>

</body>
</html>
