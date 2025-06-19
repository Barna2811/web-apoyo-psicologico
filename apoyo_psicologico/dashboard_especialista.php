<?php
session_start();
include 'includes/header.php';
// Asegúrate de incluir la conexión a la base de datos aquí
include 'includes/db_config.php'; 

// Verificar si el usuario está logueado y es especialista
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php"); // Redirigir si no está logueado o no es especialista
    exit();
}

$especialista_id = $_SESSION['usuario_id'];

// --- Lógica para las métricas de reportes ---
$num_reportes_generados = 0;
$tasa_lectura_reportes = "N/A"; // Valor predeterminado si no hay datos o la lógica es más compleja
$tiempo_promedio_analisis = "N/A"; // Valor predeterminado

try {
    // Número de reportes de caso distintos generados/vistos por este especialista
    $stmt_num_reportes = $pdo->prepare("
        SELECT COUNT(DISTINCT paciente_id) FROM reportes_log WHERE especialista_id = ?
    ");
    $stmt_num_reportes->execute([$especialista_id]);
    $num_reportes_generados = $stmt_num_reportes->fetchColumn();

    // Tasa de lectura de reportes:
    // Con nuestra implementación actual en reporte_caso_paciente.php,
    // cada vez que se genera un reporte, se marca como 'leído' (fecha_lectura = NOW()).
    // Por lo tanto, la "tasa de lectura" de los reportes generados *por este especialista*
    // para los que hay registro en `reportes_log` será del 100% de los reportes "vistos".
    // Si tuvieras un sistema donde los reportes se "generan" de otra forma (ej. por un cron job)
    // y solo se "leen" cuando el especialista los abre, la lógica sería más compleja.
    // Por ahora, lo mostraremos de forma sencilla.
    if ($num_reportes_generados > 0) {
        $tasa_lectura_reportes = "100%"; // Asumiendo que todo reporte registrado fue leído al generarse.
    } else {
        $tasa_lectura_reportes = "0%";
    }

    // Tiempo promedio de análisis por caso:
    // Como mencionamos, esta es una métrica avanzada.
    // Sin un sistema de tracking de tiempo detallado (ej. con JS para medir tiempo activo en la página),
    // es difícil obtenerlo con precisión. Por ahora, lo dejamos como N/A.
    // Si tienes ideas para implementarlo, podemos abordarlo más adelante.

} catch (PDOException $e) {
    error_log("Error al obtener métricas de reportes en dashboard_especialista.php: " . $e->getMessage());
    // Puedes dejar los valores predeterminados de "N/A" o 0
}

// --- Fin de la lógica de métricas ---
?>

<style>
    /* Estilos específicos para el dashboard del especialista */
    .content-section {
        background-color: #e8f5e9; /* Un verde muy claro */
        border: 1px solid #c8e6c9; /* Borde verde suave */
        padding: 40px;
        margin-top: 30px;
        text-align: center;
    }

    .content-section h2 {
        color: #2e7d32; /* Verde oscuro */
        margin-bottom: 25px;
        font-size: 2.2em;
    }

    .content-section p {
        color: #555;
        font-size: 1.1em;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    .content-section ul {
        list-style: none;
        padding: 0;
        margin-bottom: 40px;
    }

    .content-section ul li {
        margin-bottom: 15px;
    }

    .content-section ul li a {
        display: inline-block;
        background-color: #4CAF50; /* Verde principal */
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s ease, transform 0.2s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .content-section ul li a:hover {
        background-color: #388E3C; /* Verde más oscuro al pasar el ratón */
        transform: translateY(-2px); /* Pequeño efecto de elevación */
    }

    /* Estilo para el botón de cerrar sesión */
    .content-section .btn {
        background-color: #f44336; /* Rojo para cerrar sesión */
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .content-section .btn:hover {
        background-color: #d32f2f; /* Rojo más oscuro al pasar el ratón */
    }

    .metrics-container {
        background-color: #f0f8ff; /* Azul muy claro */
        border: 1px solid #b0e0e6; /* Borde azul suave */
        padding: 20px;
        margin: 30px auto;
        border-radius: 8px;
        text-align: left;
        max-width: 600px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .metrics-container h3 {
        color: #007bff; /* Azul primario */
        margin-bottom: 15px;
        border-bottom: 1px dashed #c0c0c0;
        padding-bottom: 10px;
    }

    .metrics-container p {
        font-size: 1em;
        margin-bottom: 10px;
        color: #333;
    }

    .metrics-container p strong {
        color: #0056b3;
    }

</style>

<section class="content-section">
    <h2>Bienvenido, Especialista <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h2>
    <p>Este es tu panel de control como especialista. Aquí podrás gestionar tu perfil, ver casos y acceder a herramientas.</p>

    <div class="metrics-container">
        <h3>Estadísticas de Reportes de Caso</h3>
        <p><strong>Nº de reportes de pacientes revisados:</strong> <?php echo $num_reportes_generados; ?></p>
        <p><strong>Tasa de lectura de reportes:</strong> <?php echo $tasa_lectura_reportes; ?></p>
        <p><strong>Tiempo promedio de análisis por caso:</strong> <?php echo $tiempo_promedio_analisis; ?></p>
        <p style="font-size: 0.9em; color: #666; margin-top: 15px;">
            <small>Nota: La tasa de lectura actual asume que todos los reportes generados en el sistema son leídos al momento de generarse. El tiempo promedio de análisis es una métrica avanzada que requiere implementación adicional.</small>
        </p>
    </div>

    <ul>
        <li><a href="perfil.php">Gestionar mi perfil profesional</a></li>
        <li><a href="#">Ver solicitudes de pacientes (funcionalidad futura)</a></li>
        <li><a href="#">Acceder a herramientas para especialistas</a></li>
        <li><a href="ver_citas_especialista.php">Gestionar Citas de Pacientes</a></li>
        <li><a href="ver_respuestas_encuestas.php">Ver Respuestas de Encuestas</a></li>
        <li><a href="ver_alertas_especialista.php">Ver Alertas de Pacientes</a></li>
        <li><a href="calendario_especialista.php">Ver mi Calendario de Citas</a></li>
    </ul>
    <p><a href="logout.php" class="btn">Cerrar Sesión</a></p>
</section>

<?php include 'includes/footer.php'; ?>