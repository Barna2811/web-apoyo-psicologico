<?php
// db_config.php - Configuración de conexión a la base de datos

$host = 'localhost'; // Normalmente 'localhost' para XAMPP
$db   = 'apoyo_psicologico'; // ¡¡¡CAMBIA ESTO por el nombre REAL de tu base de datos!!!
$user = 'root';      // Normalmente 'root' para XAMPP
$pass = '';          // Normalmente vacío para XAMPP si no has puesto contraseña
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Si hay un error al conectar, mostraremos un mensaje.
    // En un proyecto real (en un servidor), NUNCA muestres el error directamente al usuario por seguridad.
    echo "Error de conexión a la base de datos: " . $e->getMessage();
    exit(); // Detiene la ejecución del script
}
?>