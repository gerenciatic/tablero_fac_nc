<?php
// Configuración de headers para CORS y JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar solicitudes OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivo de conexión
include_once 'includes/conexsql.php';

// Obtener datos JSON del cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);

// Parámetros de entrada
$fechaInicio = $input['fechaInicio'] ?? date('Y-m-01');
$fechaFin = $input['fechaFin'] ?? date('Y-m-d');
$tipoMetrica = $input['tipoMetrica'] ?? 'mensual';
$action = $input['action'] ?? 'get_data';

try {
        // Si la acción es guardar una meta
        if ($action === 'save_meta' && isset($input['meta'])) {
            $ano = $input['ano'] ?? date('Y');
            $mes = $input['mes'] ?? date('m');
            $meta = floatval($input['meta']);
            
            // Verificar si la tabla Metas_Notas existe, si no crearla
            $sqlCheckTable = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='Metas_Notas' AND xtype='U')
                            CREATE TABLE Metas_Notas (
                                Id INT IDENTITY(1,1) PRIMARY KEY,
                                Ano INT NOT NULL,
                                Mes INT NOT NULL,
                                Meta_Mes_Notas DECIMAL(5,2) NOT NULL, // CAMBIADO
                                Meta_Ano_Notas DECIMAL(5,2) NULL, // CAMBIADO
                                Fecha_Registro DATETIME DEFAULT GETDATE(),
                                Usuario_Registro VARCHAR(100),
                                Fecha_Actualizacion DATETIME DEFAULT GETDATE(),
                                Usuario_Actualizacion VARCHAR(100),
                                UNIQUE(Ano, Mes)
                            )";
            sqlsrv_query($conn, $sqlCheckTable);
            
            // Insertar o actualizar la meta - USAR Meta_Mes_Notas
            $sqlUpsert = "MERGE INTO Metas_Notas AS target
                        USING (SELECT ? AS Ano, ? AS Mes, ? AS Meta_Mes_Notas) AS source // CAMBIADO
                        ON target.Ano = source.Ano AND target.Mes = source.Mes
                        WHEN MATCHED THEN
                            UPDATE SET Meta_Mes_Notas = source.Meta_Mes_Notas, // CAMBIADO
                                        Fecha_Actualizacion = GETDATE(),
                                        Usuario_Actualizacion = 'sistema'
                        WHEN NOT MATCHED THEN
                            INSERT (Ano, Mes, Meta_Mes_Notas, Usuario_Registro)  // CAMBIADO
                            VALUES (source.Ano, source.Mes, source.Meta_Mes_Notas, 'sistema');"; // CAMBIADO
            
            $params = array($ano, $mes, $meta);
            $stmt = sqlsrv_query($conn, $sqlUpsert, $params);
            
            if ($stmt === false) {
                throw new Exception('Error al guardar la meta: ' . print_r(sqlsrv_errors(), true));
            }
            
            echo json_encode(['success' => true, 'message' => 'Meta guardada correctamente']);
            exit();
        }
    
    // Consulta para obtener los datos del período seleccionado
    $sql = "
    SELECT
        Fecha,
        YEAR(Fecha) AS ANNO,
        MONTH(Fecha) AS MES,
        DAY(Fecha) AS DIA,
        SUM(Total_Facturas) AS Total_Facturas,
        SUM(Total_Notas_Credito) AS Total_Notas_Credito,
        SUM(Total_Documentos) AS Total_Documentos
    FROM (
        -- Facturas
        SELECT
            B002200FAC AS Fecha,
            COUNT(*) AS Total_Facturas,
            0 AS Total_Notas_Credito,
            COUNT(*) AS Total_Documentos
        FROM dbo.FAC_CAB
        WHERE B002200FAC IS NOT NULL
        AND B002200FAC BETWEEN ? AND ?
        GROUP BY B002200FAC
        
        UNION ALL
        
        -- Notas de Crédito
        SELECT
            C002110FAC AS Fecha,
            0 AS Total_Facturas,
            COUNT(*) AS Total_Notas_Credito,
            COUNT(*) AS Total_Documentos
        FROM dbo.vwX002CF110H
        WHERE C002110FAC IS NOT NULL
        AND C002110TDO ='NC'
        AND [C002110CCN] LIKE '%01%'
        AND C002110FAC BETWEEN ? AND ?
        GROUP BY C002110FAC
    ) AS Combined
    GROUP BY Fecha
    ORDER BY Fecha DESC
    ";

    // Ejecutar consulta con parámetros
    $params = array($fechaInicio, $fechaFin, $fechaInicio, $fechaFin);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        throw new Exception('Error en la consulta: ' . print_r($errors, true));
    }
    
    $datos = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if (isset($row['Fecha']) && $row['Fecha'] instanceof DateTime) {
            $row['Fecha'] = $row['Fecha']->format('Y-m-d');
        }
        $datos[] = $row;
    }
    
    sqlsrv_free_stmt($stmt);
    
    // CONSULTA PARA OBTENER EL PROMEDIO HISTÓRICO MENSUAL (todo el año actual)
    $anoActual = date('Y');
   // CONSULTA PARA OBTENER EL PROMEDIO HISTÓRICO MENSUAL (todos los años disponibles)
