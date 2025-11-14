<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../includes/header.php");
require_once("../db/config.php");

// Obtener responsables activos
$users = $conn->query("SELECT id_users, name FROM users WHERE active = 1 ORDER BY name");
?>

<h2 class="mb-4 text-success">Create New Task</h2>

<form action="../src/controllers/save_actions.php" method="POST" class="card p-4 shadow-sm" onsubmit="return validarFormulario()">

    <div class="mb-3">
        <label class="form-label">Title *</label>
        <input type="text" name="title" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Responsible *</label>
        <select name="user_id" class="form-select">
            <option value="">Select...</option>
            <?php while ($u = $users->fetch_assoc()) : ?>
                <option value="<?= $u['id_users'] ?>"><?= $u['name'] ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Priority *</label>
        <select name="priority" class="form-select">
            <option value="">Seleccione...</option>
            <option value="Baja">Baja</option>
            <option value="Media">Media</option>
            <option value="Alta">Alta</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Deadline *</label>
        <input type="date" name="deadline" class="form-control" min="<?= date('Y-m-d'); ?>">
    </div>

    <div class="d-flex justify-content-center gap-3 mt-4">
        <button type="submit" name="save" class="btn btn-primary px-4">
            <i class="bi bi-save"></i> Save
        </button>

        <a href="../index.php" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left-short"></i> Back
        </a>
    </div>

</form>

<?php require_once("../includes/footer.php"); ?>
