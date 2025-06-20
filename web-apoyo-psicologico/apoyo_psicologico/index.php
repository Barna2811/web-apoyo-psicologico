<?php include 'includes/header.php'; ?>

<section class="hero">
    <h1>Bienvenido a Tu Espacio de Apoyo Psicológico</h1>
    <p>Encuentra recursos y apoyo para tu bienestar mental.</p>
    <a href="#cuestionario" class="btn">Realizar Cuestionario</a>
</section>

<section id="cuestionario" class="cuestionario-section">
    <h2>Cuestionario de Orientación Psicológica</h2>
    <p>Este cuestionario es solo una herramienta de orientación y no sustituye el diagnóstico de un profesional. Responde honestamente para obtener una sugerencia sobre posibles áreas de apoyo.</p>

    <form action="procesar_cuestionario.php" method="POST">
        <?php
        $preguntas = [
            "¿Te sientes triste o deprimido la mayor parte del tiempo?",
            "¿Has perdido interés en actividades que antes disfrutabas?",
            "¿Tienes dificultades para conciliar el sueño o duermes demasiado?",
            "¿Experimentas cambios significativos en tu apetito o peso?",
            "¿Te sientes cansado o con poca energía la mayor parte del día?",
            "¿Te sientes inútil, culpable o con baja autoestima?",
            "¿Tienes dificultades para concentrarte o tomar decisiones?",
            "¿Experimentas ansiedad, nerviosismo o preocupación excesiva?",
            "¿Sientes ataques de pánico o temor intenso sin razón aparente?",
            "¿Evitas situaciones sociales o lugares públicos por miedo?",
            "¿Tienes pensamientos recurrentes e intrusivos que te causan malestar?",
            "¿Realizas acciones repetitivas o rituales para aliviar la ansiedad?",
            "¿Has experimentado un evento traumático recientemente?",
            "¿Tienes flashbacks o pesadillas relacionadas con un evento traumático?",
            "¿Te sientes desconectado de los demás o de la realidad?",
            "¿Tienes dificultades para controlar tu ira o irritabilidad?",
            "¿Experimentas cambios de humor extremos o impredecibles?",
            "¿Has tenido pensamientos de autolesión o suicidio?",
            "¿Consumes alcohol o drogas para afrontar tus problemas?",
            "¿Tienes problemas en tus relaciones personales o laborales debido a tu estado de ánimo?"
        ];

        for ($i = 0; $i < count($preguntas); $i++) {
            echo "<div class='pregunta-item'>";
            echo "<p>" . ($i + 1) . ". " . $preguntas[$i] . "</p>";
            echo "<label><input type='radio' name='q" . ($i + 1) . "' value='si' required> Sí</label>";
            echo "<label><input type='radio' name='q" . ($i + 1) . "' value='no'> No</label>";
            echo "</div>";
        }
        ?>
        <button type="submit" class="btn">Ver Resultados</button>
    </form>
</section>

<section class="call-to-action">
    <h2>¿Necesitas hablar con alguien?</h2>
    <p>No estás solo. Busca ayuda profesional si lo necesitas.</p>
    <a href="#" class="btn btn-secondary">Encuentra un Terapeuta</a>
</section>

<?php include 'includes/footer.php'; ?>