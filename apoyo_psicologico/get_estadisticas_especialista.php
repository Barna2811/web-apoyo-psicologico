<?php
// get_estadisticas_especialista.php

include 'includes/db_config.php'; // Tu archivo de conexión a la base de datos

header('Content-Type: application/json'); // Indicar que la respuesta es JSON

$especialista_id = $_GET['especialista_id'] ?? null;
$response = ['success' => false, 'message' => ''];

if ($especialista_id) {
    try {
        // --- Nº de citas por psicólogo (Últimos 30 días) ---
        $stmt_citas = $pdo->prepare("
            SELECT COUNT(id) AS total_citas
            FROM citas
            WHERE especialista_id = ?
            AND estado = 'confirmada'
            AND fecha_cita >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $stmt_citas->execute([$especialista_id]);
        $num_citas_confirmadas = $stmt_citas->fetchColumn();

        // --- Tiempo promedio por sesión ---
        // Esto asume que tienes una duración estándar o registrada.
        // Si no registras la duración, puedes asumir un valor (ej. 60 minutos)
        // O si tienes una columna 'duracion_minutos' en 'citas':
        // SELECT AVG(duracion_minutos) ...

        // Para este ejemplo, si no tienes duración_minutos en tu tabla citas,
        // asumiremos que cada sesión es de 60 minutos y calcularemos el promedio
        // de las citas confirmadas con ese valor fijo.
        $tiempo_promedio_sesion = 60; // Asumimos 60 minutos por sesión

        // Si tuvieras una columna 'duracion_minutos' en citas:
        // $stmt_duracion = $pdo->prepare("
        //     SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_cita, fecha_fin_cita)) AS avg_duration
        //     FROM citas
        //     WHERE especialista_id = ? AND estado = 'completada'
        // ");
        // $stmt_duracion->execute([$especialista_id]);
        // $tiempo_promedio_sesion = round($stmt_duracion->fetchColumn() ?? 0);


        // --- Tasa de disponibilidad vs demanda ---
        // Esto es más complejo y requeriría una lógica para:
        // 1. Calcular el tiempo total disponible del especialista en un periodo (ej. 30 días)
        //    (sumando los bloques de disponibilidad de la tabla 'disponibilidad_especialista')
        // 2. Calcular el tiempo total ocupado por citas confirmadas en ese mismo periodo.
        // Tasa = (Tiempo Ocupado / Tiempo Disponible) * 100

        // Por ahora, solo devolveremos un valor de ejemplo o lo omitiremos si no tienes 'disponibilidad_especialista' llena
        $tasa_disponibilidad = "N/A"; // Valor por defecto

        $response['success'] = true;
        $response['num_citas_confirmadas'] = $num_citas_confirmadas;
        $response['tiempo_promedio_sesion'] = $tiempo_promedio_sesion;
        $response['tasa_disponibilidad'] = $tasa_disponibilidad; // Por ahora, N/A

    } catch (PDOException $e) {
        $response['message'] = 'Error al cargar estadísticas: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ID de especialista no proporcionado.';
}

echo json_encode($response);
?>