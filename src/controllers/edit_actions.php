<?php
session_start();
require_once("../../db/config.php");

// Activar reporte de errores (solo para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ================================
// Manejo de errores y excepciones
// ================================
$message = "";
$message_type = "";

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    global $message, $message_type;
    $message = "Error PHP: $errstr en $errfile:$errline";
    $message_type = "danger";
    return true;
});

set_exception_handler(function ($e) {
    global $message, $message_type;
    $message = "Excepción: " . $e->getMessage();
    $message_type = "danger";
});

// ================================
// Clase Task
// ================================
class Task
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function isUserActive(int $user_id): bool
    {
        $stmt = $this->conn->prepare("SELECT active FROM users WHERE id_users = ?");
        if (!$stmt) throw new Exception($this->conn->error);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return isset($result['active']) && $result['active'] == 1;
    }

    public function getById(int $id_tasks): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE id_tasks = ?");
        if (!$stmt) throw new Exception($this->conn->error);
        $stmt->bind_param("i", $id_tasks);
        $stmt->execute();
        $task = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $task ?: null;
    }

    public function update(array $data): bool
    {
        $current_task = $this->getById($data['id_tasks']);
        if (!$current_task) throw new Exception("Tarea no encontrada.");

        $sql = "UPDATE tasks SET title=?, user_id=?, priority=?, deadline=?";
        $params = [$data['title'], $data['user_id'], $data['priority'], $data['deadline']];
        $types = "siss";

        // Si cambia el responsable, actualizar assigned_at
        if ($data['user_id'] != $current_task['user_id']) {
            $sql .= ", assigned_at=NOW()";
        }

        $sql .= " WHERE id_tasks=?";
        $types .= "i";
        $params[] = $data['id_tasks'];

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new Exception($this->conn->error);
        $stmt->bind_param(...array_merge([$types], $params));
        $success = $stmt->execute();
        if (!$success) throw new Exception($stmt->error);
        $stmt->close();
        return $success;
    }
}

// ================================
// Inicialización
// ================================
$taskObj = new Task($conn);

// Responsables activos
$users = $conn->query("SELECT id_users, name FROM users WHERE active = 1 ORDER BY name");

// ================================
// Procesar POST (actualización)
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id_tasks = intval($_POST['id_tasks']);
    $title = trim($_POST['title']);
    $user_id = intval($_POST['user_id']);
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];

    $valid_priorities = ['Baja', 'Media', 'Alta'];

    try {
        // Validaciones del lado del servidor
        if (empty($title) || empty($user_id) || empty($priority) || empty($deadline)) {
            throw new Exception("Todos los campos son obligatorios.");
        }
        if (!in_array($priority, $valid_priorities)) {
            throw new Exception("Prioridad inválida. Valor recibido: '$priority'");
        }
        if (!$taskObj->isUserActive($user_id)) {
            throw new Exception("El responsable seleccionado no está activo.");
        }
        if (strtotime($deadline) < strtotime(date('Y-m-d'))) {
            throw new Exception("La fecha límite no puede ser anterior a hoy.");
        }

        $taskObj->update([
            'id_tasks' => $id_tasks,
            'title' => $title,
            'user_id' => $user_id,
            'priority' => $priority,
            'deadline' => $deadline
        ]);

        $message = "Tarea actualizada exitosamente.";
        $message_type = "success";
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = "danger";
    }
}

// ================================
// Obtener tarea para mostrar en formulario
// ================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $id_tasks = intval($_GET['id']);
    $task = $taskObj->getById($id_tasks);
    if (!$task) {
        $message = "Tarea no encontrada.";
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <?php if (!empty($message)): ?>
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header <?= $message_type === 'success' ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                        <h5 class="modal-title"><?= $message_type === 'success' ? 'Éxito' : 'Error' ?></h5>
                        <button type="button" class="btn-close" id="closeModalBtn" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body"><?= $message ?></div>
                    <div class="modal-footer">
                        <a href="../../index.php" class="btn btn-<?= $message_type === 'success' ? 'success' : 'secondary' ?>">Return</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('load', () => {
            const modalEl = document.getElementById('resultModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                document.getElementById('closeModalBtn')?.addEventListener('click', () => {
                    window.location.href = '../../index.php';
                });
                modalEl.addEventListener('hidden.bs.modal', () => {
                    window.location.href = '../../index.php';
                });
            }
        });
    </script>

</body>

</html>
