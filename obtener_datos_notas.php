<?php
// obtener_datos_notas.php

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

// Verificar si se recibieron datos POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

// Obtener parámetros de entrada
$input = $_POST;
$fechaInicio = isset($input['fechaInicio']) ? $input['fechaInicio'] : date('Y-m-01');
$fechaFin = isset($input['fechaFin']) ? $input['fechaFin'] : date('Y-m-d');
$tipoNota = isset($input['tipoNota']) ? $input['tipoNota'] : 'TODAS';
$agrupacion = isset($input['agrupacion']) ? $input['agrupacion'] : 'mensual';

try {
    // Primero, obtener la lista de vendedores para mapear códigos a nombres
    $queryVendedores = "SELECT A00201CVE as codigo, A00201NVE as nombre 
                       FROM dbo.vwX002AF01 
                       WHERE A00201STS = '1'";
    $stmtVendedores = sqlsrv_query($conn, $queryVendedores);
    
    $vendedoresMap = [];
    if ($stmtVendedores !== false) {
        while ($row = sqlsrv_fetch_array($stmtVendedores, SQLSRV_FETCH_ASSOC)) {
            $vendedoresMap[$row['codigo']] = $row['nombre'];
        }
        sqlsrv_free_stmt($stmtVendedores);
    }

    // CONSULTA 1: Cabecero filtrado por causa '01' (EXISTENTE - NO MODIFICAR)
    $queryCabecero = "SELECT 
                        h.C002110NDO as numero_documento,
                        h.C002110TDO as tipo_nota,
                        CONVERT(varchar, h.C002110FAC, 23) as fecha_contable,
                        h.C002110CVE as codigo_vendedor,
                        h.C002110PUS as usuario,
                        h.C002110CCN as codigo_causa_cabecero,
                        c.A002035CCN as codigo_causa,
                        c.A002035DEL as descripcion_causa
                    FROM dbo.vwX002CF110H h
                    INNER JOIN dbo.vwX002AF035 c ON h.C002110CCN = c.A002035CCN
                    WHERE CONVERT(varchar, h.C002110FAC, 23) BETWEEN ? AND ?
                    AND h.ANNO >= '2024'
                    AND h.C002110CCN ='01'
                    AND c.A002035DEL IS NOT NULL";
    
    // Añadir filtro por tipo de nota si es necesario
    if ($tipoNota !== 'TODAS') {
        $queryCabecero .= " AND h.C002110TDO = ?";
    }
    
    // Preparar consulta cabecero
    $paramsCabecero = array($fechaInicio, $fechaFin);
    if ($tipoNota !== 'TODAS') {
        $paramsCabecero[] = $tipoNota;
    }
    
    $stmtCabecero = sqlsrv_query($conn, $queryCabecero, $paramsCabecero);
    
    if ($stmtCabecero === false) {
        throw new Exception('Error al ejecutar consulta cabecero: ' . print_r(sqlsrv_errors(), true));
    }
    
    // Obtener todos los datos del cabecero filtrado
    $cabecerosFiltrados = [];
    while ($row = sqlsrv_fetch_array($stmtCabecero, SQLSRV_FETCH_ASSOC)) {
        $cabecerosFiltrados[$row['numero_documento']] = $row;
    }
    $totalNotasCabecero = count($cabecerosFiltrados);
    sqlsrv_free_stmt($stmtCabecero);

    // CONSULTA 1B: CABECERO ADICIONAL PARA VENDEDORES (SIN FILTRO DE CAUSA)
    $queryCabeceroVendedores = "SELECT 
                        h.C002110NDO as numero_documento,
                        h.C002110CVE as codigo_vendedor
                    FROM dbo.vwX002CF110H h
                    WHERE CONVERT(varchar, h.C002110FAC, 23) BETWEEN ? AND ?
                    AND h.ANNO >= '2024'";
    
    $stmtCabeceroVendedores = sqlsrv_query($conn, $queryCabeceroVendedores, array($fechaInicio, $fechaFin));
    
    if ($stmtCabeceroVendedores === false) {
        throw new Exception('Error al ejecutar consulta cabecero vendedores: ' . print_r(sqlsrv_errors(), true));
    }
    
    // Obtener cabeceros para vendedores (sin filtro de causa)
    $cabecerosVendedores = [];
    while ($row = sqlsrv_fetch_array($stmtCabeceroVendedores, SQLSRV_FETCH_ASSOC)) {
        $cabecerosVendedores[$row['numero_documento']] = $row;
    }
    sqlsrv_free_stmt($stmtCabeceroVendedores);

    // CONSULTA 2: Detalle COMPLETO (sin filtrar por causa)
    $queryDetalleCompleto = "SELECT 
                        d.C002111NDO as numero_documento,
                        d.C002111CPR as codigo_producto,
                        d.C002111CRE as codigo_causa_detalle,
                        cd.A00236CRE as codigo_causa,
                        cd.A00236DEL as descripcion_causa,
                        cd.A00236DPT as codigo_departamento,
                        CONVERT(varchar, d.C002111FAC, 23) as fecha_detalle,
                        d.C002111UVE as unidad_vendida,
                        d.C002111PIM as monto,
                        d.C002111BPR as cantidad,
                        d.C002111PPR as precio_unitario,
                        d.C002111BDE as descuento,
                        d.C002111BPP as precio_neto,
                        d.C002111MNE as monto_neto
                    FROM dbo.vwX002CF111H d
                    INNER JOIN dbo.vwX002AF036 cd ON d.C002111CRE = cd.A00236CRE
                    WHERE CONVERT(varchar, d.C002111FAC, 23) BETWEEN ? AND ?
                    AND cd.A00236DEL IS NOT NULL";
    
    // Preparar consulta detalle
    $paramsDetalle = array($fechaInicio, $fechaFin);
    $stmtDetalle = sqlsrv_query($conn, $queryDetalleCompleto, $paramsDetalle);
    
    if ($stmtDetalle === false) {
        throw new Exception('Error al ejecutar consulta detalle: ' . print_r(sqlsrv_errors(), true));
    }
    
    // Procesar detalle completo
    $detalleCompleto = [];
    $montoTotalDetalle = 0;
    $totalLineasDetalle = 0;
    
    while ($rowDetalle = sqlsrv_fetch_array($stmtDetalle, SQLSRV_FETCH_ASSOC)) {
        $detalleCompleto[] = $rowDetalle;
        $montoTotalDetalle += floatval($rowDetalle['monto'] ?? 0);
        $totalLineasDetalle++;
    }
    sqlsrv_free_stmt($stmtDetalle);

    // CONSULTA 3: Obtener departamentos
    $queryDepartamentos = "SELECT 
                            Cod_Departamento as codigo,
                            Descripcion as descripcion
                          FROM dbo.DEPARTAMENTO";
    $stmtDepartamentos = sqlsrv_query($conn, $queryDepartamentos);
    
    $departamentosMap = [];
    if ($stmtDepartamentos !== false) {
        while ($row = sqlsrv_fetch_array($stmtDepartamentos, SQLSRV_FETCH_ASSOC)) {
            $departamentosMap[$row['codigo']] = $row['descripcion'];
        }
        sqlsrv_free_stmt($stmtDepartamentos);
    }

    // Procesar datos para el dashboard (EXISTENTE)
    $datosDashboard = procesarDatosParaDashboard($detalleCompleto, $cabecerosFiltrados, $agrupacion, $fechaInicio, $fechaFin, $vendedoresMap, $departamentosMap);
    
    // NUEVO: Procesar datos ESPECÍFICOS para vendedores (SIN filtro de causa)
    $causasPorVendedor = obtenerCausasPorVendedor($detalleCompleto, $cabecerosVendedores, $vendedoresMap);
    
    // Devolver respuesta
    echo json_encode([
        'success' => true,
        'totalNotasCabecero' => $totalNotasCabecero,
        'totalLineasDetalle' => $totalLineasDetalle,
        'montoTotal' => $montoTotalDetalle,
        'notasPorDia' => $datosDashboard['notasPorDia'],
        'causaPrincipalCabecero' => $datosDashboard['causaPrincipalCabecero'],
        'causaPrincipalDetalle' => $datosDashboard['causaPrincipalDetalle'],
        'evolucionMensual' => $datosDashboard['evolucionMensual'],
        'distribucionTipo' => $datosDashboard['distribucionTipo'],
        'causasCabeceroData' => $datosDashboard['causasCabeceroData'],
        'causasDetalleData' => $datosDashboard['causasDetalleData'],
        'vendedoresData' => $datosDashboard['vendedoresData'],
        'causasPorVendedorData' => $causasPorVendedor, // NUEVO DATO
        'departamentosData' => $datosDashboard['departamentosData']
    ]);
    
} catch (Exception $e) {
    error_log('Error en obtener_datos_notas: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// ============================================================================
// FUNCIONES EXISTENTES (NO MODIFICAR)
// ============================================================================

// Función para calcular notas por día
function calcularNotasPorDia($totalNotas, $fechaInicio, $fechaFin) {
    if ($totalNotas == 0) return 0;
    
    $dias = (strtotime($fechaFin) - strtotime($fechaInicio)) / (60 * 60 * 24) + 1;
    if ($dias <= 0) $dias = 1;
    
    return round($totalNotas / $dias, 2);
}

// Función para obtener la causa principal del CABECERO
function obtenerCausaPrincipalCabecero($cabeceros) {
    if (empty($cabeceros)) return '-';
    
    $conteoCausas = [];
    foreach ($cabeceros as $cabecero) {
        $causa = $cabecero['descripcion_causa'] ?? 'Desconocida';
        if (!isset($conteoCausas[$causa])) {
            $conteoCausas[$causa] = 0;
        }
        $conteoCausas[$causa]++;
    }
    
    arsort($conteoCausas);
    return key($conteoCausas) ?: '-';
}

// Función para obtener la causa principal del DETALLE
function obtenerCausaPrincipalDetalle($detalles) {
    if (empty($detalles)) return '-';
    
    $conteoCausas = [];
    foreach ($detalles as $detalle) {
        $causa = $detalle['descripcion_causa'] ?? 'Desconocida';
        if (!isset($conteoCausas[$causa])) {
            $conteoCausas[$causa] = 0;
        }
        $conteoCausas[$causa]++;
    }
    
    arsort($conteoCausas);
    return key($conteoCausas) ?: '-';
}

// Función para obtener evolución mensual
function obtenerEvolucionMensual($detalles, $agrupacion) {
    $datosAgrupados = [];
    
    foreach ($detalles as $detalle) {
        $fecha = $detalle['fecha_detalle'];
        
        // Determinar la clave de agrupación
        if ($agrupacion === 'diaria') {
            $clave = date('Y-m-d', strtotime($fecha));
        } elseif ($agrupacion === 'mensual') {
            $clave = date('Y-m', strtotime($fecha));
        } else {
            $clave = date('Y', strtotime($fecha));
        }
        
        if (!isset($datosAgrupados[$clave])) {
            $datosAgrupados[$clave] = ['nc' => 0, 'nd' => 0];
        }
        
        // Simulación - en producción esto vendría del cabecero
        $datosAgrupados[$clave]['nc']++;
    }
    
    ksort($datosAgrupados);
    
    $labels = array_keys($datosAgrupados);
    $ncData = array_column($datosAgrupados, 'nc');
    $ndData = array_column($datosAgrupados, 'nd');
    
    return [
        'labels' => $labels,
        'ncData' => $ncData,
        'ndData' => $ndData
    ];
}

// Función para obtener distribución por tipo
function obtenerDistribucionPorTipo($detalles, $cabeceros) {
    $ncCount = 0;
    $ndCount = 0;
    
    foreach ($detalles as $detalle) {
        $numeroDocumento = $detalle['numero_documento'];
        if (isset($cabeceros[$numeroDocumento])) {
            $tipoNota = $cabeceros[$numeroDocumento]['tipo_nota'];
            if (strtoupper($tipoNota) === 'NC') {
                $ncCount++;
            } else {
                $ndCount++;
            }
        }
    }
    
    return [
        'ncCount' => $ncCount,
        'ndCount' => $ndCount
    ];
}

// Función para obtener datos por causa del CABECERO
function obtenerDatosPorCausaCabecero($cabeceros) {
    $datosPorCausa = [];
    
    foreach ($cabeceros as $cabecero) {
        $codigoCausa = $cabecero['codigo_causa_cabecero'] ?? 'DESC';
        $descripcionCausa = $cabecero['descripcion_causa'] ?? 'Desconocida';
        
        if (!isset($datosPorCausa[$codigoCausa])) {
            $datosPorCausa[$codigoCausa] = [
                'codigo' => $codigoCausa,
                'causa' => $descripcionCausa,
                'descripcion' => $descripcionCausa,
                'cantidad' => 0
            ];
        }
        
        $datosPorCausa[$codigoCausa]['cantidad']++;
    }
    
    return array_values($datosPorCausa);
}

// Función para obtener datos por causa del DETALLE (CONTAR LÍNEAS)
function obtenerDatosPorCausaDetalle($detalles) {
    $datosPorCausa = [];
    
    foreach ($detalles as $detalle) {
        $codigoCausa = $detalle['codigo_causa'] ?? 'DESC';
        $descripcionCausa = $detalle['descripcion_causa'] ?? 'Desconocida';
        $monto = floatval($detalle['monto'] ?? 0);
        
        if (!isset($datosPorCausa[$codigoCausa])) {
            $datosPorCausa[$codigoCausa] = [
                'codigo' => $codigoCausa,
                'causa' => $descripcionCausa,
                'descripcion' => $descripcionCausa,
                'cantidad' => 0,
                'monto' => 0
            ];
        }
        
        // Contar LÍNEAS, no documentos
        $datosPorCausa[$codigoCausa]['cantidad']++;
        $datosPorCausa[$codigoCausa]['monto'] += $monto;
    }
    
    return array_values($datosPorCausa);
}

// Función para obtener datos por vendedor (MEJORADA)
function obtenerDatosPorVendedor($detalles, $cabeceros, $vendedoresMap) {
    $datosPorVendedor = [];
    $causasPorVendedor = [];
    
    foreach ($detalles as $detalle) {
        $numeroDocumento = $detalle['numero_documento'];
        if (isset($cabeceros[$numeroDocumento])) {
            $codigoVendedor = $cabeceros[$numeroDocumento]['codigo_vendedor'] ?? 'DESC';
            $nombreVendedor = isset($vendedoresMap[$codigoVendedor]) ? trim($vendedoresMap[$codigoVendedor]) : "Vendedor $codigoVendedor";
            $causaCodigo = $detalle['codigo_causa'] ?? 'DESC';
            $causaDescripcion = $detalle['descripcion_causa'] ?? 'Desconocida';
            $monto = floatval($detalle['monto'] ?? 0);
            
            if (!isset($datosPorVendedor[$codigoVendedor])) {
                $datosPorVendedor[$codigoVendedor] = [
                    'codigo' => $codigoVendedor,
                    'nombre' => $nombreVendedor,
                    'cantidad' => 0,
                    'monto' => 0,
                    'causas' => []
                ];
            }
            
            // Contar líneas por vendedor
            $datosPorVendedor[$codigoVendedor]['cantidad']++;
            $datosPorVendedor[$codigoVendedor]['monto'] += $monto;
            
            // Agregar causa al vendedor
            if (!isset($datosPorVendedor[$codigoVendedor]['causas'][$causaCodigo])) {
                $datosPorVendedor[$codigoVendedor]['causas'][$causaCodigo] = [
                    'codigo' => $causaCodigo,
                    'causa' => $causaDescripcion,
                    'descripcion' => $causaDescripcion,
                    'cantidad' => 0,
                    'monto' => 0
                ];
            }
            $datosPorVendedor[$codigoVendedor]['causas'][$causaCodigo]['cantidad']++;
            $datosPorVendedor[$codigoVendedor]['causas'][$causaCodigo]['monto'] += $monto;
        }
    }
    
    // Determinar la causa principal para cada vendedor
    foreach ($datosPorVendedor as $codigoVendedor => &$vendedor) {
        $causas = $vendedor['causas'];
        if (!empty($causas)) {
            usort($causas, function($a, $b) {
                return $b['cantidad'] - $a['cantidad'];
            });
            $vendedor['causaPrincipal'] = $causas[0]['causa'];
            $vendedor['causas'] = array_values($causas);
        } else {
            $vendedor['causaPrincipal'] = 'N/A';
            $vendedor['causas'] = [];
        }
    }
    
    return array_values($datosPorVendedor);
}

// Función para obtener datos por departamento
function obtenerDatosPorDepartamento($detalles, $departamentosMap) {
    $datosPorDepartamento = [];
    $causasPorDepartamento = [];
    
    foreach ($detalles as $detalle) {
        $codigoDepartamento = $detalle['codigo_departamento'] ?? 'SIN_DEPARTAMENTO';
        $descripcionDepartamento = isset($departamentosMap[$codigoDepartamento]) ? 
            $departamentosMap[$codigoDepartamento] : "Departamento $codigoDepartamento";
        $causa = $detalle['descripcion_causa'] ?? 'Desconocida';
        
        if (!isset($datosPorDepartamento[$codigoDepartamento])) {
            $datosPorDepartamento[$codigoDepartamento] = [
                'codigo' => $codigoDepartamento,
                'descripcion' => $descripcionDepartamento,
                'cantidad' => 0,
                'monto' => 0
            ];
            $causasPorDepartamento[$codigoDepartamento] = [];
        }
        
        // Contar líneas por departamento
        $datosPorDepartamento[$codigoDepartamento]['cantidad']++;
        $datosPorDepartamento[$codigoDepartamento]['monto'] += floatval($detalle['monto'] ?? 0);
        
        // Contar causas por departamento
        if (!isset($causasPorDepartamento[$codigoDepartamento][$causa])) {
            $causasPorDepartamento[$codigoDepartamento][$causa] = 0;
        }
        $causasPorDepartamento[$codigoDepartamento][$causa]++;
    }
    
    // Determinar la causa principal para cada departamento
    foreach ($causasPorDepartamento as $codigoDepartamento => $causas) {
        if (isset($datosPorDepartamento[$codigoDepartamento])) {
            arsort($causas);
            $causaPrincipal = key($causas);
            $datosPorDepartamento[$codigoDepartamento]['causaPrincipal'] = $causaPrincipal;
        }
    }
    
    return array_values($datosPorDepartamento);
}

// Función principal para procesar datos (actualizada)
function procesarDatosParaDashboard($detalles, $cabeceros, $agrupacion, $fechaInicio, $fechaFin, $vendedoresMap, $departamentosMap) {
    return [
        'notasPorDia' => calcularNotasPorDia(count($cabeceros), $fechaInicio, $fechaFin),
        'causaPrincipalCabecero' => obtenerCausaPrincipalCabecero($cabeceros),
        'causaPrincipalDetalle' => obtenerCausaPrincipalDetalle($detalles),
        'evolucionMensual' => obtenerEvolucionMensual($detalles, $agrupacion),
        'distribucionTipo' => obtenerDistribucionPorTipo($detalles, $cabeceros),
        'causasCabeceroData' => obtenerDatosPorCausaCabecero($cabeceros),
        'causasDetalleData' => obtenerDatosPorCausaDetalle($detalles),
        'vendedoresData' => obtenerDatosPorVendedor($detalles, $cabeceros, $vendedoresMap),
        'departamentosData' => obtenerDatosPorDepartamento($detalles, $departamentosMap)
    ];
}

// ============================================================================
// NUEVA FUNCIÓN: Obtener causas por vendedor (SIN FILTRO DE CAUSA)
// ============================================================================

function obtenerCausasPorVendedor($detalles, $cabecerosVendedores, $vendedoresMap) {
    $vendedoresAgrupados = [];
    
    foreach ($detalles as $detalle) {
        $numeroDocumento = $detalle['numero_documento'];
        
        // Usar cabeceros SIN filtro de causa
        if (isset($cabecerosVendedores[$numeroDocumento])) {
            $codigoVendedor = $cabecerosVendedores[$numeroDocumento]['codigo_vendedor'] ?? 'DESC';
            $nombreVendedor = $vendedoresMap[$codigoVendedor] ?? "Vendedor $codigoVendedor";
            $codigoCausa = $detalle['codigo_causa'] ?? 'DESC';
            $descripcionCausa = $detalle['descripcion_causa'] ?? 'Desconocida';
            
            // Inicializar vendedor si no existe
            if (!isset($vendedoresAgrupados[$codigoVendedor])) {
                $vendedoresAgrupados[$codigoVendedor] = [
                    'codigo' => $codigoVendedor,
                    'nombre' => $nombreVendedor,
                    'total_causas' => 0,
                    'causas' => []
                ];
            }
            
            // Inicializar causa para este vendedor si no existe
            if (!isset($vendedoresAgrupados[$codigoVendedor]['causas'][$codigoCausa])) {
                $vendedoresAgrupados[$codigoVendedor]['causas'][$codigoCausa] = [
                    'codigo' => $codigoCausa,
                    'causa' => $descripcionCausa,
                    'cantidad' => 0
                ];
            }
            
            // Contar causa para este vendedor
            $vendedoresAgrupados[$codigoVendedor]['causas'][$codigoCausa]['cantidad']++;
            $vendedoresAgrupados[$codigoVendedor]['total_causas']++;
        }
    }
    
    // Convertir a formato adecuado para el frontend
    $resultado = [];
    foreach ($vendedoresAgrupados as $vendedor) {
        $vendedor['causas'] = array_values($vendedor['causas']);
        $resultado[] = $vendedor;
    }
    
    return $resultado;
}
?>