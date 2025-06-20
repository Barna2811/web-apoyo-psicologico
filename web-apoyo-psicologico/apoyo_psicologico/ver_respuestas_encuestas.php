<?php
session_start();
include 'includes/header.php';
include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

// Verificar si el usuario está logueado y es especialista
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php");
    exit();
}

$mensaje = '';
$respuestas = [];

try {
    // Unir tablas para obtener el nombre del paciente y el texto de la pregunta
    $stmt_respuestas = $pdo->query("
        SELECT
            r.id,
            u.nombre_usuario AS paciente_nombre,
            e.titulo AS encuesta_titulo,
            p.pregunta_texto,
            r.respuesta_valor,
            r.puntuacion_emocional,
            r.fecha_respuesta
        FROM
            respuestas_pacientes_encuesta r
        JOIN
            usuarios u ON r.paciente_id = u.id
        JOIN
            preguntas_encuesta p ON r.pregunta_id = p.id
        JOIN
            encuestas e ON p.encuesta_id = e.id
        ORDER BY
            r.fecha_respuesta DESC, u.nombre_usuario ASC
        LIMIT 50 -- Limita el número de respuestas para no cargar demasiado
    ");
    $respuestas = $stmt_respuestas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mensaje = "<p style='color: red;'>Error al cargar las respuestas: " . $e->getMessage() . "</p>";
}
?>

<section class="content-section">
    <h2>Respuestas de Encuestas de Pacientes</h2>
    <?php echo $mensaje; ?>

    <?php if (empty($respuestas)): ?>
        <p>No hay respuestas de encuestas registradas todavía.</p>
    <?php else: ?>
        <table border="1" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 10px; text-align: left;">Paciente</th>
                    <th style="padding: 10px; text-align: left;">Encuesta</th>
                    <th style="padding: 10px; text-align: left;">Pregunta</th>
                    <th style="padding: 10px; text-align: left;">Respuesta</th>
                    <th style="padding: 10px; text-align: left;">Puntuación</th>
                    <th style="padding: 10px; text-align: left;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($respuestas as $res): ?>
                    <tr>
                        <td style="padding: 10px; text-align: left;"><?php echo htmlspecialchars($res['paciente_nombre']); ?></td>
                        <td style="padding: 10px; text-align: left;"><?php echo htmlspecialchars($res['encuesta_titulo']); ?></td>
                        <td style="padding: 10px; text-align: left;"><?php echo htmlspecialchars($res['pregunta_texto']); ?></td>
                        <td style="padding: 10px; text-align: left;"><?php echo htmlspecialchars($res['respuesta_valor'] ?? 'N/A'); ?></td>
                        <td style="padding: 10px; text-align: left;"><?php echo htmlspecialchars($res['puntuacion_emocional'] ?? 'N/A'); ?></td>
                        <td style="padding: 10px; text-align: left;"><?php echo date('d/m/Y H:i', strtotime($res['fecha_respuesta'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <p style="margin-top: 20px;"><a href="dashboard_especialista.php" class="btn" style="background-color: #ccc;">Volver al Dashboard</a></p>
</section>

<?php include 'includes/footer.php'; ?>