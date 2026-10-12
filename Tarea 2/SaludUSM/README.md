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

Avance 5.1:
- Conexión a la base de datos mediante PDO.
- Página de inicio.
- Listado de médicos obtenido desde la base.
- Navegación mediante menú y enlaces.
- Mensajes de error y presentación básica.

El acceso por roles se implementará en el paso 5.2.
