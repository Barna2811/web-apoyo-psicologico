-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-06-2025 a las 18:57:57
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `apoyo_psicologico`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

CREATE TABLE `alertas` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `especialista_id` int(11) DEFAULT NULL,
  `fecha_alerta` timestamp NOT NULL DEFAULT current_timestamp(),
  `nivel_urgencia` enum('critica','alta','media','baja') NOT NULL,
  `descripcion` text NOT NULL,
  `atendida` tinyint(1) DEFAULT 0,
  `fecha_atencion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `especialista_id` int(11) DEFAULT NULL,
  `fecha_cita` datetime NOT NULL,
  `diagnostico_id` int(11) DEFAULT NULL,
  `estado` enum('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
  `notas_paciente` text DEFAULT NULL,
  `notas_especialista` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `enlace_videollamada` varchar(255) DEFAULT NULL,
  `duracion_sesion_segundos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `paciente_id`, `especialista_id`, `fecha_cita`, `diagnostico_id`, `estado`, `notas_paciente`, `notas_especialista`, `fecha_creacion`, `enlace_videollamada`, `duracion_sesion_segundos`) VALUES
(1, 2, NULL, '2025-05-12 12:30:00', 1, 'pendiente', 'esta mal de la cabecita', NULL, '2025-06-11 15:37:35', NULL, NULL),
(2, 2, NULL, '2025-06-12 01:30:00', 2, 'pendiente', 'ayudaaaaa', NULL, '2025-06-11 15:50:40', NULL, NULL),
(3, 2, NULL, '2025-06-12 11:00:00', 2, 'pendiente', 'ayuda', NULL, '2025-06-11 16:55:27', NULL, NULL),
(4, 13, NULL, '2025-06-11 02:30:00', 2, 'pendiente', 'Me siento desmotivado en realizar mis actividades', NULL, '2025-06-11 18:49:33', NULL, NULL),
(5, 2, NULL, '2025-06-13 01:30:00', 3, 'pendiente', 'Quisiera una cita para tratamiento de ansiedad', NULL, '2025-06-12 15:46:10', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `diagnosticos`
--

CREATE TABLE `diagnosticos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `diagnosticos`
--

INSERT INTO `diagnosticos` (`id`, `nombre`) VALUES
(1, 'Ansiedad'),
(2, 'Depresión'),
(3, 'Estrés'),
(5, 'Trastorno Bipolar'),
(4, 'Trastorno Obsesivo Compulsivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disponibilidad_especialista`
--

CREATE TABLE `disponibilidad_especialista` (
  `id` int(11) NOT NULL,
  `especialista_id` int(11) NOT NULL,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `fecha_inicio_vigencia` date DEFAULT NULL,
  `fecha_fin_vigencia` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas`
--

CREATE TABLE `encuestas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `encuestas`
--

INSERT INTO `encuestas` (`id`, `titulo`, `descripcion`, `fecha_creacion`, `activa`) VALUES
(1, 'Encuesta Semanal de Estado Emocional', 'Responde estas preguntas para monitorear tu bienestar emocional esta semana.', '2025-06-11 15:40:47', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades_disponibles`
--

CREATE TABLE `especialidades_disponibles` (
  `id` int(11) NOT NULL,
  `nombre_especialidad` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especialidades_disponibles`
--

INSERT INTO `especialidades_disponibles` (`id`, `nombre_especialidad`) VALUES
(8, 'Adicciones'),
(2, 'Ansiedad'),
(1, 'Depresión'),
(3, 'Estrés Postraumático'),
(7, 'Terapia de Pareja'),
(6, 'Terapia Familiar'),
(5, 'Trastorno Bipolar'),
(4, 'Trastorno Obsesivo Compulsivo (TOC)');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `mensaje` text NOT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_encuesta`
--

CREATE TABLE `preguntas_encuesta` (
  `id` int(11) NOT NULL,
  `encuesta_id` int(11) NOT NULL,
  `pregunta_texto` text NOT NULL,
  `tipo_respuesta` enum('escala_1_5','si_no','texto_corto','texto_largo') NOT NULL,
  `orden` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_encuesta`
--

INSERT INTO `preguntas_encuesta` (`id`, `encuesta_id`, `pregunta_texto`, `tipo_respuesta`, `orden`) VALUES
(1, 1, '¿Con qué frecuencia te sentiste triste o deprimido esta semana?', 'escala_1_5', 1),
(2, 1, '¿Experimentaste niveles altos de ansiedad o preocupación?', 'escala_1_5', 2),
(3, 1, '¿Disfrutaste de tus actividades diarias habituales?', 'escala_1_5', 3),
(4, 1, '¿Dormiste bien la mayoría de las noches?', 'escala_1_5', 4),
(5, 1, '¿Sentiste energía y motivación para tus tareas?', 'escala_1_5', 5),
(6, 1, '¿Hubo algo en particular que te causara mucho estrés esta semana?', 'texto_largo', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_frecuentes`
--

CREATE TABLE `preguntas_frecuentes` (
  `id` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `respuesta` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_frecuentes`
--

INSERT INTO `preguntas_frecuentes` (`id`, `pregunta`, `respuesta`) VALUES
(1, '¿Cómo puedo saber si necesito ayuda psicológica?', 'Si sientes que tu estado de ánimo, pensamientos o comportamientos están afectando significativamente tu vida diaria, tus relaciones o tu bienestar general, es un buen momento para buscar ayuda profesional. No dudes en contactar a un especialista.'),
(2, '¿Es normal sentirse triste o ansioso?', 'Sí, es completamente normal experimentar tristeza o ansiedad en ciertas situaciones de la vida. Sin embargo, si estos sentimientos son persistentes, intensos y dificultan tu funcionamiento, es importante buscar apoyo.'),
(3, '¿Qué puedo esperar de la terapia?', 'La terapia es un espacio seguro y confidencial donde puedes explorar tus pensamientos y sentimientos con un profesional. Te ayudará a desarrollar estrategias para afrontar desafíos, mejorar tu bienestar y alcanzar tus metas personales.'),
(4, '¿Cuánto tiempo dura la terapia?', 'La duración de la terapia varía según la persona y sus necesidades. Puede ser desde unas pocas sesiones para un problema específico hasta un proceso más largo para abordar temas profundos. Tu terapeuta discutirá esto contigo.'),
(5, '¿Es costosa la terapia psicológica?', 'Los costos de la terapia varían ampliamente dependiendo del profesional, la duración de la sesión y si hay seguros involucrados. Muchos terapeutas ofrecen tarifas escalonadas o descuentos, y existen recursos públicos o gratuitos. ¡Invierte en tu bienestar!');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_log`
--

CREATE TABLE `reportes_log` (
  `id` int(11) NOT NULL,
  `especialista_id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_lectura` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reportes_log`
--

INSERT INTO `reportes_log` (`id`, `especialista_id`, `paciente_id`, `fecha_generacion`, `fecha_lectura`) VALUES
(1, 10, 2, '2025-06-11 17:25:23', '2025-06-11 18:53:14'),
(2, 10, 2, '2025-06-12 15:47:52', '2025-06-12 15:47:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_cuestionario`
--

CREATE TABLE `respuestas_cuestionario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `respuesta1` int(11) DEFAULT NULL,
  `respuesta2` int(11) DEFAULT NULL,
  `resultado_sugerido` varchar(255) DEFAULT NULL,
  `fecha_respuesta` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_pacientes_encuesta`
--

CREATE TABLE `respuestas_pacientes_encuesta` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta_valor` varchar(255) DEFAULT NULL,
  `puntuacion_emocional` int(11) DEFAULT NULL,
  `fecha_respuesta` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuestas_pacientes_encuesta`
--

INSERT INTO `respuestas_pacientes_encuesta` (`id`, `paciente_id`, `pregunta_id`, `respuesta_valor`, `puntuacion_emocional`, `fecha_respuesta`) VALUES
(1, 2, 1, '1', 1, '2025-06-11 15:43:04'),
(2, 2, 2, '1', 1, '2025-06-11 15:43:04'),
(3, 2, 3, '2', 2, '2025-06-11 15:43:04'),
(4, 2, 4, '2', 2, '2025-06-11 15:43:04'),
(5, 2, 5, '1', 1, '2025-06-11 15:43:04'),
(6, 2, 6, 'la presion social', NULL, '2025-06-11 15:43:04'),
(7, 1, 1, '5', 5, '2025-06-11 15:52:17'),
(8, 1, 2, '5', 5, '2025-06-11 15:52:17'),
(9, 1, 3, '5', 5, '2025-06-11 15:52:17'),
(10, 1, 4, '5', 5, '2025-06-11 15:52:17'),
(11, 1, 5, '5', 5, '2025-06-11 15:52:17'),
(12, 1, 6, 'ya no piedo con la vida\r\n', NULL, '2025-06-11 15:52:17'),
(13, 4, 1, '1', 1, '2025-06-11 15:53:20'),
(14, 4, 2, '1', 1, '2025-06-11 15:53:20'),
(15, 4, 3, '1', 1, '2025-06-11 15:53:20'),
(16, 4, 4, '1', 1, '2025-06-11 15:53:20'),
(17, 4, 5, '2', 2, '2025-06-11 15:53:20'),
(18, 4, 6, 'help', NULL, '2025-06-11 15:53:20'),
(19, 13, 1, '1', 1, '2025-06-11 18:47:13'),
(20, 13, 2, '2', 2, '2025-06-11 18:47:13'),
(21, 13, 3, '2', 2, '2025-06-11 18:47:13'),
(22, 13, 4, '1', 1, '2025-06-11 18:47:13'),
(23, 13, 5, '2', 2, '2025-06-11 18:47:13'),
(24, 13, 6, 'El trabajo en exceso', NULL, '2025-06-11 18:47:13'),
(25, 2, 1, '1', 1, '2025-06-12 15:43:00'),
(26, 2, 2, '2', 2, '2025-06-12 15:43:00'),
(27, 2, 3, '1', 1, '2025-06-12 15:43:00'),
(28, 2, 4, '2', 2, '2025-06-12 15:43:00'),
(29, 2, 5, '1', 1, '2025-06-12 15:43:00'),
(30, 2, 6, 'me siento algo cansado', NULL, '2025-06-12 15:43:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultados_cuestionario`
--

CREATE TABLE `resultados_cuestionario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `resultado` text NOT NULL,
  `fecha_cuestionario` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resultados_cuestionario`
--

INSERT INTO `resultados_cuestionario` (`id`, `usuario_id`, `resultado`, `fecha_cuestionario`) VALUES
(1, 4, 'Tu estado de bienestar general parece ser bueno. Mantén hábitos saludables.', '2025-06-05 13:06:00'),
(2, 2, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 4). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental.', '2025-06-05 13:09:22'),
(3, 2, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 4). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental.', '2025-06-05 13:09:43'),
(4, 5, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 7). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental. Además, has indicado algunas preocupaciones generales que podrían afectar tu bienestar. Es importante prestarles atención.', '2025-06-05 13:14:51'),
(5, 6, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 4). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental. Además, has indicado algunas preocupaciones generales que podrían afectar tu bienestar. Es importante prestarles atención.', '2025-06-05 13:23:02'),
(6, 2, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 5). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental. Además, has indicado algunas preocupaciones generales que podrían afectar tu bienestar. Es importante prestarles atención.', '2025-06-05 13:25:31'),
(7, 2, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 7). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental. Además, has indicado algunas preocupaciones generales que podrían afectar tu bienestar. Es importante prestarles atención.', '2025-06-05 13:28:44'),
(8, 2, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 5). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental. Además, has indicado algunas preocupaciones generales que podrían afectar tu bienestar. Es importante prestarles atención.', '2025-06-05 13:32:45'),
(9, 11, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 5). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental. Además, has indicado algunas preocupaciones generales que podrían afectar tu bienestar. Es importante prestarles atención.', '2025-06-05 13:51:05'),
(10, 11, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 5). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental. Además, has indicado algunas preocupaciones generales que podrían afectar tu bienestar. Es importante prestarles atención.', '2025-06-05 13:51:44'),
(11, 13, 'Basado en tus respuestas, una de las áreas con mayor puntuación es: **Depresión** (Síntomas detectados: 5). Si te identificas con esto, te recomendamos encarecidamente buscar la ayuda de un profesional de la salud mental. Además, has indicado algunas preocupaciones generales que podrían afectar tu bienestar. Es importante prestarles atención.', '2025-06-11 12:47:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo_usuario` varchar(50) NOT NULL DEFAULT 'paciente',
  `especialidad` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `email`, `contrasena`, `fecha_registro`, `tipo_usuario`, `especialidad`) VALUES
(1, 'maru', 'maru@gmail.com', '$2y$10$ESy1c08yBNByM4P7vfI8Oe3ULl6F6CSnQB6a9y3mp6aPf.Ztva5Qu', '2025-06-05 18:40:09', 'paciente', NULL),
(2, 'seb', 'seb@gmail.com', '$2y$10$sJH8t3i4QtFMEVMCCxevwOUnwVaEkhGFiA48h.8LxClpHa5vArFeK', '2025-06-05 18:57:16', 'paciente', NULL),
(3, 'mariano', 'mariano@gmail.com', '$2y$10$XXJu.yC6ca2hh8eI5xq3vuZGMb3JYx9dhfDWCBunGtqCfHMtKnQSq', '2025-06-05 18:58:10', 'especialista', NULL),
(4, 'maruri', 'maruri@gmail.com', '$2y$10$.zreMdblHUc8.6W3myFkKub0ommP/YDVWQBvFsQV.Yh7l.2c0SikG', '2025-06-05 19:03:06', 'paciente', NULL),
(5, 'holw', '1@gmail.com', '$2y$10$yClPbGjdNiAL4PtL0181X.yMTe.Hyu3bxNYYv1CPiv9ne2FsktZ1y', '2025-06-05 19:14:09', 'paciente', NULL),
(6, 'amaro', 'a@gmail.com', '$2y$10$R4i3oeMNen/NuA.mZlwVmOfnxIbaZ6E36ESjLI89IsUmaYFqhXM.6', '2025-06-05 19:22:19', 'paciente', ''),
(7, 'mistica', 'm@gmail.com', '$2y$10$b1ycSm5QIWTOitQEskt6neDrL85DSk38XyXceyyYnzsRz0xl0G5C6', '2025-06-05 19:23:38', 'especialista', 'Ansiedad'),
(8, 'bibiana', 'b@gmail.com', '$2y$10$aud7lsNQDaflLyERfx7g/OFOa1yjpYqb3.u3iZxccXf3eRHYI7Eqe', '2025-06-05 19:23:59', 'paciente', ''),
(9, 'bibi', 'bi@gmail.com', '$2y$10$eY8EEYvrk.sVDkE4LaQFc.EqkfimjcIzzPapwBKk0iYe2bypbWkLu', '2025-06-05 19:24:21', 'especialista', 'Depresión'),
(10, 'Maritza', 'marit@gmail.com', '$2y$10$CEF2cM.QMgx1Gq8HVn0m7OQVofWJVT4GKmIm2TVn4nDufC3xrXDJ.', '2025-06-05 19:24:49', 'especialista', 'Depresión'),
(11, 'miguel', 'miguel@gmail.com', '$2y$10$kdVGVbkQZ3PRpRyEaoBga./O/TzIB4BfplcGwCg7AvC7H0wfPsUMS', '2025-06-05 19:50:09', 'paciente', ''),
(12, 'lilo', 'lilo@gmail.com', '$2y$10$DsHYodaR6xi0403FhhkhRewmOyhUpyKC/9FiU2mXZyxl/qhBw2Mwy', '2025-06-07 21:39:00', 'especialista', 'Depresión'),
(13, 'Wilber', 'wilber@gmail.com', '$2y$10$M2uMRzU.oBR4MohbRwRX2eb/S1/shCLIdI8mhvd2c0EJFu2naOLFe', '2025-06-11 18:46:03', 'paciente', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `especialista_id` (`especialista_id`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `especialista_id` (`especialista_id`);

--
-- Indices de la tabla `diagnosticos`
--
ALTER TABLE `diagnosticos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `disponibilidad_especialista`
--
ALTER TABLE `disponibilidad_especialista`
  ADD PRIMARY KEY (`id`),
  ADD KEY `especialista_id` (`especialista_id`);

--
-- Indices de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `especialidades_disponibles`
--
ALTER TABLE `especialidades_disponibles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_especialidad` (`nombre_especialidad`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `preguntas_encuesta`
--
ALTER TABLE `preguntas_encuesta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `encuesta_id` (`encuesta_id`);

--
-- Indices de la tabla `preguntas_frecuentes`
--
ALTER TABLE `preguntas_frecuentes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reportes_log`
--
ALTER TABLE `reportes_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `especialista_id` (`especialista_id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- Indices de la tabla `respuestas_cuestionario`
--
ALTER TABLE `respuestas_cuestionario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `respuestas_pacientes_encuesta`
--
ALTER TABLE `respuestas_pacientes_encuesta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `pregunta_id` (`pregunta_id`);

--
-- Indices de la tabla `resultados_cuestionario`
--
ALTER TABLE `resultados_cuestionario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `diagnosticos`
--
ALTER TABLE `diagnosticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `disponibilidad_especialista`
--
ALTER TABLE `disponibilidad_especialista`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `especialidades_disponibles`
--
ALTER TABLE `especialidades_disponibles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas_encuesta`
--
ALTER TABLE `preguntas_encuesta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `preguntas_frecuentes`
--
ALTER TABLE `preguntas_frecuentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `reportes_log`
--
ALTER TABLE `reportes_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `respuestas_cuestionario`
--
ALTER TABLE `respuestas_cuestionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuestas_pacientes_encuesta`
--
ALTER TABLE `respuestas_pacientes_encuesta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `resultados_cuestionario`
--
ALTER TABLE `resultados_cuestionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `alertas_ibfk_2` FOREIGN KEY (`especialista_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`especialista_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `disponibilidad_especialista`
--
ALTER TABLE `disponibilidad_especialista`
  ADD CONSTRAINT `disponibilidad_especialista_ibfk_1` FOREIGN KEY (`especialista_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `preguntas_encuesta`
--
ALTER TABLE `preguntas_encuesta`
  ADD CONSTRAINT `preguntas_encuesta_ibfk_1` FOREIGN KEY (`encuesta_id`) REFERENCES `encuestas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes_log`
--
ALTER TABLE `reportes_log`
  ADD CONSTRAINT `reportes_log_ibfk_1` FOREIGN KEY (`especialista_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `reportes_log_ibfk_2` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `respuestas_cuestionario`
--
ALTER TABLE `respuestas_cuestionario`
  ADD CONSTRAINT `respuestas_cuestionario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `respuestas_pacientes_encuesta`
--
ALTER TABLE `respuestas_pacientes_encuesta`
  ADD CONSTRAINT `respuestas_pacientes_encuesta_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `respuestas_pacientes_encuesta_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas_encuesta` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resultados_cuestionario`
--
ALTER TABLE `resultados_cuestionario`
  ADD CONSTRAINT `resultados_cuestionario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
