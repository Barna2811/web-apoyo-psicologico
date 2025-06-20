from flask import Flask, render_template, redirect, url_for, request, session, flash

# Variable para nombrar la aplicación Flask
app = Flask(__name__)
app.secret_key = 'clave_secreta_para_sesiones'  # Necesario para las sesiones

# Usuarios de ejemplo (en un proyecto real usarías una base de datos)
usuarios = {
    "usuario1": "contraseña1",
    "usuario2": "contraseña2",
    "admin": "admin123"
}

# Ruta raíz - redirige al login si no hay sesión
@app.route('/')
def index():
    if 'usuario' in session:
        return redirect(url_for('bienvenida'))
    return redirect(url_for('login'))

# Ruta para el login
@app.route('/login', methods=['GET', 'POST'])
def login():
    error = None
    if request.method == 'POST':
        usuario = request.form['usuario']
        contraseña = request.form['contraseña']
        
        if usuario in usuarios and usuarios[usuario] == contraseña:
            session['usuario'] = usuario
            return redirect(url_for('bienvenida'))
        else:
            error = 'Credenciales inválidas. Por favor, intente nuevamente.'
    
    return render_template('login.html', error=error)

# Ruta para el registro de nuevos usuarios
@app.route('/registro', methods=['GET', 'POST'])
def registro():
    error = None
    if request.method == 'POST':
        nuevo_usuario = request.form['nuevo_usuario']
        nueva_contraseña = request.form['nueva_contraseña']
        confirmar_contraseña = request.form['confirmar_contraseña']
        
        # Verificar que las contraseñas coincidan
        if nueva_contraseña != confirmar_contraseña:
            error = 'Las contraseñas no coinciden. Por favor, inténtelo nuevamente.'
        # Verificar que el nombre de usuario no exista ya
        elif nuevo_usuario in usuarios:
            error = 'El nombre de usuario ya existe. Por favor, elija otro.'
        # Verificar que el nombre de usuario y la contraseña no estén vacíos
        elif not nuevo_usuario or not nueva_contraseña:
            error = 'El nombre de usuario y la contraseña no pueden estar vacíos.'
        else:
            # Registrar al nuevo usuario
            usuarios[nuevo_usuario] = nueva_contraseña
            flash('¡Registro exitoso! Ahora puede iniciar sesión.', 'success')
            return redirect(url_for('login'))
    
    return render_template('registro.html', error=error)

# Ruta para la página de bienvenida
@app.route('/bienvenida')
def bienvenida():
    if 'usuario' not in session:
        return redirect(url_for('login'))
    return render_template('bienvenida.html', usuario=session['usuario'])

# Ruta para la página de clases
@app.route('/clases')
def clases():
    if 'usuario' not in session:
        return redirect(url_for('login'))
    return render_template('clases.html')

# Ruta para la página de somos
@app.route('/somos')
def somos():
    return render_template('somos.html')

# Ruta para la página de contactos
@app.route('/contactos')
def contactos():
    return render_template('contactos.html')

# Ruta para ver el perfil del usuario
@app.route('/perfil')
def perfil():
    if 'usuario' not in session:
        return redirect(url_for('login'))
    return render_template('perfil.html', usuario=session['usuario'])

# Ruta para cerrar sesión
@app.route('/logout')
def logout():
    session.pop('usuario', None)
    return redirect(url_for('login'))

# Definir una condición para ejecutar la aplicación y determinar el puerto
if __name__ == '__main__':
    app.run(debug=True, port=8005)