$sqlPromedio = "
SELECT
    YEAR(Fecha) as ano,
    MONTH(Fecha) as mes,
    SUM(Total_Notas_Credito) as total_notas,
    SUM(Total_Facturas) as total_facturas,
    CASE 
        WHEN SUM(Total_Facturas) > 0 THEN (SUM(Total_Notas_Credito) / SUM(Total_Facturas)) * 100 
        ELSE 0 
    END as porcentaje_notas
FROM (
    -- Facturas
    SELECT
        B002200FAC AS Fecha,
        COUNT(*) AS Total_Facturas,
        0 AS Total_Notas_Credito
    FROM dbo.FAC_CAB
    WHERE B002200FAC IS NOT NULL
    AND B002200FAC >= '2023-01-01'  -- Ajusta según los datos históricos disponibles
    GROUP BY B002200FAC
    
    UNION ALL
    
    -- Notas de Crédito
    SELECT
        C002110FAC AS Fecha,
        0 AS Total_Facturas,
        COUNT(*) AS Total_Notas_Credito
    FROM dbo.vwX002CF110H
    WHERE C002110FAC IS NOT NULL
    AND C002110TDO ='NC'
    AND [C002110CCN] LIKE '%01%'
    AND C002110FAC >= '2023-01-01'  -- Ajusta según los datos históricos disponibles
    GROUP BY C002110FAC
) AS Combined
GROUP BY YEAR(Fecha), MONTH(Fecha)
HAVING SUM(Total_Facturas) > 0  -- Solo meses con datos válidos
ORDER BY YEAR(Fecha), MONTH(Fecha)
";

$stmtPromedio = sqlsrv_query($conn, $sqlPromedio);

$porcentajesMensuales = array();
$sumaPorcentajes = 0;
$mesesConDatos = 0;

if ($stmtPromedio !== false) {
    while ($row = sqlsrv_fetch_array($stmtPromedio, SQLSRV_FETCH_ASSOC)) {
        if ($row['porcentaje_notas'] > 0) {
            $porcentajesMensuales[] = $row;
            $sumaPorcentajes += $row['porcentaje_notas'];
            $mesesConDatos++;
            
            // Debug: mostrar los porcentajes mensuales
            error_log("Mes " . $row['mes'] . "/" . $row['ano'] . ": " . $row['porcentaje_notas'] . "%");
        }
    }
    sqlsrv_free_stmt($stmtPromedio);
}

// Calcular el promedio histórico
$promedio_historico = $mesesConDatos > 0 ? $sumaPorcentajes / $mesesConDatos : 0;

// Debug: mostrar el promedio calculado
error_log("Promedio histórico calculado: " . $promedio_historico . "% basado en " . $mesesConDatos . " meses");

            
        // CONSULTAR LA META DESDE LA TABLA Metas_Notas
        $meta_manual = null;
        $mesActual = date('m');
        $anoActual = date('Y');

        // Verificar si la tabla Metas_Notas existe
        $sqlCheckTable = "SELECT COUNT(*) as existe FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Metas_Notas'";
        $stmtCheckTable = sqlsrv_query($conn, $sqlCheckTable);

        if ($stmtCheckTable !== false) {
            $row = sqlsrv_fetch_array($stmtCheckTable, SQLSRV_FETCH_ASSOC);
            $tablaExiste = ($row['existe'] > 0);
            sqlsrv_free_stmt($stmtCheckTable);
            
            if ($tablaExiste) {
                // Obtener la meta para el mes actual - USAR Meta_Mes_Notas
                $sqlMetaExistente = "SELECT Meta_Mes_Notas FROM Metas_Notas WHERE Ano = ? AND Mes = ?";
                $paramsMeta = array($anoActual, $mesActual);
                $stmtMeta = sqlsrv_query($conn, $sqlMetaExistente, $paramsMeta);
                
                if ($stmtMeta !== false && sqlsrv_has_rows($stmtMeta)) {
                    $row = sqlsrv_fetch_array($stmtMeta, SQLSRV_FETCH_ASSOC);
                    $meta_manual = floatval($row['Meta_Mes_Notas']); // USAR Meta_Mes_Notas
                }
                sqlsrv_free_stmt($stmtMeta);
            }
        }

        // Usar la meta manual si existe, de lo contrario calcular automáticamente
        if ($meta_manual !== null) {
            $meta_automatica = $meta_manual;
            $meta_tipo = 'manual';
        } else {
            // Establecer la meta como un porcentaje menor al promedio histórico (15% menos)
            // Esto ayuda a establecer una meta alcanzable pero desafiante
            $meta_automatica = max(2, $promedio_historico * 0.85); // Mínimo 2%
            
            // Si no hay datos históricos, usar un valor por defecto (5%)
            if ($mesesConDatos === 0) {
                $meta_automatica = 5;
            }
            $meta_tipo = 'automatica';
        }
            
    // Calcular métricas del período actual
    $total_facturas = 0;
    $total_notas = 0;
    
    foreach ($datos as $fila) {
        $total_facturas += $fila['Total_Facturas'] ?? 0;
        $total_notas += $fila['Total_Notas_Credito'] ?? 0;
    }
    
    // Calcular el porcentaje actual de notas
    $porcentaje_actual = 0;
    if ($total_facturas > 0) {
        $porcentaje_actual = ($total_notas / $total_facturas) * 100;
    }
    
    // Determinar si se superó la meta (para resaltar en rojo)
    $supera_meta = $porcentaje_actual > $meta_automatica;
    
