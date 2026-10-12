SaludUSM — Aplicación PHP

Ejecución local:
1. Copiar la carpeta SaludUSM dentro de htdocs de XAMPP.
2. Copiar la carpeta SaludUSM/saludusm_config a la carpeta de XAMPP
   (en Windows debe quedar C:\xampp\saludusm_config\administrador.json).
3. Iniciar Apache y MySQL.
4. Importar el script SQL desde phpMyAdmin.
5. Configurar PHP/config/conexion.php con el nombre de la base,
   puerto, usuario y contraseña del entorno local.
6. Abrir http://localhost/SaludUSM/PHP/index.php

Cuenta del administrador:
- El administrador no está en la base de datos: sus datos y el hash de su
  contraseña están en saludusm_config/administrador.json.
- La ubicación recomendada es fuera de htdocs (paso 2), para que el archivo
  nunca quede expuesto en el servidor web.
- Si no se realiza el paso 2, la aplicación usa la copia incluida en
  SaludUSM/saludusm_config, que está bloqueada para el navegador con .htaccess.
- Si no se encuentra ninguna de las dos copias, solo falla el inicio de sesión
  del administrador; pacientes y médicos pueden seguir usando la aplicación.

Gestión del administrador (centros médicos y médicos):
- Se accede desde el menú superior del administrador: "Centros médicos" y "Médicos".
- Centros: el código lo asigna el sistema (autoincremental) y no se edita.
  La región se elige de una lista con los códigos de las 16 regiones de Chile
  (RM, VA, BI, etc.).
- Un centro con citas registradas (incluidas las pasadas) no se puede eliminar,
  para conservar el historial clínico; sí se pueden editar sus datos.
- Un centro tampoco se puede eliminar si es el único centro de algún médico.
  Al eliminar un centro sin citas se quitan sus asignaciones a médicos.
- Médicos: el administrador los crea con RUT, nombre, correo, contraseña
  inicial, entre 1 y 3 especialidades y al menos 1 centro. El médico inicia
  sesión con ese correo y contraseña. La contraseña sigue la misma política que
  el registro de pacientes. El RUT no se edita.
- El correo de un médico no puede estar en uso por otra cuenta (paciente,
  médico o administrador).
- Un médico con citas registradas (incluidas las pasadas) no se puede eliminar.
- Quitar una especialidad o un centro a un médico:
  * Si tiene citas futuras (Reservadas o Confirmadas, desde hoy) con esa
    especialidad o en ese centro, el sistema lo impide y muestra el motivo.
    Lo controlan los triggers ProtegerEspecialidadMedico y ProtegerCentroMedico
    (BEFORE DELETE sobre medico_especialidad y centro_medico_medico).
  * Si solo tiene citas pasadas con esa asignación, también se impide, porque
    cada cita referencia la asignación médico-especialidad y médico-centro
    (claves foráneas) y forma parte del historial.
  * Si no tiene citas con esa asignación, se quita normalmente.
- Los cambios de un médico se guardan en una transacción: si se rechaza una
  asignación, no se aplica ningún cambio.

Avance 5.1:
- Conexión a la base de datos mediante PDO.
- Página de inicio.
- Listado de médicos obtenido desde la base.
- Navegación mediante menú y enlaces.
- Mensajes de error y presentación básica.

El acceso por roles se implementará en el paso 5.2.
