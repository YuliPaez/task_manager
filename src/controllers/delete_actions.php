<?php
require_once("../../db/config.php");

// ================================
// Clase Task para operaciones CRUD
// ================================
class Task {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getById(int $id_tasks): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE id_tasks = ?");
        $stmt->bind_param("i", $id_tasks);
        $stmt->execute();
        $task = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $task ?: null;
    }

    public function delete(int $id_tasks): bool {
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id_tasks = ?");
        $stmt->bind_param("i", $id_tasks);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}

// ================================
// Inicialización
// ================================
$taskObj = new Task($conn);
$id_tasks = isset($_GET['id']) ? intval($_GET['id']) : 0;
$task = $taskObj->getById($id_tasks);

if (!$task) {
    $message = "La tarea no existe.";
    $message_type = "danger";
} else {
    if ($taskObj->delete($id_tasks)) {
        $message = "Tarea <strong>" . htmlspecialchars($task['title']) . "</strong> eliminada exitosamente.";
        $message_type = "success";
    } else {
        $message = "Error al eliminar la tarea <strong>" . htmlspecialchars($task['title']) . "</strong>.";
        $message_type = "danger";
    }
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

<!-- Modal de resultado-->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header <?= $message_type=='success'?'bg-success text-white':'bg-danger text-white' ?>">
        <h5 class="modal-title" id="deleteModalLabel"><?= $message_type=='success'?'Éxito':'Error' ?></h5>
      </div>
      <div class="modal-body"> 
        <?= $message ?>
      </div>
      <div class="modal-footer">
        <a href="../../index.php" class="btn btn-<?= $message_type=='success'?'success':'secondary' ?>">Return</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mostrar modal automáticamente al cargar la página
window.addEventListener('load', () => {
    const modalEl = document.getElementById('deleteModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Redirigir al cerrar el modal
    modalEl.addEventListener('hidden.bs.modal', () => {
        window.location.href='../../index.php';
    });
});
</script>

</body>
</html>
