import pandas as pd
from sklearn.preprocessing import MultiLabelBinarizer
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
import joblib # Para guardar y cargar el modelo
import os # Para manejar archivos

# --- Configuración de Rutas (Simulamos una base de datos) ---
# En un entorno real, estos serían archivos en disco o una conexión a DB.
DATA_DIR = 'data'
if not os.path.exists(DATA_DIR):
    os.makedirs(DATA_DIR)

SPECIALISTS_DATA_PATH = os.path.join(DATA_DIR, 'specialists.csv')
PATIENT_SURVEY_DATA_PATH = os.path.join(DATA_DIR, 'patient_surveys.csv')
MODEL_PATH = os.path.join(DATA_DIR, 'specialist_recommender_model.joblib')
MLB_PATH = os.path.join(DATA_DIR, 'multilabel_binarizer.joblib')

# --- 1. Simulación de Datos Iniciales (Tu "Base de Datos") ---
# Estos datos representan lo que ya tienes de tus especialistas.
# Idealmente, estos datos vendrían de una base de datos persistente.
def load_initial_specialists_data():
    data_especialistas = {
        'id_especialista': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        'nombre': ['Dr. Ana Gómez', 'Lic. Juan Pérez', 'Dra. Laura Soto', 'Mtro. Carlos Ruíz', 'Lic. Sofía Díaz',
                   'Dr. Miguel Castro', 'Lic. Valeria Rico', 'Dra. Pilar Montes', 'Dr. David Lim', 'Lic. Carla Vega'],
        'especialidad_principal': ['Terapia Cognitivo-Conductual', 'Terapia Familiar Sistémica', 'Psicoanálisis',
                                   'Terapia de Pareja', 'Terapia Gestalt', 'Neuropsicología', 'Terapia Infantil',
                                   'Psiquiatría', 'Terapia Dialéctico Conductual', 'Psicología Positiva'],
        'experiencia_sordomudos': [True, True, False, True, False, True, True, False, True, False],
        'idioma_señas': ['Avanzado', 'Intermedio', 'Básico', 'Avanzado', 'Nulo', 'Avanzado', 'Intermedio', 'Básico', 'Avanzado', 'Nulo'],
        'trastornos_que_trata': [
            ['Depresión', 'Ansiedad', 'Trastorno Obsesivo-Compulsivo'],
            ['Problemas de Pareja', 'Conflictos Familiares', 'Adicciones'],
            ['Depresión', 'Ansiedad', 'Trastornos de la Personalidad'],
            ['Problemas de Pareja', 'Comunicación'],
            ['Estrés', 'Autoestima', 'Desarrollo Personal'],
            ['TDAH', 'Trastornos del Neurodesarrollo', 'Demencias'],
            ['Ansiedad Infantil', 'Conducta', 'Autismo'],
            ['Depresión', 'Esquizofrenia', 'Trastorno Bipolar', 'Adicciones'],
            ['Trastorno Límite de la Personalidad', 'Regulación Emocional', 'Depresión'],
            ['Estrés', 'Ansiedad', 'Felicidad']
        ]
    }
    df = pd.DataFrame(data_especialistas)
    df.to_csv(SPECIALISTS_DATA_PATH, index=False)
    print("Datos iniciales de especialistas guardados.")
    return df

# Función para cargar datos de especialistas
def load_specialists_data():
    if not os.path.exists(SPECIALISTS_DATA_PATH):
        return load_initial_specialists_data()
    df = pd.read_csv(SPECIALISTS_DATA_PATH)
    # Convertir 'trastornos_que_trata' de string a lista
    df['trastornos_que_trata'] = df['trastornos_que_trata'].apply(lambda x: eval(x) if pd.notna(x) else [])
    # Convertir booleanos de string a bool
    df['experiencia_sordomudos'] = df['experiencia_sordomudos'].astype(bool)
    return df

# Simulación de datos de encuesta de pacientes (inicialmente vacío)
def load_patient_survey_data():
    if not os.path.exists(PATIENT_SURVEY_DATA_PATH):
        # Crear un DataFrame vacío con las columnas esperadas
        df = pd.DataFrame(columns=['id_paciente', 'trastorno_reportado', 'es_sordomudo', 'requiere_señas_avanzado'])
        df.to_csv(PATIENT_SURVEY_DATA_PATH, index=False)
        print("Archivo de encuestas de pacientes creado.")
    return pd.read_csv(PATIENT_SURVEY_DATA_PATH)

# --- 2. Módulo de Entrenamiento del Modelo ---

