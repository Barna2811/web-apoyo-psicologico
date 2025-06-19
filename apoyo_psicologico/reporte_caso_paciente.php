<?php
// reporte_caso_paciente.php

session_start();
include 'includes/header.php';
include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

// Verificar si el usuario está logueado y es especialista
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php");
    exit();
}

$paciente_id = $_GET['paciente_id'] ?? null;
$reporte_generado = false;
$mensaje = '';

if ($paciente_id) {
    try {
        // --- 1. Obtener Información Básica del Paciente ---
        $stmt_paciente = $pdo->prepare("SELECT id, nombre_usuario, email FROM usuarios WHERE id = ? AND tipo_usuario = 'paciente'");
        $stmt_paciente->execute([$paciente_id]);
        $paciente_info = $stmt_paciente->fetch(PDO::FETCH_ASSOC);

        if (!$paciente_info) {
            $mensaje = "<p style='color: red;'>Paciente no encontrado o no es un paciente válido.</p>";
        } else {
            $reporte_generado = true;

            // --- 2. Historial de Citas ---
            $stmt_citas = $pdo->prepare("SELECT fecha_cita, estado, notas_especialista FROM citas WHERE paciente_id = ? ORDER BY fecha_cita DESC");
            $stmt_citas->execute([$paciente_id]);
            $historial_citas = $stmt_citas->fetchAll(PDO::FETCH_ASSOC);

            // --- 3. Historial de Encuestas Emocionales (Tendencias y Alertas) ---
            // Promedio de las últimas 4 encuestas (ejemplo)
            $stmt_encuestas_promedio = $pdo->prepare("
                SELECT AVG(puntuacion_emocional) AS promedio_emocional
                FROM (
                    SELECT puntuacion_emocional FROM respuestas_pacientes_encuesta
                    WHERE paciente_id = ? ORDER BY fecha_respuesta DESC LIMIT 4
                ) AS ultimas_encuestas;
            ");
            $stmt_encuestas_promedio->execute([$paciente_id]);
            $promedio_emocional = $stmt_encuestas_promedio->fetchColumn();

            // Últimas alertas generadas para este paciente
            $stmt_alertas = $pdo->prepare("SELECT fecha_alerta, nivel_urgencia, descripcion FROM alertas WHERE paciente_id = ? ORDER BY fecha_alerta DESC LIMIT 5");
            $stmt_alertas->execute([$paciente_id]);
            $alertas_paciente = $stmt_alertas->fetchAll(PDO::FETCH_ASSOC);

            // --- 4. Registrar la generación/lectura del reporte ---
            // Verifica si ya se ha registrado que este especialista vio este reporte hoy
            $stmt_check_log = $pdo->prepare("SELECT id FROM reportes_log WHERE especialista_id = ? AND paciente_id = ? AND DATE(fecha_generacion) = CURDATE()");
            $stmt_check_log->execute([$_SESSION['usuario_id'], $paciente_id]);

            if ($stmt_check_log->rowCount() == 0) {
                // Si no hay un registro para hoy, inserta uno nuevo
                $stmt_insert_log = $pdo->prepare("INSERT INTO reportes_log (especialista_id, paciente_id, fecha_lectura) VALUES (?, ?, NOW())");
                $stmt_insert_log->execute([$_SESSION['usuario_id'], $paciente_id]);
            } else {
                // Si ya existe un registro para hoy, puedes actualizar la fecha_lectura si quieres
                // O simplemente no hacer nada, ya que ya se registró la lectura de hoy.
                // Para este caso, vamos a actualizar la fecha de lectura por si el especialista lo abre varias veces
                $stmt_update_log = $pdo->prepare("UPDATE reportes_log SET fecha_lectura = NOW() WHERE especialista_id = ? AND paciente_id = ? AND DATE(fecha_generacion) = CURDATE()");
                $stmt_update_log->execute([$_SESSION['usuario_id'], $paciente_id]);
            }
            // Fin de la lógica de registro

        }

    } catch (PDOException $e) {
        $mensaje = "<p style='color: red;'>Error al cargar el reporte: " . $e->getMessage() . "</p>";
        error_log("Error en reporte_caso_paciente.php: " . $e->getMessage()); // Para depuración
    }
} else {
    $mensaje = "<p style='color: orange;'>Por favor, selecciona un paciente para generar el reporte.</p>";
}
?>

<style>
    .report-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        padding: 30px;
        margin: 20px auto;
        max-width: 800px;
        line-height: 1.6;
    }
    .report-card h3 {
        color: #0056b3;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 10px;
        margin-top: 25px;
        margin-bottom: 15px;
    }
    .report-card p {
        margin-bottom: 8px;
    }
    .report-section {
        margin-bottom: 30px;
    }
    .report-section ul {
        list-style: none;
        padding: 0;
    }
    .report-section ul li {
        background-color: #f8f8f8;
        border: 1px solid #eee;
        padding: 10px;
        margin-bottom: 8px;
        border-radius: 5px;
    }
    .alert-item {
        background-color: #ffebee; /* Rojo claro para alertas */
        border-left: 5px solid #c62828;
        padding: 10px;
        margin-bottom: 8px;
        border-radius: 5px;
    }
    .alert-item.critica { background-color: #ffcdd2; border-color: #d32f2f; }
    .alert-item.alta { background-color: #ffecb3; border-color: #ffa000; }
    .alert-item.media { background-color: #fff9c4; border-color: #fbc02d; }
    .alert-item.baja { background-color: #e8f5e9; border-color: #43a047; }

    .no-data {
        color: #777;
        font-style: italic;
    }
</style>

<section class="content-section">
    <h2>Reporte de Caso del Usuario</h2>
    <?php echo $mensaje; ?>

    <?php if ($reporte_generado): ?>
    
    <div class="report-card">
        <div class="report-section">
            <h3>Información del Paciente</h3>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($paciente_info['nombre_usuario']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($paciente_info['email']); ?></p>
            </div>

        <div class="report-section">
            <h3>Historial de Citas</h3>
            <?php if (!empty($historial_citas)): ?>
                <ul>
                    <?php foreach ($historial_citas as $cita): ?>
                        <li>
                            <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($cita['fecha_cita'])); ?><br>
                            <strong>Estado:</strong> <?php echo htmlspecialchars(ucfirst($cita['estado'])); ?><br>
                            <strong>Notas del Especialista:</strong> <?php echo empty($cita['notas_especialista']) ? '<span class="no-data">No hay notas registradas.</span>' : htmlspecialchars($cita['notas_especialista']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-data">No hay historial de citas para este paciente.</p>
            <?php endif; ?>
        </div>

        <div class="report-section">
            <h3>Estado Emocional (Encuestas y Alertas)</h3>
            <p><strong>Promedio Emocional (últimas 4 encuestas):</strong>
                <?php echo ($promedio_emocional !== null) ? round($promedio_emocional, 2) : '<span class="no-data">No hay datos suficientes de encuestas.</span>'; ?>
            </p>

            <h4>Alertas Recientes</h4>
            <?php if (!empty($alertas_paciente)): ?>
                <ul>
                    <?php foreach ($alertas_paciente as $alerta): ?>
                        <li class="alert-item <?php echo htmlspecialchars($alerta['nivel_urgencia']); ?>">
                            <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($alerta['fecha_alerta'])); ?><br>
                            <strong>Nivel de Urgencia:</strong> <?php echo htmlspecialchars(ucfirst($alerta['nivel_urgencia'])); ?><br>
                            <strong>Descripción:</strong> <?php echo htmlspecialchars($alerta['descripcion']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-data">No se han generado alertas recientes para este paciente.</p>
            <?php endif; ?>
        </div>

        </div>
    <?php endif; ?>

    <p style="margin-top: 30px;"><a href="dashboard_especialista.php" class="btn" style="background-color: #6c757d;">Volver al Dashboard</a></p>
</section>

<?php include 'includes/footer.php'; ?>