<?php
// guardar_cita_data.php

session_start();
include_once 'includes/db_config.php';
include_once 'includes/funciones.php'; // Incluye el archivo donde está crearNotificacion()

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php");
    exit();
}

$especialista_id = $_SESSION['usuario_id'];
$cita_id = $_POST['cita_id'] ?? null;
$paciente_id = $_POST['paciente_id'] ?? null;
$enlace_videollamada = trim($_POST['enlace_videollamada'] ?? ''); // Limpiar espacios
$notas_especialista = $_POST['notas_especialista_hidden'] ?? ''; // Notas del textarea

if ($cita_id && $paciente_id) {
    try {
        $pdo->beginTransaction();

        // 1. Actualizar el enlace de videollamada y las notas del especialista
        $stmt_update = $pdo->prepare("UPDATE citas SET enlace_videollamada = ?, notas_especialista = ? WHERE id = ? AND especialista_id = ?");
        $stmt_update->execute([$enlace_videollamada, $notas_especialista, $cita_id, $especialista_id]);

        // 2. Notificar al paciente si se ha guardado un enlace (o si ya existía y se actualizó)
        if (!empty($enlace_videollamada)) {
            $mensaje_notif = "¡Cita importante! Tu especialista ha agregado/actualizado el enlace para la videollamada. Haz clic en 'Unirme a la Videollamada' en tu dashboard o usa este enlace: <a href='" . htmlspecialchars($enlace_videollamada) . "' target='_blank'>Unirme a la Videollamada</a>";
            $enlace_notif = "dashboard_paciente.php";

            crearNotificacion($pdo, $paciente_id, 'enlace_videollamada_listo', $mensaje_notif, $enlace_notif);
        }

        $pdo->commit();
        header("Location: ver_citas_especialista.php?mensaje=Datos%20de%20la%20cita%20guardados%20y%20paciente%20notificado%20(si%20se%20proporciono%20enlace).");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error al guardar datos de la cita: " . $e->getMessage());
        header("Location: ver_citas_especialista.php?error=Error%20al%20guardar%20datos:%20" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: ver_citas_especialista.php?error=Datos%20de%20cita%20incompletos.");
    exit();
}
?>