-- CONSULTAS


-- 1. Muestra ordenados alfabéticamente por nombre, los pacientes que tienen como previsión de salud Isapre (demuestra que solo tengan una previsión)
SELECT
    p.RUT_Paciente,
    p.Nombre,
    pr.Tipo AS Prevision,
    p.Comuna
	FROM Paciente p JOIN Prevision pr ON p.ID_Prevision = pr.ID_Prevision WHERE pr.Tipo = 'Isapre' ORDER BY p.Nombre ASC;

-- 2. Muestra las especialidades que posee cada médico de nuestra BDD (demuestra que los médicos tengan entre 1 a 3 especialidades)
SELECT
    m.Nombre AS Medico,
    STRING_AGG(e.Nombre, ' | ' ORDER BY me.Numero_Especialidad) AS Especialidades
	FROM Medico m JOIN Medico_Especialidad me ON m.RUT_Medico = me.RUT_Medico JOIN Especialidad e ON me.ID_Especialidad = e.ID_Especialidad GROUP BY m.RUT_Medico, m.Nombre ORDER BY m.Nombre;

-- 3. Muestra los médicos con más de 10 citas (demuestra que los médicos no tengan tope de citas) 
SELECT
    m.Nombre AS Medico,
    COUNT(c.ID_Cita) AS Total_Citas
	FROM Medico m JOIN Cita c ON m.RUT_Medico = c.RUT_Medico GROUP BY m.RUT_Medico, m.Nombre HAVING COUNT(c.ID_Cita) > 10 ORDER BY Total_Citas DESC;

-- 4. Muestra los centros médicos donde más del 15% de sus citas corresponden a inasistencias (demuestra todas las Citas con estado 'No Asistio')
SELECT
    cm.Nombre AS Centro,
    COUNT(c.ID_Cita) AS Total_Citas,
    COUNT(c.ID_Cita) FILTER (
        WHERE c.ID_Estado = 4
    ) AS Inasistencias,
    ROUND(
        100.0 * COUNT(c.ID_Cita) FILTER (
            WHERE c.ID_Estado = 4
        ) / COUNT(c.ID_Cita),
        2
    ) AS Porcentaje_Inasistencia
FROM Centro_Medico cm
JOIN Cita c
    ON cm.Codigo_Interno = c.Codigo_Interno
GROUP BY cm.Codigo_Interno, cm.Nombre
HAVING
    100.0 * COUNT(c.ID_Cita) FILTER (
        WHERE c.ID_Estado = 4
    ) / COUNT(c.ID_Cita) > 15
ORDER BY Porcentaje_Inasistencia DESC;

-- 5. Muestra los atributos de los diagnósticos registrado 5 o más veces (demuestra que nos diagnósticos se pueden repetir en distintas atenciones y además una atención puede tener más diagnósticos)
SELECT
    d.Codigo,
    d.Descripcion,
    COUNT(DISTINCT ad.ID_Atencion) AS Cantidad_Apariciones
FROM Diagnostico d
JOIN Atencion_Diagnostico ad
    ON d.Codigo = ad.Codigo
GROUP BY d.Codigo, d.Descripcion
HAVING COUNT(DISTINCT ad.ID_Atencion) >= 5
ORDER BY Cantidad_Apariciones DESC;

-- 6. Muestra la cantidad de veces que un paciente ha sido atendido en 3 o más especialidades (demuestra que un paciente puede ser atendido en especialidades distintas)
SELECT
    p.Nombre AS Paciente,
    COUNT(DISTINCT c.ID_Especialidad) AS Cantidad_Especialidades
FROM Paciente p
JOIN Cita c
    ON p.RUT_Paciente = c.RUT_Paciente
JOIN Estado_Cita ec
    ON c.ID_Estado = ec.ID_Estado
WHERE ec.Estado = 'Atendida'
GROUP BY p.RUT_Paciente, p.Nombre
HAVING COUNT(DISTINCT c.ID_Especialidad) >= 3
ORDER BY Cantidad_Especialidades DESC;


-- 7. Muestra médicos con citas agendadas que no tienen el estado en 'No Asistió' (demuestra que algunos pacientes tienen el estado 'No Asistió')
SELECT
    m.Nombre AS Medico,
    COUNT(c.ID_Cita) AS Total_Citas
FROM Medico m
JOIN Cita c
    ON m.RUT_Medico = c.RUT_Medico
GROUP BY m.RUT_Medico, m.Nombre
HAVING COUNT(*) FILTER (
    WHERE c.ID_Estado = 4
) = 0
ORDER BY m.Nombre;

-- 8. Muestra los 5 medicamentos más recetados en Medicina General (demuestra que los medicamentos pueden ser recetados a varios pacientes)
SELECT
    r.Medicamento,
    COUNT(r.ID_Receta) AS Cantidad_Recetas
FROM Especialidad e
JOIN Cita c
    ON e.ID_Especialidad = c.ID_Especialidad
JOIN Atencion a
    ON c.ID_Cita = a.ID_Cita
JOIN Receta r
    ON a.ID_Atencion = r.ID_Atencion
WHERE e.Nombre = 'Medicina General'
GROUP BY r.Medicamento
ORDER BY Cantidad_Recetas DESC
LIMIT 5;

-- 9. Muestra la edad promedio mayor a 40 años con pacientes por diagnóstico existente
WITH Pacientes_Diagnostico AS (
    SELECT DISTINCT
        d.Codigo,
        d.Descripcion,
        p.RUT_Paciente,
        p.Fecha_Nacimiento
    FROM Diagnostico d
    JOIN Atencion_Diagnostico ad
        ON d.Codigo = ad.Codigo
    JOIN Atencion a
        ON ad.ID_Atencion = a.ID_Atencion
    JOIN Cita c
        ON a.ID_Cita = c.ID_Cita
    JOIN Paciente p
        ON c.RUT_Paciente = p.RUT_Paciente
)
SELECT
    Codigo,
    Descripcion,
    ROUND(
        AVG(EXTRACT(YEAR FROM AGE(CURRENT_DATE, Fecha_Nacimiento))),
        2
    ) AS Edad_Promedio
FROM Pacientes_Diagnostico
GROUP BY Codigo, Descripcion
HAVING AVG(
    EXTRACT(YEAR FROM AGE(CURRENT_DATE, Fecha_Nacimiento))
) > 40
ORDER BY Edad_Promedio DESC;

-- 10. Cancela las citas en estado 'Reservadas' o 'Confirmadas' cuya fecha ya es antigua (demuestra que las citas se pueden cancelar)
SELECT
    c.ID_Cita,
    c.Fecha_Hora,
    ec.Estado
FROM Cita c
JOIN Estado_Cita ec
    ON c.ID_Estado = ec.ID_Estado
WHERE c.ID_Estado IN (1, 2)
  AND c.Fecha_Hora < CURRENT_TIMESTAMP
ORDER BY c.Fecha_Hora;

UPDATE Cita
SET ID_Estado = 5
WHERE ID_Estado IN (1, 2)
  AND Fecha_Hora < CURRENT_TIMESTAMP;

SELECT
    c.ID_Cita,
    c.Fecha_Hora,
    ec.Estado
FROM Cita c
JOIN Estado_Cita ec
    ON c.ID_Estado = ec.ID_Estado
WHERE c.ID_Estado = 5
ORDER BY c.Fecha_Hora;