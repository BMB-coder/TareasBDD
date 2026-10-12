# Cuentas de prueba — paso 4.2

Base: `saludusm`

| Rol | Correo | Contraseña de prueba | RUT vinculado |
|---|---|---|---|
| Paciente | paciente.prueba@example.test | PacientePrueba!2026 | 00000000-0 |
| Médico | medicogenerico0@gmail.com | MedicoPrueba!2026 | 00000000-1 |
| Administrador | admin.prueba@example.test | AdminPrueba!2026 | No aplica |

El médico tiene 2 centros y 2 especialidades.

## Supuestos
- Una cuenta administradora de prueba en archivo privado JSON: `C:\xampp\saludusm_config\administrador.json`.
- Paciente: Email_Paciente y Contrasena_Hash en paciente. Médico: E-mail_Medico y Contrasena_Hash en medico.
- Los registros sin hash no son cuentas habilitadas.
- Contraseñas almacenadas con password_hash(PASSWORD_DEFAULT); se validan con password_verify.
- Política propuesta: 12 a 64 caracteres ASCII, con mayúscula, minúscula, número y símbolo. Pendiente implementar en formularios.
- El instalador comprueba que los correos no se repitan entre las tres fuentes.
- La unicidad global entre tablas/archivo deberá mantenerse en registro y edición de perfiles; los UNIQUE por tabla no bastan.
- Los datos de prueba (paso 4.1) se cargan con `BD/saludusm.sql`; ver la sección siguiente.

## Datos de prueba (paso 4.1)
Adaptados y ampliados desde la Tarea 1. Las fechas de las citas se calculan al importar el script, relativas al lunes de la semana actual, para que siempre existan citas pasadas, del día y futuras.

- 5 centros (RM, VA, BI), 8 especialidades, 6 médicos y 9 pacientes con las tres previsiones.
- Médico de prueba (00000000-1): 2 centros y 2 especialidades. Dr. Juan Pérez tiene el máximo de 3 especialidades.
- 40 citas en días hábiles (lunes a viernes) en todos los estados, más 4 citas el día de la importación si es día hábil (Nº 41–44, agenda del médico de prueba).
- 2 citas Atendidas sin atención (Nº 39 del médico de prueba y Nº 40 de otro médico), para probar el registro de atenciones.
- 2 citas pasadas en estado Reservada/Confirmada (Nº 21 y 28) para probar la cancelación automática de citas vencidas.
- Citas futuras del médico de prueba en sus 2 centros y 2 especialidades, para probar la protección al quitar una especialidad o un centro.
- 17 atenciones con 20 líneas de receta (una atención sin receta) y 14 diagnósticos CIE-10, 13 de ellos usados. Top 5 sin empates: J00 (7), I10 (5), M54 (4), E11 (3), F41 (2).
- Inasistencia distinta por centro: San Joaquín 30,8 %, Concepción 20 %, Valparaíso 11,1 %, Vitacura y Viña del Mar 0 %.
- Casos de prueba detallados en `CASOS_PRUEBA.md`.
- Solo las tres cuentas de la tabla (y Tomás Rivas, paciente2@gmail.com / Paciente2) tienen contraseña; el resto de médicos y pacientes no son cuentas habilitadas.

## Comprobación
Hashes de las tres cuentas verificados desde almacenamiento. Pendiente login, sesiones, permisos y edición de perfiles en paso 5.2 y siguientes. No marcar 4.2 completamente verificado hasta probar el acceso real.

## Instalación en otro equipo
Recrear estas cuentas mediante preparar_cuentas.php sobre una base sin credenciales y configurar la ruta privada. No publicar administrador.json en htdocs ni en el repositorio. El instalador no sobrescribe cuentas existentes.
