<?php
// detectar_alertas.php

// Incluir la configuración de la base de datos
// Asegúrate de que esta ruta sea correcta para tu archivo db_config.php
include 'includes/db_config.php';

/**
 * Función para detectar el deterioro emocional de los pacientes y generar alertas.
 * Esta función debería ser ejecutada periódicamente (ej. cada día, cada semana)
 * por una tarea programada (cron job en Linux/macOS, Programador de tareas en Windows).
 */
function detectarDeterioroEmocional($pdo) {
    echo "Iniciando detección de deterioro emocional...<br>";

    // 1. Obtener todos los IDs de los pacientes
    $stmt_pacientes = $pdo->prepare("SELECT id FROM usuarios WHERE tipo_usuario = 'paciente'");
    $stmt_pacientes->execute();
    $pacientes_ids = $stmt_pacientes->fetchAll(PDO::FETCH_COLUMN);

    if (empty($pacientes_ids)) {
        echo "No hay pacientes registrados.<br>";
        return;
    }

    foreach ($pacientes_ids as $paciente_id) {
        echo "Procesando paciente ID: " . $paciente_id . "<br>";

        // 2. Obtener el promedio de puntuación emocional de la ÚLTIMA SEMANA
        // Usamos CURDATE() para obtener solo la fecha actual sin la hora
        $stmt_promedio_actual = $pdo->prepare("
            SELECT AVG(puntuacion_emocional)
            FROM respuestas_pacientes_encuesta
            WHERE paciente_id = ?
            AND fecha_respuesta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ");
        $stmt_promedio_actual->execute([$paciente_id]);
        $promedio_actual = (float)$stmt_promedio_actual->fetchColumn(); // Convertir a float

        // 3. Obtener el promedio de la SEMANA ANTERIOR
        $stmt_promedio_anterior = $pdo->prepare("
            SELECT AVG(puntuacion_emocional)
            FROM respuestas_pacientes_encuesta
            WHERE paciente_id = ?
            AND fecha_respuesta >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            AND fecha_respuesta < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ");
        $stmt_promedio_anterior->execute([$paciente_id]);
        $promedio_anterior = (float)$stmt_promedio_anterior->fetchColumn(); // Convertir a float

        // 4. Lógica de comparación y generación de alerta
        // Asegurarse de que ambos promedios existan (no sean 0.0 de fetchColumn si no hay datos)
        if ($promedio_actual > 0 && $promedio_anterior > 0) {
            $diferencia = $promedio_anterior - $promedio_actual; // Diferencia positiva indica deterioro

            // Definir umbrales para los niveles de alerta (ajusta estos valores según tus necesidades)
            $umbral_critico = 2.0; // Si el promedio baja 2 puntos o más
            $umbral_alto = 1.0;   // Si el promedio baja 1 punto o más
            $umbral_medio = 0.5;  // Si el promedio baja 0.5 puntos o más

            $nivel_urgencia = null;
            $descripcion_alerta = null;

            if ($diferencia >= $umbral_critico) {
                $nivel_urgencia = 'critica';
                $descripcion_alerta = "Deterioro CRÍTICO en el puntaje emocional. El promedio bajó " . round($diferencia, 2) . " puntos (Actual: " . round($promedio_actual, 2) . ", Anterior: " . round($promedio_anterior, 2) . ").";
            } elseif ($diferencia >= $umbral_alto) {
                $nivel_urgencia = 'alta';
                $descripcion_alerta = "Deterioro ALTO en el puntaje emocional. El promedio bajó " . round($diferencia, 2) . " puntos (Actual: " . round($promedio_actual, 2) . ", Anterior: " . round($promedio_anterior, 2) . ").";
            } elseif ($diferencia >= $umbral_medio) {
                $nivel_urgencia = 'media';
                $descripcion_alerta = "Deterioro MEDIO en el puntaje emocional. El promedio bajó " . round($diferencia, 2) . " puntos (Actual: " . round($promedio_actual, 2) . ", Anterior: " . round($promedio_anterior, 2) . ").";
            }

            // 5. Insertar la alerta en la base de datos si se detectó un deterioro
            if ($nivel_urgencia) {
                // Primero, verificar si ya existe una alerta activa similar para evitar duplicados excesivos
                $stmt_check_alerta = $pdo->prepare("
                    SELECT COUNT(*) FROM alertas
                    WHERE paciente_id = ?
                    AND nivel_urgencia = ?
                    AND atendida = FALSE
                    AND fecha_alerta >= DATE_SUB(NOW(), INTERVAL 24 HOUR) -- Alerta similar en las últimas 24h
                ");
                $stmt_check_alerta->execute([$paciente_id, $nivel_urgencia]);
                $alerta_existente = $stmt_check_alerta->fetchColumn();

                if ($alerta_existente == 0) { // Solo inserta si no hay una alerta similar reciente
                    $stmt_insert_alerta = $pdo->prepare("
                        INSERT INTO alertas (paciente_id, descripcion, nivel_urgencia)
                        VALUES (?, ?, ?)
                    ");
                    $stmt_insert_alerta->execute([$paciente_id, $descripcion_alerta, $nivel_urgencia]);
                    echo "Alerta generada para paciente ID " . $paciente_id . ": " . $descripcion_alerta . "<br>";
                } else {
                    echo "Ya existe una alerta similar activa para paciente ID " . $paciente_id . ". No se generó una nueva.<br>";
                }
            } else {
                echo "No se detectó deterioro significativo para paciente ID " . $paciente_id . ".<br>";
            }
        } else {
            echo "No hay suficientes datos de encuestas para el paciente ID " . $paciente_id . " en ambos períodos para comparar.<br>";
        }
    }
    echo "Detección de deterioro emocional finalizada.<br>";
}

// Llama a la función para ejecutar la detección (cuando el script se ejecute)
// Esta línea es solo para prueba, normalmente se ejecutaría a través de una tarea programada
detectarDeterioroEmocional($pdo);

?>