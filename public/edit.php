<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../includes/header.php");
require_once("../db/config.php");

$id_tasks = intval($_GET["id"]); // Aseguramos que sea un número

// Obtener info de la tarea
$task = $conn->query("SELECT * FROM tasks WHERE id_tasks = $id_tasks")->fetch_assoc();

// Responsables activos
$users = $conn->query("SELECT id_users, name FROM users WHERE active = 1 ORDER BY name");
?>
<!-- Vista de edición de tarea -->
<h2 class="mb-4 text-warning">Edit Task</h2>

<form action="../src/controllers/edit_actions.php" method="POST" class="card p-4 shadow-sm" onsubmit="return validarFormulario()">

    <!-- Mostrar ID (solo lectura) -->
    <div class="mb-3">
        <label class="form-label">Task ID</label>
        <input type="text" class="form-control" value="<?= $task['id_tasks'] ?>" readonly>
    </div>

    <!-- Enviar ID real oculto para POST -->
    <input type="hidden" name="id_tasks" value="<?= $task['id_tasks'] ?>">

    <div class="mb-3">
        <label class="form-label">Title *</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Responsible *</label>
        <select name="user_id" class="form-select">
            <option value="">Select...</option>
            <?php while ($u = $users->fetch_assoc()) : ?>
                <option value="<?= $u['id_users'] ?>" <?= $u['id_users'] == $task['user_id'] ? "selected" : "" ?>>
                    <?= $u['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Priority *</label>
        <select name="priority" class="form-select">
            <option value="Baja" <?= $task['priority'] == "Baja" ? "selected" : "" ?>>Baja</option>
            <option value="Media" <?= $task['priority'] == "Media" ? "selected" : "" ?>>Media</option>
            <option value="Alta" <?= $task['priority'] == "Alta" ? "selected" : "" ?>>Alta</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Deadline *</label>
        <input type="date"
            name="deadline"
            class="form-control"
            value="<?= $task['deadline'] ?>"
            min="<?= date('Y-m-d'); ?>">
    </div>

    <div class="d-flex justify-content-center gap-3 mt-4">
        <button type="submit" name="update" class="btn btn-primary px-4">Update</button>
        <a href="../index.php" class="btn btn-outline-secondary px-4">Back</a>
    </div>

</form>

<?php require_once("../includes/footer.php"); ?>
