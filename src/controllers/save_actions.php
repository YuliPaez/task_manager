<?php
session_start();
require_once("../../db/config.php");

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
// Clase Task para CRUD
// ================================
class Task
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Verifica si el usuario está activo
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

    // Crea una nueva tarea
    public function create(array $data): bool
    {
        $stmt = $this->conn->prepare("
            INSERT INTO tasks (title, user_id, priority, deadline, created_at, assigned_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        if (!$stmt) throw new Exception($this->conn->error);
        $stmt->bind_param("siss", $data['title'], $data['user_id'], $data['priority'], $data['deadline']);
        $success = $stmt->execute();
        if (!$success) throw new Exception($stmt->error);
        $stmt->close();
        return $success;
    }
}

// ================================
// Procesar POST
// ================================
$taskObj = new Task($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $user_id = intval($_POST['user_id'] ?? 0);
    $priority = $_POST['priority'] ?? '';
    $deadline = $_POST['deadline'] ?? '';

    $valid_priorities = ['Baja', 'Media', 'Alta'];

    try { 
        // Validaciones del lado del servidor
        if (empty($title) || empty($user_id) || empty($priority) || empty($deadline)) {
            throw new Exception("Todos los campos son obligatorios.");
        }
        if (!in_array($priority, $valid_priorities)) {
            throw new Exception("Prioridad inválida.");
        }
        if (!$taskObj->isUserActive($user_id)) {
            throw new Exception("El usuario seleccionado no está activo.");
        }
        if (strtotime($deadline) < strtotime(date('Y-m-d'))) {
            throw new Exception("La fecha límite no puede ser anterior a hoy.");
        }

        // Crear tarea
        $taskObj->create([
            'title' => $title,
            'user_id' => $user_id,
            'priority' => $priority,
            'deadline' => $deadline
        ]);

        $message = "Tarea <strong>" . htmlspecialchars($title) . "</strong> creada exitosamente.";
        $message_type = "success";
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Guardar Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- Modal resultado -->
    <?php if (!empty($message)): ?>
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header <?= $message_type === 'success' ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                        <h5 class="modal-title" id="resultModalLabel"><?= $message_type === 'success' ? 'Éxito' : 'Error' ?></h5>
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
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (!empty($message)): ?>
                const modalEl = $('#resultModal');
                modalEl.modal('show');
                modalEl.on('hidden.bs.modal', function() {
                    window.location.href = '../../index.php';
                });
            <?php endif; ?>
        });
    </script>

</body>

</html>
