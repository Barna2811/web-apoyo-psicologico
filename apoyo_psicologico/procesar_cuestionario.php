<?php
session_start();
include 'includes/header.php';
include 'database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ingresar.php?mensaje_error=Necesitas iniciar%20sesi%C3%B3n%20para%20guardar%20tus%20resultados.");
    exit();
}

$respuestas = [];
for ($i = 1; $i <= 20; $i++) {
    $respuestas['q' . $i] = isset($_POST['q' . $i]) ? $_POST['q' . $i] : 'no';
}

$puntuacion_depresion = 0;
$puntuacion_ansiedad = 0;
$puntuacion_estres_postraumatico = 0;
$puntuacion_TOC = 0;
$puntuacion_bipolar = 0;
$puntuacion_general_malestar = 0;

$preguntas_depresion = [1, 2, 3, 4, 5, 6, 7];
$preguntas_ansiedad = [8, 9, 10];
$preguntas_estres_postraumatico = [13, 14, 15];
$preguntas_TOC = [11, 12];
$preguntas_bipolar = [16, 17];
$preguntas_generales_malestar = [18, 19, 20];

foreach ($respuestas as $key => $value) {
    $pregunta_num = (int)str_replace('q', '', $key);
    if ($value === 'si') {
        if (in_array($pregunta_num, $preguntas_depresion)) {
            $puntuacion_depresion++;
        }
        if (in_array($pregunta_num, $preguntas_ansiedad)) {
            $puntuacion_ansiedad++;
        }
        if (in_array($pregunta_num, $preguntas_estres_postraumatico)) {
            $puntuacion_estres_postraumatico++;
        }
        if (in_array($pregunta_num, $preguntas_TOC)) {
            $puntuacion_TOC++;
        }
        if (in_array($pregunta_num, $preguntas_bipolar)) {
            $puntuacion_bipolar++;
        }
        if (in_array($pregunta_num, $preguntas_generales_malestar)) {
            $puntuacion_general_malestar++;
        }
    }
}

$resultados_categorias = [
    'Depresión' => $puntuacion_depresion,
    'Ansiedad' => $puntuacion_ansiedad,
    'Estrés Postraumático' => $puntuacion_estres_postraumatico,
    'Trastorno Obsesivo Compulsivo (TOC)' => $puntuacion_TOC,
    'Trastorno Bipolar' => $puntuacion_bipolar // Cambié el texto para que coincida con la especialidad
];

// Determine el resultado principal
$resultado_principal_texto = "Parece que tus respuestas no indican una necesidad significativa en las áreas evaluadas por este cuestionario. Si aún así sientes malestar, no dudes en buscar apoyo.";
$mayor_puntuacion = 0;
$area_sugerida = "General"; // Valor por defecto si no hay puntuaciones altas

foreach ($resultados_categorias as $categoria => $puntuacion) {
    if ($puntuacion > $mayor_puntuacion) {
        $mayor_puntuacion = $puntuacion;
        $area_sugerida = $categoria;
    }
}

// Construir el mensaje de resultado a guardar y mostrar
if ($mayor_puntuacion > 0) {
    $resultado_principal_texto = "Basado en tus respuestas, una de las áreas con mayor puntuación es: **" . htmlspecialchars($area_sugerida) . "** (Síntomas detectados: " . $mayor_puntuacion . "). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental.";
}

if ($puntuacion_general_malestar >= 2) {
    $resultado_principal_texto .= " Además, has indicado algunas preocupaciones generales que podrían afectar tu bienestar. Es importante prestarles atención.";
}

// --- GUARDAR EL RESULTADO COMPLETO EN LA BASE DE DATOS ---
$usuario_id = $_SESSION['usuario_id'];
$resultado_final_a_guardar = $resultado_principal_texto;

$stmt_insert = $conn->prepare("INSERT INTO resultados_cuestionario (usuario_id, resultado) VALUES (?, ?)");
$stmt_insert->bind_param("is", $usuario_id, $resultado_final_a_guardar);

if ($stmt_insert->execute()) {
    $mensaje_guardado = "<p class='success'>Tu resultado ha sido guardado en tu historial.</p>";
} else {
    $mensaje_guardado = "<p class='error'>Hubo un error al guardar tu resultado: " . $stmt_insert->error . "</p>";
}
$stmt_insert->close();

// --- ALMACENAR LA ESPECIALIDAD SUGERIDA EN SESIÓN ---
// Esto es clave para poder usarla en la página de listado de especialistas
$_SESSION['especialidad_sugerida'] = $area_sugerida; // Guardamos la especialidad sugerida