def train_recommender_model():
    """
    Carga los datos de especialistas, preprocesa, entrena el modelo
    y lo guarda junto con el MultiLabelBinarizer.
    """
    print("--- Iniciando el entrenamiento del modelo de recomendación ---")
    df_especialistas = load_specialists_data()

    # Preprocesar datos para el entrenamiento
    mlb = MultiLabelBinarizer()
    trastornos_binarizados = mlb.fit_transform(df_especialistas['trastornos_que_trata'])

    df_especialistas['experiencia_sordomudos_num'] = df_especialistas['experiencia_sordomudos'].astype(int)
    df_especialistas['idioma_señas_avanzado'] = (df_especialistas['idioma_señas'] == 'Avanzado').astype(int)
    df_especialistas['idioma_señas_intermedio'] = (df_especialistas['idioma_señas'] == 'Intermedio').astype(int)

    X = df_especialistas[['experiencia_sordomudos_num', 'idioma_señas_avanzado', 'idioma_señas_intermedio']]
    Y = trastornos_binarizados

    # Entrenar el modelo
    model = RandomForestClassifier(n_estimators=100, random_state=42)
    model.fit(X, Y)

    # Guardar el modelo y el MLB para su uso posterior
    joblib.dump(model, MODEL_PATH)
    joblib.dump(mlb, MLB_PATH)

    print("Modelo de recomendación entrenado y guardado exitosamente.")
    print(f"Características de entrada para el modelo: {X.columns.tolist()}")
    print(f"Clases de salida (Trastornos): {mlb.classes_}")
    print("\n" + "="*80 + "\n")

# --- 3. Módulo de Predicción/Recomendación para un Nuevo Paciente ---

def recommend_for_new_patient(paciente_data, top_n=3, prob_minima=0.1):
    """
    Carga el modelo entrenado y el MLB, y predice especialistas para un nuevo paciente.
    """
    print("\n--- Generando recomendación para un nuevo paciente ---")

    # Verificar si el modelo y MLB existen
    if not os.path.exists(MODEL_PATH) or not os.path.exists(MLB_PATH):
        print("Error: Modelo o MultiLabelBinarizer no encontrados. Por favor, entrena el modelo primero.")
        return pd.DataFrame()

    # Cargar el modelo y el MLB
    model = joblib.load(MODEL_PATH)
    mlb = joblib.load(MLB_PATH)
    df_especialistas = load_specialists_data()

    trastorno_paciente = paciente_data['trastorno_reportado']
    requiere_sordomudos = paciente_data['es_sordomudo']
    requiere_señas_avanzado = paciente_data['requiere_señas_avanzado']

    # Preprocesar las características de los especialistas de la misma manera que en el entrenamiento
    df_especialistas['experiencia_sordomudos_num'] = df_especialistas['experiencia_sordomudos'].astype(int)
    df_especialistas['idioma_señas_avanzado'] = (df_especialistas['idioma_señas'] == 'Avanzado').astype(int)
    df_especialistas['idioma_señas_intermedio'] = (df_especialistas['idioma_señas'] == 'Intermedio').astype(int)

    X_predict = df_especialistas[['experiencia_sordomudos_num', 'idioma_señas_avanzado', 'idioma_señas_intermedio']]

    # Obtener las probabilidades de que cada especialista trate cada trastorno
    predicciones_prob = model.predict_proba(X_predict)
    prob_df = pd.DataFrame(
        [p[1] for p in predicciones_prob], # Probabilidad de la clase positiva (1)
        columns=mlb.classes_
    )
    prob_df['id_especialista'] = df_especialistas['id_especialista']

    df_resultado = df_especialistas.merge(prob_df, on='id_especialista', how='left')

    if trastorno_paciente not in mlb.classes_:
        print(f"Advertencia: El trastorno '{trastorno_paciente}' del paciente no está en la lista de trastornos conocidos por el modelo.")
        return pd.DataFrame()

    df_resultado['Puntuacion_Final'] = 0.0
    df_resultado['Puntuacion_Final'] = df_resultado[trastorno_paciente]

    df_filtrado_temp = df_resultado[df_resultado[trastorno_paciente] >= prob_minima].copy()

    if requiere_sordomudos:
        df_filtrado_temp['Puntuacion_Final'] += df_filtrado_temp['experiencia_sordomudos'].astype(int) * 0.2
        df_filtrado_temp = df_filtrado_temp[df_filtrado_temp['experiencia_sordomudos'] == True]

    if requiere_señas_avanzado:
        df_filtrado_temp['Puntuacion_Final'] += (df_filtrado_temp['idioma_señas'] == 'Avanzado').astype(int) * 0.3
        df_filtrado_temp = df_filtrado_temp[df_filtrado_temp['idioma_señas'] == 'Avanzado']

    if df_filtrado_temp.empty:
        print(f"No se encontraron especialistas que coincidan con '{trastorno_paciente}' y los requisitos adicionales.")
        return pd.DataFrame()

    df_recomendaciones = df_filtrado_temp.sort_values(by='Puntuacion_Final', ascending=False)

    return df_recomendaciones[['id_especialista', 'nombre', 'especialidad_principal',
                               'experiencia_sordomudos', 'idioma_señas', 'Puntuacion_Final']].head(top_n)

# --- 4. Simulación del Flujo de Uso (Cómo interactuarías con el sistema) ---

