<?php
session_start();
include 'includes/header.php';
include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

// Verificar si el usuario está logueado y es paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'paciente') {
    header("Location: ingresar.php");
    exit();
}

$paciente_id = $_SESSION['usuario_id'];
$mensaje = '';

// Obtener la encuesta activa (podrías expandir esto para seleccionar una encuesta específica si tuvieras varias)
try {
    $stmt_encuesta = $pdo->prepare("SELECT id, titulo, descripcion FROM encuestas WHERE activa = TRUE LIMIT 1");
    $stmt_encuesta->execute();
    $encuesta = $stmt_encuesta->fetch(PDO::FETCH_ASSOC);

    if (!$encuesta) {
        $mensaje = "<p style='color: orange;'>No hay encuestas activas en este momento.</p>";
        $preguntas = [];
    } else {
        // Obtener las preguntas para esta encuesta
        $stmt_preguntas = $pdo->prepare("SELECT id, pregunta_texto, tipo_respuesta FROM preguntas_encuesta WHERE encuesta_id = ? ORDER BY orden ASC");
        $stmt_preguntas->execute([$encuesta['id']]);
        $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);

        // Verificar si el paciente ya respondió esta encuesta hoy (o esta semana, según tu lógica)
        // Esto es una simplificación; podrías tener una tabla de asignación de encuestas o cron jobs.
        $stmt_respondido = $pdo->prepare("SELECT COUNT(*) FROM respuestas_pacientes_encuesta WHERE paciente_id = ? AND pregunta_id IN (SELECT id FROM preguntas_encuesta WHERE encuesta_id = ?) AND DATE(fecha_respuesta) = CURDATE()");
        $stmt_respondido->execute([$paciente_id, $encuesta['id']]);
        $ya_respondio_hoy = $stmt_respondido->fetchColumn() > 0;

        if ($ya_respondio_hoy) {
            $mensaje = "<p style='color: blue;'>Ya has respondido esta encuesta hoy. ¡Gracias!</p>";
        }
    }
} catch (PDOException $e) {
    $mensaje = "<p style='color: red;'>Error al cargar la encuesta: " . $e->getMessage() . "</p>";
    $preguntas = [];
    $encuesta = null;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && $encuesta && !$ya_respondio_hoy) {
    try {
        $pdo->beginTransaction(); // Iniciar una transacción para asegurar que todas las respuestas se guarden o ninguna

        foreach ($preguntas as $pregunta) {
            $pregunta_id = $pregunta['id'];
            $respuesta_valor = $_POST['pregunta_' . $pregunta_id] ?? null;
            $puntuacion_emocional = null;

            // Lógica para asignar puntuación si es una escala
            if ($pregunta['tipo_respuesta'] == 'escala_1_5' && is_numeric($respuesta_valor)) {
                $puntuacion_emocional = (int)$respuesta_valor;
            }

            $stmt_insert_respuesta = $pdo->prepare("INSERT INTO respuestas_pacientes_encuesta (paciente_id, pregunta_id, respuesta_valor, puntuacion_emocional) VALUES (?, ?, ?, ?)");
            $stmt_insert_respuesta->execute([$paciente_id, $pregunta_id, $respuesta_valor, $puntuacion_emocional]);
        }

        $pdo->commit(); // Confirmar la transacción
        $mensaje = "<p style='color: green;'>¡Encuesta enviada con éxito! Gracias por tu feedback.</p>";
        // Marcar como respondido para evitar múltiples envíos en la misma carga de página
        $ya_respondio_hoy = true; 

    } catch (PDOException $e) {
        $pdo->rollBack(); // Deshacer la transacción si algo falla
        $mensaje = "<p style='color: red;'>Error al guardar las respuestas: " . $e->getMessage() . "</p>";
    }
}
?>

<section class="content-section">
    <?php if ($encuesta): ?>
        <h2><?php echo htmlspecialchars($encuesta['titulo']); ?></h2>
        <p><?php echo htmlspecialchars($encuesta['descripcion']); ?></p>

        <?php echo $mensaje; ?>

        <?php if ($ya_respondio_hoy): ?>
            <p>Ya has completado esta encuesta recientemente. Por favor, revisa tu progreso en el dashboard.</p>
        <?php elseif (empty($preguntas)): ?>
            <p>Esta encuesta no tiene preguntas definidas.</p>
        <?php else: ?>
            <form action="responder_encuesta.php" method="POST" style="max-width: 600px; margin: 0 auto; text-align: left;">
                <?php foreach ($preguntas as $pregunta): ?>
                    <div style="margin-bottom: 20px; border: 1px solid #eee; padding: 15px; border-radius: 8px; background-color: #f9f9f9;">
                        <label for="pregunta_<?php echo $pregunta['id']; ?>" style="display: block; margin-bottom: 10px; font-weight: bold; color: #333; font-size: 1.1em;">
                            <?php echo htmlspecialchars($pregunta['orden'] ?? '') . '. ' . htmlspecialchars($pregunta['pregunta_texto']); ?>
                        </label>
                        <?php if ($pregunta['tipo_respuesta'] == 'escala_1_5'): ?>
                            <div style="display: flex; justify-content: space-around; margin-top: 10px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <label style="cursor: pointer;">
                                        <input type="radio" name="pregunta_<?php echo $pregunta['id']; ?>" value="<?php echo $i; ?>" required>
                                        <?php echo $i; ?>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <small style="display: block; text-align: center; margin-top: 5px; color: #666;">(1 = Para nada, 5 = Mucho)</small>
                        <?php elseif ($pregunta['tipo_respuesta'] == 'si_no'): ?>
                            <div style="margin-top: 10px;">
                                <label style="margin-right: 15px; cursor: pointer;">
                                    <input type="radio" name="pregunta_<?php echo $pregunta['id']; ?>" value="Si" required> Sí
                                </label>
                                <label style="cursor: pointer;">
                                    <input type="radio" name="pregunta_<?php echo $pregunta['id']; ?>" value="No" required> No
                                </label>
                            </div>
                        <?php elseif ($pregunta['tipo_respuesta'] == 'texto_corto'): ?>
                            <input type="text" name="pregunta_<?php echo $pregunta['id']; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
                        <?php elseif ($pregunta['tipo_respuesta'] == 'texto_largo'): ?>
                            <textarea name="pregunta_<?php echo $pregunta['id']; ?>" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn" style="background-color: #6495ed; color: white; border: none; cursor: pointer; padding: 12px 30px; margin-top: 20px;">Enviar Encuesta</button>
            </form>
        <?php endif; ?>

    <?php else: ?>
        <p><?php echo $mensaje; ?></p>
    <?php endif; ?>
    <p style="margin-top: 20px;"><a href="dashboard_paciente.php" class="btn" style="background-color: #ccc;">Volver al Dashboard</a></p>
</section>

<?php include 'includes/footer.php'; ?>