# Cuentas de prueba — paso 4.2

Base: `saludusm`

| Rol | Correo | Contraseña de prueba | RUT vinculado |
|---|---|---|---|
| Paciente | paciente.prueba@example.test | PacientePrueba!2026 | 00000000-0 |
| Médico | medico0@gmail.com | MedicoPrueba!2026 | 00000000-1 |
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
- No se han creado citas, atenciones, diagnósticos ni recetas.

## Comprobación
Hashes de las tres cuentas verificados desde almacenamiento. Pendiente login, sesiones, permisos y edición de perfiles en paso 5.2 y siguientes. No marcar 4.2 completamente verificado hasta probar el acceso real.

## Instalación en otro equipo
Recrear estas cuentas mediante preparar_cuentas.php sobre una base sin credenciales y configurar la ruta privada. No publicar administrador.json en htdocs ni en el repositorio. El instalador no sobrescribe cuentas existentes.
