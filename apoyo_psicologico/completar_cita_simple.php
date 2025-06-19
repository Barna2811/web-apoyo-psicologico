<?php
// completar_cita_simple.php

session_start();
include_once 'includes/db_config.php';
// Puedes incluir includes/funciones.php si deseas añadir una notificación al paciente
// include_once 'includes/funciones.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php");
    exit();
}

$especialista_id = $_SESSION['usuario_id'];
$cita_id = $_GET['id'] ?? null; // Ahora viene por GET
$paciente_id = $_GET['paciente_id'] ?? null; // Ahora viene por GET

if ($cita_id && $paciente_id) {
    try {
        $pdo->beginTransaction();

        // Solo actualizamos el estado a 'completada'
        $stmt_update = $pdo->prepare("UPDATE citas SET estado = 'completada' WHERE id = ? AND especialista_id = ?");
        $stmt_update->execute([$cita_id, $especialista_id]);

        // Opcional: Notificar al paciente que la sesión ha sido completada
        /*
        if (function_exists('crearNotificacion')) {
            crearNotificacion($pdo, $paciente_id, 'sesion_completada', 'Tu sesión con el especialista ha sido marcada como completada.', 'dashboard_paciente.php');
        }
        */

        $pdo->commit();
        header("Location: ver_citas_especialista.php?mensaje=Cita%20marcada%20como%20completada.");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error al completar cita (simple): " . $e->getMessage());
        header("Location: ver_citas_especialista.php?error=Error%20al%20completar%20cita:%20" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: ver_citas_especialista.php?error=Datos%20de%20cita%20incompletos.");
    exit();
}
?>