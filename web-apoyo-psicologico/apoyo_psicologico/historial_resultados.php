<?php
session_start(); // ¡IMPORTANTE! Inicia la sesión para acceder a $_SESSION
include 'includes/header.php';
include 'database.php'; // Incluye la conexión a la BD

// --- VERIFICAR SI EL USUARIO ESTÁ LOGUEADO Y ES PACIENTE ---
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'paciente') {
    // Si no está logueado o no es un paciente, lo redirigimos a la página de ingreso.
    header("Location: ingresar.php?error=acceso_no_autorizado");
    exit();
}

$usuario_id = $_SESSION['usuario_id']; // Obtenemos el ID del paciente logueado
$mensaje_historial = ""; // Para mensajes futuros, si es necesario
?>

<style>
    /* Estilos para historial_resultados.php */
    .content-section {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        padding: 30px;
        margin-top: 30px;
        text-align: center;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .content-section h2 {
        color: #333;
        margin-bottom: 25px;
        font-size: 2em;
        border-bottom: 2px solid #6495ed; /* Línea azul debajo del título */
        padding-bottom: 10px;
        display: inline-block;
    }

    .resultado-item {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-left: 5px solid #6495ed; /* Borde azul de color para destacar */
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        text-align: left; /* Alinea el texto del resultado a la izquierda */
    }

    .resultado-item p {
        margin: 0 0 10px 0;
        color: #444;
        line-height: 1.6;
    }

    .resultado-item .fecha {
        font-size: 0.9em;
        color: #777;
        text-align: right; /* Alinea la fecha a la derecha */
    }

    .no-resultados {
        color: #666;
        font-style: italic;
        padding: 20px;
        background-color: #fdf3f3;
        border: 1px solid #f0b0b0;
        border-radius: 5px;
    }

    .btn-volver {
        display: inline-block;
        margin-top: 20px;
        background-color: #007bff; /* Azul estándar */
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .btn-volver:hover {
        background-color: #0056b3;
    }
</style>

<section class="content-section">
    <h2>Tu Historial de Resultados del Cuestionario</h2>

    <?php
    // Preparamos la consulta para obtener los resultados del cuestionario del usuario actual
    $stmt = $conn->prepare("SELECT resultado, fecha_cuestionario FROM resultados_cuestionario WHERE usuario_id = ? ORDER BY fecha_cuestionario DESC");
    $stmt->bind_param("i", $usuario_id); // 'i' indica que usuario_id es un entero
    $stmt->execute();
    $result = $stmt->get_result(); // Obtenemos el resultado de la consulta

    if ($result->num_rows > 0) {
        // Si hay resultados, los recorremos y mostramos
        while ($row = $result->fetch_assoc()) {
            echo "<div class='resultado-item'>";
            echo "<p>" . htmlspecialchars($row['resultado']) . "</p>"; // Muestra el texto del resultado
            echo "<p class='fecha'>Fecha: " . date("d/m/Y H:i", strtotime($row['fecha_cuestionario'])) . "</p>"; // Muestra la fecha formateada
            echo "</div>";
        }
    } else {
        // Si no hay resultados para este usuario
        echo "<p class='no-resultados'>Aún no has completado ningún cuestionario o no hay resultados guardados para tu cuenta.</p>";
    }

    $stmt->close(); // Cerramos el prepared statement
    $conn->close(); // Cerramos la conexión a la base de datos
    ?>

    <p><a href="dashboard_paciente.php" class="btn-volver">Volver al Panel de Paciente</a></p>
</section>

<?php include 'includes/footer.php'; ?>