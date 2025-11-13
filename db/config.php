<?php
//Parametros de conexión a la base de datos maria db
$host = "127.0.0.1"; 
$port = 3307;
$user = "root";
$pass = "12345";
$dbname = "task_manager";

$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
