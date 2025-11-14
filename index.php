<?php include_once("includes/header.php"); ?>
<?php require_once("db/config.php"); ?>

<!-- Botón para crear una nueva tarea -->
<a href="public/create.php" class="btn btn-success mb-3">➕ New Task</a>

<!-- Tabla de tareas --> 
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        Task List
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead class="table-dark">
                <tr> 
                    <th>Title</th>
                    <th>Responsible</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Deadline</th>
                    <th>Elapsed Time</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>
<!-- PHP para obtener y mostrar las tareas -->
<?php
$sql = "SELECT 
            t.id_tasks,
            t.title,
            t.priority,
            t.deadline,
            t.created_at,
            t.status,
            u.name AS responsible,
            DATEDIFF(NOW(), t.created_at) AS diff
        FROM tasks t
        JOIN users u ON t.user_id = u.id_users
        ORDER BY t.created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($task = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$task['title']}</td>
                <td>{$task['responsible']}</td>
                <td>{$task['status']}</td>
                <td>{$task['priority']}</td>
                <td>{$task['deadline']}</td>
                <td>{$task['diff']} días</td>
                <td>
                    <a href='public/edit.php?id={$task['id_tasks']}' class='btn btn-warning btn-sm'>✏ Edit</a>
                    <a href='public/delete.php?id={$task['id_tasks']}' class='btn btn-danger btn-sm'>🗑 Delete</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center text-muted'>No tasks registered</td></tr>";
}
?>

            </tbody>
        </table>
    </div>
</div>

<?php include_once("includes/footer.php"); ?>