if __name__ == "__main__":
    print("--- SIMULACIÓN DEL SISTEMA DE RECOMENDACIÓN INTELIGENTE ---")

    # Paso 1: Cargar/Inicializar datos de especialistas (si no existen)
    # Esto simula que tu base de datos ya tiene especialistas.
    load_specialists_data()
    load_patient_survey_data() # Asegura que el archivo de encuestas existe

    # Paso 2: Entrenar el modelo inicial o reentrenarlo (se haría periódicamente o al añadir nuevos especialistas)
    # Esto simula que tu sistema se entrena por sí solo.
    print("\n--- Realizando el entrenamiento inicial del modelo (o re-entrenamiento) ---")
    train_recommender_model()

    # --- SIMULACIÓN DE NUEVAS ENCUESTAS DE PACIENTES Y PREDICCIONES ---

    print("\n--- SIMULANDO ENCUESTAS DE NUEVOS PACIENTES Y GENERANDO RECOMENDACIONES ---")

    # Nuevo paciente 1
    new_patient_1 = {
        'id_paciente': 1,
        'trastorno_reportado': 'Depresión',
        'es_sordomudo': True,
        'requiere_señas_avanzado': False
    }
    # En un sistema real, guardarías esta encuesta en tu base de datos de encuestas
    # df_surveys = load_patient_survey_data()
    # df_surveys = pd.concat([df_surveys, pd.DataFrame([new_patient_1])], ignore_index=True)
    # df_surveys.to_csv(PATIENT_SURVEY_DATA_PATH, index=False) # Guardar la nueva encuesta

    print(f"\nEncuesta de Paciente 1: {new_patient_1}")
    recomendaciones_1 = recommend_for_new_patient(new_patient_1)
    print("Recomendaciones para Paciente 1:")
    print(recomendaciones_1)
    print("-" * 30)

    # Nuevo paciente 2
    new_patient_2 = {
        'id_paciente': 2,
        'trastorno_reportado': 'TDAH',
        'es_sordomudo': True,
        'requiere_señas_avanzado': True
    }
    print(f"\nEncuesta de Paciente 2: {new_patient_2}")
    recomendaciones_2 = recommend_for_new_patient(new_patient_2)
    print("Recomendaciones para Paciente 2:")
    print(recomendaciones_2)
    print("-" * 30)

    # Nuevo paciente 3
    new_patient_3 = {
        'id_paciente': 3,
        'trastorno_reportado': 'Problemas de Pareja',
        'es_sordomudo': False,
        'requiere_señas_avanzado': False
    }
    print(f"\nEncuesta de Paciente 3: {new_patient_3}")
    recomendaciones_3 = recommend_for_new_patient(new_patient_3)
    print("Recomendaciones para Paciente 3:")
    print(recomendaciones_3)
    print("-" * 30)

    # Nuevo paciente 4 (trastorno no conocido por el modelo)
    new_patient_4 = {
        'id_paciente': 4,
        'trastorno_reportado': 'Fobia Social',
        'es_sordomudo': False,
        'requiere_señas_avanzado': False
    }
    print(f"\nEncuesta de Paciente 4: {new_patient_4}")
    recomendaciones_4 = recommend_for_new_patient(new_patient_4)
    print("Recomendaciones para Paciente 4:")
    print(recomendaciones_4)
    print("-" * 30)

    # --- Simulación de Actualización de Datos y Re-entrenamiento ---
    print("\n--- SIMULANDO UNA ACTUALIZACIÓN DE DATOS (ej. un nuevo especialista se une) ---")

    # Cargar datos existentes, añadir un nuevo especialista
    df_especialistas_actual = load_specialists_data()
    new_specialist_id = df_especialistas_actual['id_especialista'].max() + 1 if not df_especialistas_actual.empty else 1

    new_specialist = {
        'id_especialista': new_specialist_id,
        'nombre': 'Lic. Andrea Montes',
        'especialidad_principal': 'Terapia de Duelo',
        'experiencia_sordomudos': True,
        'idioma_señas': 'Avanzado',
        'trastornos_que_trata': [['Duelo', 'Estrés Postraumático', 'Depresión']]
    }
    # Convertir a DataFrame y usar pd.concat
    df_especialistas_actual = pd.concat([df_especialistas_actual, pd.DataFrame([new_specialist])], ignore_index=True)
    df_especialistas_actual.to_csv(SPECIALISTS_DATA_PATH, index=False)
    print(f"Nuevo especialista añadido: {new_specialist['nombre']}")

    # Re-entrenar el modelo con los nuevos datos
    print("\n--- Re-entrenando el modelo con el nuevo especialista ---")
    train_recommender_model()

    # Ahora, el modelo actualizado puede recomendar al nuevo especialista si es relevante
    print("\n--- Predicción después del re-entrenamiento ---")
    new_patient_5 = {
        'id_paciente': 5,
        'trastorno_reportado': 'Duelo', # Este trastorno es nuevo para el modelo antiguo
        'es_sordomudo': True,
        'requiere_señas_avanzado': True
    }
    print(f"\nEncuesta de Paciente 5: {new_patient_5}")
    recomendaciones_5 = recommend_for_new_patient(new_patient_5)
    print("Recomendaciones para Paciente 5 (después del re-entrenamiento):")
    print(recomendaciones_5)
    print("-" * 30)