// Calcular progreso hacia la meta (CORREGIDO)
if ($meta_automatica == 0) {
    $progreso_meta = $porcentaje_actual == 0 ? 100 : 0;
    $texto_progreso = $porcentaje_actual == 0 ? "Meta cumplida (0%)" : "Lejos de la meta (+" . number_format($porcentaje_actual, 2) . "%)";
} else {
    // El progreso debe ser el porcentaje actual, no una relación con la meta
    $progreso_meta = min(100, max(0, $porcentaje_actual));
    $diferencia = $porcentaje_actual - $meta_automatica;
    
    if ($diferencia >= 0) {
        $texto_progreso = "Lejos de la meta (+" . number_format($diferencia, 2) . "% sobre la meta)";
    } else {
        $texto_progreso = "Dentro de la Meta (+" . number_format(abs($diferencia), 2) . "% bajo la meta)";
    }
}
    
    // Calcular días hábiles
    $dias_transcurridos = dias_habiles($fechaInicio, $fechaFin);
    $dias_totales = dias_habiles($fechaInicio, date('Y-m-t', strtotime($fechaFin)));
    $porcentaje_dias = $dias_totales > 0 ? round(($dias_transcurridos / $dias_totales) * 100) : 0;
    
    // Preparar datos para gráficos
    $graficos = [
        'evolution' => [
            'labels' => [],
            'facturas' => [],
            'notas' => []
        ],
        'distribution' => [
            'facturas' => $total_facturas,
            'notas' => $total_notas
        ]
    ];
    
    // Procesar datos para gráficos
    foreach ($datos as $fila) {
        if ($tipoMetrica === 'diaria') {
            $graficos['evolution']['labels'][] = $fila['Fecha'] ?? '';
            $graficos['evolution']['facturas'][] = $fila['Total_Facturas'] ?? 0;
            $graficos['evolution']['notas'][] = $fila['Total_Notas_Credito'] ?? 0;
        } else {
            $mes = ($fila['MES'] ?? '') . '/' . ($fila['ANNO'] ?? '');
            if (!in_array($mes, $graficos['evolution']['labels'])) {
                $graficos['evolution']['labels'][] = $mes;
                $graficos['evolution']['facturas'][] = 0;
                $graficos['evolution']['notas'][] = 0;
            }
            
            $index = array_search($mes, $graficos['evolution']['labels']);
            $graficos['evolution']['facturas'][$index] += $fila['Total_Facturas'] ?? 0;
            $graficos['evolution']['notas'][$index] += $fila['Total_Notas_Credito'] ?? 0;
        }
    }
    
    // Preparar respuesta
        // Preparar respuesta con más detalles
        $response = [
            'success' => true,
            'metricas' => [
                'total_facturas' => $total_facturas,
                'total_notas' => $total_notas,
                'porcentaje_actual' => round($porcentaje_actual, 2),
                'promedio_historico' => round($promedio_historico, 2),
                'meta_automatica' => round($meta_automatica, 2),
                'meta_tipo' => $meta_tipo,
                'supera_meta' => $supera_meta,
                'progreso_meta' => round($progreso_meta, 2),
                'texto_progreso' => $texto_progreso,
                'dias_transcurridos' => $dias_transcurridos,
                'dias_totales' => $dias_totales,
                'porcentaje_dias' => $porcentaje_dias,
                'meses_historicos' => $mesesConDatos, // Nuevo: cantidad de meses considerados
                'detalle_mensual' => $porcentajesMensuales // Nuevo: detalle por mes
            ],
            'graficos' => $graficos,
            'tabla' => $datos
        ];


    echo json_encode($response);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}

// Función para calcular días hábiles
function dias_habiles($fecha_inicio, $fecha_fin) {
    try {
        $inicio = new DateTime($fecha_inicio);
        $fin = new DateTime($fecha_fin);
        $fin->modify('+1 day');
        
        $interval = $fin->diff($inicio);
        $dias_totales = $interval->days;
        
        $periodo = new DatePeriod($inicio, new DateInterval('P1D'), $fin);
        
        $dias_habiles = 0;
        foreach ($periodo as $fecha) {
            if ($fecha->format('N') < 6) {
                $dias_habiles++;
            }
        }
        
        return $dias_habiles;
    } catch (Exception $e) {
        return 0;
    }
}
?>