$conn->close();
?>

<style>
    /* Estilos específicos para la sección de resultados del cuestionario */
    /* ... (Mismo CSS que en la respuesta anterior) ... */
    .resultados-cuestionario {
        background-color: #f0f0f0; /* Fondo gris claro */
        border: 1px solid #ccc;
        padding: 30px;
        margin-top: 30px;
        text-align: center;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .resultados-cuestionario h2 {
        color: #333;
        margin-bottom: 20px;
        font-size: 2.5em;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        display: inline-block;
    }

    .resultados-cuestionario p {
        color: #555;
        font-size: 1.1em;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .resultados-cuestionario strong {
        color: #007bff;
    }

    .resultados-cuestionario h3 {
        color: #444;
        margin-top: 30px;
        margin-bottom: 15px;
        font-size: 1.8em;
    }

    .resultados-cuestionario ul {
        list-style: none;
        padding: 0;
        margin-bottom: 30px;
    }

    .resultados-cuestionario ul li {
        background-color: #e9ecef;
        margin-bottom: 10px;
        padding: 12px 20px;
        border-radius: 5px;
        color: #333;
        font-size: 1em;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .resultados-cuestionario .alert-disclaimer {
        background-color: #ffe0b2;
        border-left: 5px solid #ff9800;
        padding: 15px;
        margin-top: 40px;
        font-size: 0.9em;
        color: #333;
        text-align: left;
        border-radius: 5px;
    }

    .resultados-cuestionario .alert-disclaimer strong {
        color: #ff9800;
    }

    .resultados-cuestionario .btn {
        display: inline-block;
        background-color: #28a745;
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s ease;
        margin-top: 20px;
        margin-right: 10px; /* Espacio entre botones */
        margin-left: 10px;
    }

    .resultados-cuestionario .btn:hover {
        background-color: #218838;
    }

    .resultados-cuestionario .success {
        color: #28a745;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .resultados-cuestionario .error {
        color: #dc3545;
        font-weight: bold;
        margin-bottom: 15px;
    }
</style>

<section class="resultados-cuestionario">
    <h2>Resultados de tu Cuestionario</h2>
    <p>Basado en tus respuestas, estas son algunas áreas donde podrías necesitar apoyo. Recuerda que esto es una orientación, no un diagnóstico profesional.</p>

    <?php echo $mensaje_guardado ?? ''; ?>

    <?php if (!empty($area_sugerida) && $mayor_puntuacion > 0): ?>
        <p>Una de las áreas con mayor puntuación es: <strong><?php echo htmlspecialchars($area_sugerida); ?></strong> (Síntomas detectados: <?php echo $mayor_puntuacion; ?>).</p>
        <p>Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental.</p>
    <?php else: ?>
        <p>Parece que tus respuestas no indican una necesidad significativa en las áreas evaluadas por este cuestionario. Si aún así sientes malestar, no dudes en buscar apoyo.</p>
    <?php endif; ?>

    <h3>Recomendaciones Generales:</h3>
    <ul>
        <li>Considera hablar con un terapeuta o consejero.</li>
        <li>Practica el autocuidado: duerme bien, come sano, haz ejercicio.</li>
        <li>Mantén contacto con amigos y familiares.</li>
        <li>Evita el aislamiento social.</li>
        <li>Si sientes que tu vida corre peligro o la de otros, busca ayuda de emergencia inmediatamente.</li>
    </ul>

    <p>Para más información o para encontrar un profesional, visita nuestra sección de <a href="#">Recursos</a> (crea esta página en el futuro).</p>

    <div class="alert-disclaimer">
        <strong>Descargo de responsabilidad importante:</strong> Este cuestionario y sus resultados son solo para fines informativos y de orientación general. **No son un sustituto del asesoramiento, diagnóstico o tratamiento médico o psicológico profesional.** Siempre busque el consejo de un profesional de la salud mental calificado para cualquier pregunta sobre una afección de salud mental. Si experimenta una emergencia, contacte a los servicios de emergencia de inmediato.
    </div>

    <p>
        <a href="index.php" class="btn">Volver al Inicio</a>
        <a href="historial_resultados.php" class="btn">Ver mi historial de resultados</a>
        <a href="buscar_especialistas.php?especialidad=<?php echo urlencode($area_sugerida); ?>" class="btn">Buscar Especialistas para <?php echo htmlspecialchars($area_sugerida); ?></a>
    </p>
</section>

<?php include 'includes/footer.php'; ?>