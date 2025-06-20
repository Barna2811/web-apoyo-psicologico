<?php
// marcar_alerta_atendida.php

session_start();
include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

// Verificar que el usuario esté logueado y sea especialista
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'especialista') {
    header("Location: ingresar.php");
    exit();
}

$alerta_id = $_GET['id'] ?? null; // Obtener el ID de la alerta desde la URL

if ($alerta_id) {
    try {
        // Actualizar el estado de la alerta a atendida y registrar la fecha de atención
        $stmt_update = $pdo->prepare("
            UPDATE alertas
            SET atendida = TRUE, fecha_atencion = NOW()
            WHERE id = ?
        ");
        $stmt_update->execute([$alerta_id]);

        // Redirigir de nuevo a la página de alertas
        header("Location: ver_alertas_especialista.php?mensaje=Alerta%20marcada%20como%20atendida.");
        exit();

    } catch (PDOException $e) {
        // En caso de error, puedes redirigir con un mensaje de error o registrarlo
        header("Location: ver_alertas_especialista.php?error=Error%20al%20marcar%20alerta.");
        exit();
    }
} else {
    // Si no se proporcionó un ID de alerta, redirigir con un error
    header("Location: ver_alertas_especialista.php?error=ID%20de%20alerta%20no%20proporcionado.");
    exit();
}
?>