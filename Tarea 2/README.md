# TareasBDD
Repositorio de las tareas de Base de Datos (INF239) del segundo semstre del año 2026.

## Estudiantes a cargo
Estudiante N°1: Benjamin Medina, 202473644-2
Estudiante N°2: Martin Diaz, 202473520-9

# Uso de objetos SQL

- Para cumplir con la actualización de citas vencidas y el uso de Procedimientos Almacenados, se creó el SP ActualizarCitasVencidas(). Este se ejecuta automáticamente desde PHP cada vez que un usuario accede a la vista 'Mis Citas' o a la 'Agenda', garantizando que los datos mostrados en pantalla estén limpios y actualizados en tiempo real.

- Para cumplir con el requerimiento de evitar la eliminación de especialidades con citas futuras, se implementó el Trigger trg_proteger_especialidad_medico. Este actúa en el evento BEFORE DELETE sobre la tabla Medico_Especialidad, realizando una validación en tiempo real. Si detecta citas vigentes (estado 1 o 2) posteriores a la fecha actual, bloquea la eliminación y retorna un error SQLSTATE 45000 para que el backend en PHP lo capture y notifique al administrador.

- Para cumplir con el requerimiento de la Vista SQL (View), se implementó Vista_Detalle_Citas. Esta vista centraliza las relaciones de 7 tablas distintas, facilitando las consultas de lectura desde PHP para las secciones 'Mis Citas', 'Agenda' y 'Búsqueda Avanzada'. Cumple con abstraer la complejidad del modelo relacional, mejorando el tiempo de respuesta y simplificando los filtros mediante comandos WHERE directos.

- Para cumplir con el requerimiento de la Función SQL, se creó CalcularPorcentajeInasistencia(Codigo_Interno). Esta función escalar recibe el ID de un centro médico y retorna el porcentaje exacto de citas en estado 'No Asistió'. Se integró en el Panel de Gestión del Administrador, permitiendo encapsular la lógica matemática directamente en el servidor MySQL y aligerando el procesamiento del backend en PHP.