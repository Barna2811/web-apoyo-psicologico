<?php
// includes/funciones.php

// Asegúrate de que $pdo esté disponible, si no, incluye db_config.php aquí.
// Ejemplo: include 'db_config.php'; // Si este archivo se incluye solo, necesita la conexión.

/**
 * Función para crear y guardar una notificación en la base de datos.
 * @param PDO $pdo Objeto PDO de la conexión a la base de datos.
 * @param int $usuario_id El ID del usuario que recibirá la notificación.
 * @param string $tipo El tipo de notificación (ej. 'cita_agendada', 'alerta_deterioro').
 * @param string $mensaje El contenido del mensaje de la notificación.
 * @param string|null $enlace Un enlace opcional para la notificación.
 * @return bool True si la notificación se guardó correctamente, false en caso contrario.
 */
function crearNotificacion($pdo, $usuario_id, $tipo, $mensaje, $enlace = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, mensaje, enlace) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$usuario_id, $tipo, $mensaje, $enlace]);
    } catch (PDOException $e) {
        error_log("Error al crear notificación: " . $e->getMessage()); // Registra el error para depuración
        return false;
    }
}
?>