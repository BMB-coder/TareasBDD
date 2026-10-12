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

Citas (agendar, mis citas y agenda médica):
- Horario de atención: lunes a viernes, de 09:00 a 13:00 y de 14:00 a 18:00,
  en bloques de 30 minutos (09:00, 09:30, ..., 12:30, 14:00, ..., 17:30).
  Es el mismo horario para todos los médicos y centros.
- Se puede reservar desde hoy hasta 90 días hacia adelante. En el día actual
  solo se ofrecen los bloques que aún no pasan.
- Agendar hora (paciente): se elige especialidad, luego centro (solo los que
  tienen médicos de esa especialidad), médico (solo los que tienen la
  especialidad y atienden en ese centro) y fecha. Se muestran solo los bloques
  libres para el médico y para el paciente. La cita se crea como Reservada.
- Reglas al reservar o reprogramar (se validan en PHP y en la base de datos):
  * El médico no puede tener dos citas vigentes en el mismo bloque y el
    paciente tampoco. Lo controla el procedimiento ValidarBloqueCita, llamado
    por los triggers ValidarAgendaInsert y ValidarAgendaUpdate.
  * Una cita Cancelada libera su bloque. Por eso cita no tiene claves únicas
    por médico/paciente + fecha + hora (esas claves también contaban las
    canceladas); se reemplazaron por los triggers anteriores.
  * El médico debe tener la especialidad y atender en el centro elegidos.
  * La fecha debe ser un día hábil dentro del rango y la hora un bloque futuro.
- Mis citas (paciente): usa la vista comprobante_reserva_hora (Vista 1).
  Próximas: citas Reservadas o Confirmadas desde hoy. Históricas: todas las
  demás (Atendidas, No Asistió, Canceladas y pasadas), de la más reciente a la
  más antigua.
- Reprogramar: solo cambia la fecha y la hora; el médico, el centro y la
  especialidad se mantienen (para cambiarlos se cancela y se reserva otra).
  La cita reprogramada vuelve a estado Reservada, porque la confirmación
  correspondía al horario anterior.
- Reprogramar y cancelar solo se permite en citas propias, Reservadas o
  Confirmadas, cuya fecha y hora aún no pasan. Cancelar usa el procedimiento
  almacenado NULLCita.
- Agenda (médico): muestra las citas de un día (por defecto hoy) ordenadas por
  hora, con paciente, previsión, centro, especialidad y estado. Permite ver
  otros días.
- Cambios de estado del médico (solo en sus citas):
  * Confirmada: solo antes de la hora de la cita, desde Reservada.
  * Atendida o No Asistió: desde la hora de la cita, desde Reservada o
    Confirmada. Se puede corregir entre Atendida y No Asistió mientras la
    cita no tenga una atención registrada.
  * El médico no cancela citas, y una cita Cancelada no cambia de estado.
- Citas vencidas: las citas Reservadas o Confirmadas con fecha anterior a hoy
  pasan a Cancelada mediante el procedimiento almacenado
  CancelarCitasVencidas (consulta 10 de la Tarea 1). Se ejecuta cada vez que
  se abre Mis citas, Agendar hora o la Agenda del médico. Se compara solo la
  fecha (no la hora), para que el médico pueda marcar durante el mismo día
  las citas de hoy como Atendida o No Asistió.
- Las consultas sobre la vista usan preparación emulada de PDO, porque
  MariaDB de XAMPP responde el error 1615 al preparar en el servidor
  consultas sobre vistas. Los parámetros siguen escapados por PDO.

Avance 5.1:
- Conexión a la base de datos mediante PDO.
- Página de inicio.
- Listado de médicos obtenido desde la base.
- Navegación mediante menú y enlaces.
- Mensajes de error y presentación básica.

El acceso por roles se implementará en el paso 5.2.
