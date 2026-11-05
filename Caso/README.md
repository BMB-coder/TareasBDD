# Bases de datos - Caso (Tarea 3)
Repositorio del caso asignado para la asignatura de Bases de datos (INF-239) del segundo semestre del año 2026.

## Estudiantes a cargo
- Estudiante N°1: Benjamin Medina, 202473644-2
- Estudiante N°2: Martin Diaz, 202473520-9

## Versiones utilizadas en la realización de la tarea
- Docker Desktop: 29.9.2
- Docker Compose: v5.6.1
- Python: 3.13.9
- PyMongo: 4.19.0
- PyMySQL: 1.2.3
- JupyterLab: 4.6.5
- MongoDB: [versión]
## Instrucciones de ejecución
## Instrucciones de ejecución

### 4.1. Diseño del modelo documental

El diseño del modelo documental se encuentra en el informe PDF incluido en la carpeta `PDF`.

Esta sección describe las colecciones de MongoDB, sus campos y la representación de las relaciones mediante referencias y documentos embebidos. También explica las decisiones utilizadas para transformar el modelo relacional de MySQL al modelo documental.

Para revisar esta sección, basta con abrir el informe en un lector de PDF o en un navegador. No es necesario ejecutar comandos ni iniciar las bases de datos.

### 4.2. Instalación, población y migración de MySQL a MongoDB

Las siguientes instrucciones permiten preparar el entorno en Windows y ejecutar el notebook desde Visual Studio Code.

La migración utiliza Python para consultar MySQL, transformar los registros e insertar los documentos en MongoDB. El archivo SQL debe importarse previamente en MySQL: Python se conecta al servidor, no directamente al archivo.

#### 1. Preparar los archivos de la entrega

Descargar y extraer la entrega en una carpeta del computador, conservando la organización de sus archivos.

Para ejecutar esta sección se necesitan:

| Archivo | Ubicación |
|---|---|
| `pymongo-202473644-2-202473520-9.ipynb` | Carpeta principal |
| `deploy-202473644-2-202473520-9.yml` | Carpeta principal |
| `salud_usm.sql` | Subcarpeta `BD` |

En estas instrucciones se denomina `Caso` a la carpeta principal del proyecto.

No es suficiente descargar únicamente el notebook: también se necesitan el respaldo SQL y el archivo de despliegue.

#### 2. Instalar las herramientas

Descargar e instalar:

- [Visual Studio Code](https://code.visualstudio.com/download).
- [Python 3.13 para Windows](https://www.python.org/downloads/windows/).
- [Docker Desktop para Windows](https://docs.docker.com/desktop/setup/install/windows-install/).

Durante la instalación de Python, habilitar el lanzador `py` y la opción para agregar Python al `PATH`, si aparecen.

Docker Desktop debe utilizar contenedores Linux. Para utilizar el backend WSL 2, seguir los requisitos indicados por su instalador.

Si es necesario instalar WSL:

1. Abrir el menú Inicio de Windows.
2. Buscar PowerShell.
3. Seleccionar **Ejecutar como administrador**.
4. Ejecutar:

```powershell
wsl --install
```

Reiniciar Windows si se solicita y completar la configuración de WSL.

Después, abrir Docker Desktop y esperar hasta que su motor esté en ejecución. El equipo debe cumplir los requisitos de Windows, WSL y virtualización indicados en la documentación de Docker.

No es necesario instalar MySQL ni MongoDB directamente en Windows: Docker descargará sus imágenes.

#### 3. Abrir el proyecto en VS Code

1. Abrir Visual Studio Code.
2. Seleccionar **Archivo → Abrir carpeta**.
3. Elegir la carpeta `Caso`.
4. Abrir **Extensiones** mediante `Ctrl + Shift + X`.
5. Instalar las extensiones **Python** y **Jupyter**, ambas publicadas por Microsoft.
6. Seleccionar **Terminal → Nueva terminal**.
7. Utilizar una terminal de tipo **PowerShell**.

Comprobar que la terminal se encuentra en la carpeta principal del proyecto:

```powershell
Get-Location
```

La ruta mostrada debe terminar en la carpeta `Caso`.

Comprobar que los archivos estén disponibles:

```powershell
Get-ChildItem
Get-ChildItem ".\BD"
```

> Salvo que se indique otra ubicación, todos los comandos PowerShell de los pasos siguientes se ejecutan en la terminal integrada de VS Code, ubicada en `Caso`.

#### 4. Verificar las instalaciones e instalar las bibliotecas

Ejecutar en PowerShell:

```powershell
py -3.13 --version
docker --version
docker compose version
```

Cada comando debe mostrar la versión de la herramienta correspondiente.

Si algún comando no se reconoce después de instalar las herramientas, cerrar completamente VS Code y abrirlo nuevamente.

Instalar las bibliotecas de Python:

```powershell
py -3.13 -m pip install ipykernel pymysql pymongo cryptography
```

Registrar el kernel que utilizará el notebook:

```powershell
py -3.13 -m ipykernel install --user --name salud-usm --display-name "Python SaludUSM"
```

Este procedimiento utiliza Python instalado en Windows y no crea una carpeta `.venv` dentro del proyecto.

Para este método de ejecución no es necesario iniciar JupyterLab en el navegador.

#### 5. Crear el servidor MySQL

Este paso corresponde a la primera instalación, cuando todavía no existe el contenedor `mysql-tarea3`.

En el siguiente comando, reemplazar `CAMBIA_ESTA_CLAVE` por una contraseña elegida para la práctica. Conservar las comillas y recordar la contraseña, porque se solicitará durante la importación y la conexión desde Python.

Ejecutar en PowerShell:

```powershell
docker run -d --name mysql-tarea3 -p 127.0.0.1:3307:3306 -e "MYSQL_ROOT_PASSWORD=CAMBIA_ESTA_CLAVE" -e "MYSQL_ROOT_HOST=%" -v salud_usm_mysql_data:/var/lib/mysql mysql:8.4
```

Este comando:

- Crea un contenedor llamado `mysql-tarea3`.
- Utiliza MySQL 8.4.
- Permite conectarse desde Windows mediante `127.0.0.1:3307`.
- Conserva los datos en el volumen Docker `salud_usm_mysql_data`.

Consultar el inicio del servidor:

```powershell
docker logs mysql-tarea3 --tail 40
```

Esperar hasta que MySQL esté listo para aceptar conexiones. La primera inicialización puede demorar.

Si el contenedor ya existe y está detenido, iniciarlo mediante:

```powershell
docker start mysql-tarea3
```

No repetir `docker run` para iniciar un contenedor existente.

> El puerto `3307` debe estar disponible. Si se utiliza otro puerto, también debe modificarse el parámetro `port` de la conexión MySQL en el notebook.

#### 6. Importar el respaldo SQL

Desde PowerShell, ubicado en `Caso`, copiar el archivo al contenedor:

```powershell
docker cp ".\BD\salud_usm.sql" mysql-tarea3:/tmp/salud_usm.sql
```

Abrir el cliente MySQL:

```powershell
docker exec -it mysql-tarea3 mysql -u root -p
```

Cuando aparezca `Enter password:`, ingresar la contraseña elegida al crear el contenedor y presionar Enter.

No se mostrarán caracteres mientras se escribe la contraseña.

Cuando aparezca el indicador `mysql>`, ejecutar:

```sql
SOURCE /tmp/salud_usm.sql;
```

El respaldo incluye las instrucciones `CREATE DATABASE` y `USE` para crear y seleccionar `salud_usm`.

Esperar hasta que finalice la importación y vuelva a aparecer `mysql>`. Revisar que no se hayan mostrado errores.

En ese mismo indicador MySQL, ejecutar:

```sql
USE salud_usm;

SELECT 'paciente' AS tabla, COUNT(*) AS total FROM paciente
UNION ALL SELECT 'medico', COUNT(*) FROM medico
UNION ALL SELECT 'cita', COUNT(*) FROM cita
UNION ALL SELECT 'atencion', COUNT(*) FROM atencion
UNION ALL SELECT 'receta', COUNT(*) FROM receta;
```

Los resultados esperados son:

| Tabla | Registros |
|---|---:|
| paciente | 49 |
| medico | 22 |
| cita | 205 |
| atencion | 60 |
| receta | 80 |

Salir del cliente MySQL:

```sql
exit;
```

La terminal volverá a PowerShell.

> La importación se realiza una sola vez durante la instalación inicial. No volver a importar el respaldo sobre una base en uso para retomar una sesión.

#### 7. Iniciar los tres nodos de MongoDB

Ejecutar en PowerShell, ubicado en `Caso`:

```powershell
docker compose -f ".\deploy-202473644-2-202473520-9.yml" up -d
```

Comprobar los servicios:

```powershell
docker compose -f ".\deploy-202473644-2-202473520-9.yml" ps
```

Deben aparecer los servicios `mongo1`, `mongo2` y `mongo3` en ejecución.

Los tres nodos formarán el conjunto de réplicas `rsSaludUSM`. Su inicialización se realiza mediante las celdas correspondientes del notebook.

Los puertos utilizados son `30001`, `30002` y `30003`, por lo que deben estar disponibles.

#### 8. Configurar los nombres de los nodos en Windows

El notebook debe poder localizar los nombres anunciados por los miembros del conjunto de réplicas.

Para configurarlos:

1. Buscar **Bloc de notas** en el menú Inicio.
2. Hacer clic derecho y seleccionar **Ejecutar como administrador**.
3. Seleccionar **Archivo → Abrir**.
4. Abrir el archivo:

```text
C:\Windows\System32\drivers\etc\hosts
```

Si no aparece, cambiar el filtro del cuadro de apertura a **Todos los archivos**.

Agregar al final la siguiente línea, sin borrar el contenido existente:

```text
127.0.0.1 mongo1 mongo2 mongo3
```

Guardar el archivo con su nombre original, `hosts`, sin agregar la extensión `.txt`.

Si la entrada ya existe correctamente, no duplicarla.

#### 9. Abrir el notebook y seleccionar el kernel

Desde el explorador de archivos de VS Code, abrir:

```text
pymongo-202473644-2-202473520-9.ipynb
```

En la esquina superior derecha del notebook:

1. Presionar **Seleccionar kernel**.
2. Si corresponde, elegir **Seleccionar otro kernel → Kernels de Jupyter**.
3. Seleccionar **Python SaludUSM**.

Si el kernel no aparece, reiniciar VS Code y comprobar que el comando de registro del paso 4 terminó correctamente.

El kernel es el proceso de Python que ejecuta las celdas y mantiene sus variables en memoria.

#### 10. Ejecutar la preparación y la migración

Ejecutar las celdas necesarias una por una, siguiendo el orden del notebook, mediante el botón de reproducción de cada celda o `Shift + Enter`.

- Las celdas **Markdown** contienen explicaciones.
- Las celdas **Code** contienen instrucciones Python.

El procedimiento de ejecución es:

1. Importar las bibliotecas.
2. Comprobar la conexión con los nodos de MongoDB.
3. Inicializar el conjunto de réplicas si todavía no está inicializado.
4. Comprobar que exista un nodo `PRIMARY` y dos nodos `SECONDARY`.
5. Conectar Python con MySQL.
6. Leer las tablas MySQL y mostrar sus conteos.
7. Agrupar las relaciones y transformar los registros al modelo documental.
8. Validar los documentos preparados.
9. Insertar las nueve colecciones principales.
10. Generar e insertar los documentos de `Comprobantes` y `Fichas`.
11. Ejecutar las comprobaciones de conteos, contenido, mínimos de población y disponibilidad de datos.

Si los nodos todavía están iniciándose, esperar unos segundos y volver a ejecutar únicamente la celda de comprobación de roles.

La conexión MySQL del notebook debe utilizar:

| Parámetro | Valor |
|---|---|
| `host` | `127.0.0.1` |
| `port` | `3307` |
| `user` | `root` |
| `database` | `salud_usm` |
| Contraseña | La elegida al crear el contenedor |

Cuando una celda solicite la contraseña, escribirla en el campo de entrada del notebook y presionar Enter. No incorporarla como texto visible en el código.

Si una celda muestra un error, resolverlo antes de ejecutar las siguientes, ya que pueden depender de sus resultados.

#### 11. Consideraciones sobre las celdas de ajuste y las ejecuciones repetidas

El respaldo SQL actualizado ya contiene las citas `41`, `43`, `45` y `50` con estado **No Asistió**.

Por lo tanto, en una instalación nueva desde este respaldo no es necesario ejecutar las celdas auxiliares utilizadas durante el desarrollo para:

- Inspeccionar los disparadores de MySQL.
- Buscar citas candidatas para ajustar la inasistencia.
- Modificar el estado de esas cuatro citas.
- Corregir sus comprobantes después de la modificación.

Sí se deben ejecutar las comprobaciones finales de consistencia y disponibilidad de datos.

Las celdas de inserción están destinadas a una base MongoDB inicialmente vacía. Si informan que las colecciones ya contienen documentos, no repetir las inserciones ni borrar las colecciones para continuar una sesión.

Después de reiniciar el kernel, las variables de Python se pierden, pero los datos almacenados en Docker permanecen. Para continuar, ejecutar nuevamente las conexiones y las celdas de preparación de variables que necesiten las consultas posteriores.

> No utilizar **Ejecutar todo** sin revisar antes las celdas de inserción y las operaciones que modifican datos.

#### 12. Resultados esperados y evidencia de la sección 4.2

La población inicial de MongoDB debe presentar los siguientes resultados:

| Colección o dato | Cantidad esperada |
|---|---:|
| Centros | 5 |
| Especialidades | 8 |
| Previsiones | 3 |
| Estados | 5 |
| Diagnosticos | 14 |
| Pacientes | 49 |
| Medicos | 22 |
| Citas | 205 |
| Atenciones | 60 |
| Comprobantes | 205 |
| Fichas | 60 |
| Recetas embebidas en Atenciones | 80 |
| Relaciones atención–diagnóstico | 72 |

También se deben verificar:

- Tres regiones distintas entre los centros.
- Ninguna atención sin diagnóstico.
- Coincidencia entre los conteos equivalentes de MySQL y MongoDB.
- Conservación de los datos y las relaciones durante la transformación.
- Disponibilidad de datos para los cinco casos de consulta del punto 4.2.3.
- Existencia de citas vencidas en estado Reservada o Confirmada.

El centro San Joaquín tiene ocho inasistencias entre 48 citas, equivalentes al `16,67 %`.

Las comprobaciones de citas vencidas dependen de la fecha de ejecución. Las salidas guardadas documentan los resultados obtenidos al realizar la prueba.

La evidencia de la sección 4.2 se conserva en las salidas del notebook. Guardar el archivo mediante `Ctrl + S`, manteniendo visibles los conteos, las comparaciones y los resultados de las verificaciones.

Estas cantidades corresponden al estado inicial posterior a la migración. Las operaciones de las secciones posteriores pueden modificar los datos.

#### 13. Retomar el trabajo después de reiniciar el computador

Abrir Docker Desktop y esperar hasta que su motor esté en ejecución.

Después, abrir la carpeta `Caso` en VS Code y ejecutar en su terminal PowerShell:

```powershell
docker start mysql-tarea3
docker compose -f ".\deploy-202473644-2-202473520-9.yml" up -d
```

Abrir el notebook, seleccionar el kernel **Python SaludUSM** y ejecutar las celdas necesarias de conexión y preparación de variables.

No es necesario volver a importar el SQL ni repetir la migración si los datos ya están almacenados.

#### 14. Problemas frecuentes

| Problema | Acción |
|---|---|
| PowerShell no reconoce `docker` | Comprobar la instalación de Docker Desktop y reiniciar VS Code. |
| No se puede conectar con el motor de Docker | Abrir Docker Desktop y esperar a que termine de iniciar. |
| No se encuentra `BD/salud_usm.sql` | Comprobar que la terminal esté ubicada en `Caso` y que el archivo exista en `BD`. |
| No aparece el kernel | Repetir el registro del paso 4 y reiniciar VS Code. |
| Aparece `ModuleNotFoundError` | Comprobar el kernel seleccionado y ejecutar la instalación de bibliotecas del paso 4. |
| Aparece `NameError` para una variable | Ejecutar primero la celda que define esa variable. |
| MySQL muestra `Access denied` | Revisar la contraseña ingresada y los parámetros de conexión. |
| MongoDB muestra `getaddrinfo failed` | Revisar la entrada `mongo1 mongo2 mongo3` en el archivo `hosts`. |
| MongoDB no encuentra un `PRIMARY` | Comprobar que los tres servicios estén iniciados y que el conjunto de réplicas esté inicializado. |
| Las colecciones ya contienen documentos | Evitar repetir la inserción; continuar con las consultas y verificaciones del estado existente. |

### 4.3. Consultas

Con las conexiones de la sección 4.2 activas, ejecutar en orden las celdas de la sección 4.3 del notebook. Cada consulta muestra su resultado, su consulta SQL equivalente y la comparación con MySQL, por lo que el contenedor `mysql-tarea3` debe estar iniciado.

**Criterio de desempate de la consulta (d):** los medicamentos con la misma cantidad de recetas se ordenan alfabéticamente de forma ascendente.

### 4.4. Replicación

Ejecutar en orden las celdas de la sección 4.4 después de las de la sección 4.3. Estas celdas modifican los datos, detienen el contenedor del nodo primario y lo vuelven a iniciar mediante `docker compose`, por lo que el notebook debe ejecutarse desde la carpeta `Caso`.

Esta sección se ejecuta una sola vez sobre una base recién migrada: al repetirla, la reserva se rechaza porque el bloque ya está ocupado. Para repetir toda la demostración desde cero, se deben eliminar los datos de MongoDB y ejecutar nuevamente el notebook desde el inicio, reiniciando antes el kernel:

```powershell
docker compose -f ".\deploy-202473644-2-202473520-9.yml" down -v
docker compose -f ".\deploy-202473644-2-202473520-9.yml" up -d
```

Después de esta sección, MongoDB queda con 207 citas, 207 comprobantes y 79 recetas embebidas.

## Supuestos
- Se asume que se puede utilizar el mismo archivo .sql exportado desde phpmyadmin para ser utilizado en MySQL
- Las consultas de la sección 4.3 usan `Citas` y `Atenciones` como fuente de verdad, ya que `Comprobantes` y `Fichas` son copias para presentar las vistas de usuario.
- Las operaciones de la sección 4.4 actualizan en una misma transacción la colección principal y su copia en `Comprobantes` o `Fichas`.
- Una cita está vencida si su fecha es anterior al día actual en Chile. Las citas del día actual siguen vigentes, igual que en el procedimiento `CancelarCitasVencidas` de MySQL.
- La migración deja exactamente 80 recetas, el mínimo solicitado. La eliminación pedida en la sección 4.4 deja 79 en el estado final.
- El identificador de una cita nueva es el mayor identificador existente más uno, reemplazando el `AUTO_INCREMENT` de MySQL.
- La consulta (c) aplica el umbral del 15 % al porcentaje sin redondear.
- El conjunto de réplicas se llama `rsSaludUSM` en lugar del nombre de ejemplo del enunciado.

MySQLSaludUSM