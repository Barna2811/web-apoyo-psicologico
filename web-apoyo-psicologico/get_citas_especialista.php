<?php
// get_citas_especialista.php

include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

header('Content-Type: application/json'); // Indicar que la respuesta es JSON

$especialista_id = $_GET['especialista_id'] ?? null;
$events = [];

if ($especialista_id) {
    try {
        // Obtener citas confirmadas para el especialista logueado
        $stmt = $pdo->prepare("
            SELECT
                c.id,
                c.fecha_cita,
                c.estado,
                c.notas_paciente,
                u.nombre_usuario AS paciente_nombre
            FROM
                citas c
            JOIN
                usuarios u ON c.paciente_id = u.id
            WHERE
                c.especialista_id = ?
                AND c.estado = 'confirmada'
            ORDER BY c.fecha_cita ASC
        ");
        $stmt->execute([$especialista_id]);
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($citas as $cita) {
            $events[] = [
                'id' => $cita['id'],
                'title' => 'Cita con ' . htmlspecialchars($cita['paciente_nombre']),
                'start' => $cita['fecha_cita'], // FullCalendar entiende el formato DATETIME
                'end' => date('Y-m-d H:i:s', strtotime($cita['fecha_cita']) + (60 * 60)), // Asume duración de 1 hora
                'color' => '#6495ed', // Color de los eventos, puedes cambiarlo
                'extendedProps' => [ // Propiedades adicionales que puedes usar
                    'estado' => $cita['estado'],
                    'paciente_nombre' => htmlspecialchars($cita['paciente_nombre']),
                    'notas_paciente' => htmlspecialchars($cita['notas_paciente'])
                ]
            ];
        }

        echo json_encode($events);

    } catch (PDOException $e) {
        // En caso de error, devuelve un JSON con un mensaje de error
        echo json_encode(['error' => 'Error al cargar citas: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID de especialista no proporcionado.']);
}
?>