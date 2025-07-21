<?php
header('Content-Type: application/json');
require 'conexion.php';

$action = $_GET['action'] ?? '';
$db = new Database();

try {
    switch ($action) {
        case 'summary':
            echo json_encode(generateSummaryReport($db));
            break;
        case 'department':
            $dept = $_GET['department'] ?? '';
            echo json_encode(generateDepartmentReport($db, $dept));
            break;
        case 'operational':
            echo json_encode(generateOperationalReport($db));
            break;
        case 'export':
            $format = $_GET['format'] ?? 'json';
            $type = $_GET['type'] ?? 'summary';
            echo json_encode(exportReport($db, $type, $format));
            break;
        case 'departments_list':
            echo json_encode(getDepartmentsList($db));
            break;
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function generateSummaryReport($db) {
    $startTime = microtime(true);
    
    // Get data
    $puertosData = $db->getPuertos()->find();
    $aeropuertosData = $db->getAeropuertos()->find();
    $ferroviariasData = $db->getFerroviarias()->find();
    
    // Conteos generales
    $puertos = count($puertosData);
    $aeropuertos = count($aeropuertosData);
    $ferroviarias = count($ferroviariasData);
    
    // Estados operativos
    $puertosOperativos = count(array_filter($puertosData, function($p) { 
        return isset($p['ESTADO']) && strtolower($p['ESTADO']) === 'operativo'; 
    }));
    $aeropuertosOperativos = count(array_filter($aeropuertosData, function($a) { 
        return isset($a['ESTADO']) && strtolower($a['ESTADO']) === 'operativo'; 
    }));
    $ferroviariasOperativas = count(array_filter($ferroviariasData, function($f) { 
        return isset($f['ESTADO']) && strtolower($f['ESTADO']) === 'operativo'; 
    }));
    
    // Distribución por departamento
    $deptDistribution = [];
    
    // Contar por departamento
    foreach ($puertosData as $puerto) {
        $dept = $puerto['DEPARTAMENTO'] ?? 'Sin Departamento';
        $deptDistribution[$dept] = ($deptDistribution[$dept] ?? 0) + 1;
    }
    
    foreach ($aeropuertosData as $aeropuerto) {
        $dept = $aeropuerto['DEPARTAMENTO'] ?? 'Sin Departamento';
        $deptDistribution[$dept] = ($deptDistribution[$dept] ?? 0) + 1;
    }
    
    foreach ($ferroviariasData as $ferroviaria) {
        $dept = $ferroviaria['DEPARTAMENTO'] ?? 'Sin Departamento';
        $deptDistribution[$dept] = ($deptDistribution[$dept] ?? 0) + 1;
    }
    
    arsort($deptDistribution);
    
    // Titularidad
    $titularityStats = [];
    $datasets = [
        'puertos' => $puertosData,
        'aeropuertos' => $aeropuertosData, 
        'ferroviarias' => $ferroviariasData
    ];
    
    foreach ($datasets as $type => $data) {
        $titularityStats[$type] = [];
        
        foreach ($data as $item) {
            $titularidad = $item['TITULARIDAD'] ?? 'No especificado';
            $titularityStats[$type][$titularidad] = ($titularityStats[$type][$titularidad] ?? 0) + 1;
        }
        
        arsort($titularityStats[$type]);
    }
    
    $endTime = microtime(true);
    
    return [
        'success' => true,
        'report_type' => 'summary',
        'generated_at' => date('Y-m-d H:i:s'),
        'generation_time' => round(($endTime - $startTime) * 1000, 2) . ' ms',
        'data' => [
            'totals' => [
                'puertos' => $puertos,
                'aeropuertos' => $aeropuertos,
                'ferroviarias' => $ferroviarias,
                'total_infraestructura' => $puertos + $aeropuertos + $ferroviarias
            ],
            'operational_status' => [
                'puertos_operativos' => $puertosOperativos,
                'aeropuertos_operativos' => $aeropuertosOperativos,
                'ferroviarias_operativas' => $ferroviariasOperativas,
                'total_operativos' => $puertosOperativos + $aeropuertosOperativos + $ferroviariasOperativas,
                'porcentaje_operativo' => round((($puertosOperativos + $aeropuertosOperativos + $ferroviariasOperativas) / max(1, $puertos + $aeropuertos + $ferroviarias)) * 100, 1)
            ],
            'department_distribution' => $deptDistribution,
            'titularity_stats' => $titularityStats,
            'coverage' => [
                'departments_count' => count($deptDistribution),
                'departments_with_infrastructure' => array_keys($deptDistribution)
            ]
        ]
    ];
}

function generateDepartmentReport($db, $department) {
    if (empty($department)) {
        return ['error' => 'Departamento no especificado'];
    }
    
    $startTime = microtime(true);
    $report = ['success' => true, 'department' => $department, 'data' => []];
    
    // Datos de cada tipo de infraestructura
    foreach (['puertos', 'aeropuertos', 'ferroviarias'] as $type) {
        $collection = $type === 'puertos' ? $db->getPuertos() : 
                     ($type === 'aeropuertos' ? $db->getAeropuertos() : $db->getFerroviarias());
        
        $items = $collection->find(['DEPARTAMENTO' => $department]);
        
        $typeData = [
            'count' => count($items),
            'items' => [],
            'status_distribution' => [],
            'titularity_distribution' => []
        ];
        
        foreach ($items as $item) {
            $itemData = [
                'nombre' => $item['NOMBRE'] ?? $item['NOMBRE_TERMINAL'] ?? 'Sin nombre',
                'localidad' => $item['LOCALIDAD'] ?? 'No especificada',
                'estado' => $item['ESTADO'] ?? 'No especificado',
                'titularidad' => $item['TITULARIDAD'] ?? 'No especificada'
            ];
            
            if (isset($item['LATITUD']) && isset($item['LONGITUD'])) {
                $itemData['coordenadas'] = [
                    'lat' => $item['LATITUD'],
                    'lng' => $item['LONGITUD']
                ];
            }
            
            // Campos específicos por tipo
            if ($type === 'ferroviarias') {
                $itemData['tramo'] = $item['TRAMO'] ?? 'No especificado';
                $itemData['longitud_km'] = $item['LONGITUD'] ?? 0;
                $itemData['ancho_via'] = $item['ANCHO'] ?? 'No especificado';
            }
            
            $typeData['items'][] = $itemData;
            
            // Distribuciones
            $estado = $item['ESTADO'] ?? 'No especificado';
            $titularidad = $item['TITULARIDAD'] ?? 'No especificada';
            
            $typeData['status_distribution'][$estado] = ($typeData['status_distribution'][$estado] ?? 0) + 1;
            $typeData['titularity_distribution'][$titularidad] = ($typeData['titularity_distribution'][$titularidad] ?? 0) + 1;
        }
        
        $report['data'][$type] = $typeData;
    }
    
    $endTime = microtime(true);
    $report['generated_at'] = date('Y-m-d H:i:s');
    $report['generation_time'] = round(($endTime - $startTime) * 1000, 2) . ' ms';
    
    return $report;
}

function generateOperationalReport($db) {
    $startTime = microtime(true);
    
    $operationalData = [];
    
    foreach (['puertos', 'aeropuertos', 'ferroviarias'] as $type) {
        $collection = $type === 'puertos' ? $db->getPuertos() : 
                     ($type === 'aeropuertos' ? $db->getAeropuertos() : $db->getFerroviarias());
        
        // Estados disponibles
        $pipeline = [
            ['$group' => ['_id' => '$ESTADO', 'count' => ['$sum' => 1]]],
            ['$sort' => ['count' => -1]]
        ];
        
        $statusDistribution = [];
        $statusResults = $collection->aggregate($pipeline);
        
        foreach ($statusResults as $item) {
            $status = $item['_id'] ?? 'No especificado';
            $statusDistribution[$status] = $item['count'];
        }
        
        // Items operativos por departamento
        $operativeByDept = [];
        $operativeItems = $collection->find(['ESTADO' => 'Operativo']);
        
        foreach ($operativeItems as $item) {
            $dept = $item['DEPARTAMENTO'] ?? 'No especificado';
            $operativeByDept[$dept] = ($operativeByDept[$dept] ?? 0) + 1;
        }
        
        arsort($operativeByDept);
        
        $operationalData[$type] = [
            'status_distribution' => $statusDistribution,
            'operative_by_department' => $operativeByDept,
            'total_operative' => $statusDistribution['Operativo'] ?? 0,
            'total_items' => array_sum($statusDistribution),
            'operative_percentage' => round((($statusDistribution['Operativo'] ?? 0) / max(1, array_sum($statusDistribution))) * 100, 1)
        ];
    }
    
    $endTime = microtime(true);
    
    return [
        'success' => true,
        'report_type' => 'operational',
        'generated_at' => date('Y-m-d H:i:s'),
        'generation_time' => round(($endTime - $startTime) * 1000, 2) . ' ms',
        'data' => $operationalData
    ];
}

function getDepartmentsList($db) {
    $departments = [];
    
    foreach (['puertos', 'aeropuertos', 'ferroviarias'] as $type) {
        $collection = $type === 'puertos' ? $db->getPuertos() : 
                     ($type === 'aeropuertos' ? $db->getAeropuertos() : $db->getFerroviarias());
        
        $depts = array_unique(array_column($collection->find(), 'DEPARTAMENTO'));
        $departments = array_merge($departments, $depts);
    }
    
    $departments = array_unique($departments);
    sort($departments);
    
    return [
        'success' => true,
        'departments' => $departments
    ];
}

function exportReport($db, $type, $format) {
    $report = null;
    
    switch ($type) {
        case 'summary':
            $report = generateSummaryReport($db);
            break;
        case 'operational':
            $report = generateOperationalReport($db);
            break;
        default:
            return ['error' => 'Tipo de reporte no válido'];
    }
    
    if (!$report || !$report['success']) {
        return ['error' => 'Error al generar el reporte'];
    }
    
    $filename = $type . '_report_' . date('Y-m-d_H-i-s');
    
    switch ($format) {
        case 'csv':
            return exportToCSV($report, $filename);
        case 'json':
            return exportToJSON($report, $filename);
        case 'xml':
            return exportToXML($report, $filename);
        default:
            return ['error' => 'Formato no soportado'];
    }
}

function exportToCSV($report, $filename) {
    $csvData = [];
    $csvData[] = ['Reporte de Infraestructura - ' . $report['report_type']];
    $csvData[] = ['Generado el', $report['generated_at']];
    $csvData[] = [''];
    
    if ($report['report_type'] === 'summary') {
        $data = $report['data'];
        
        $csvData[] = ['TOTALES'];
        $csvData[] = ['Tipo', 'Cantidad'];
        $csvData[] = ['Puertos', $data['totals']['puertos']];
        $csvData[] = ['Aeropuertos', $data['totals']['aeropuertos']];
        $csvData[] = ['Ferroviarias', $data['totals']['ferroviarias']];
        $csvData[] = ['Total', $data['totals']['total_infraestructura']];
        $csvData[] = [''];
        
        $csvData[] = ['DISTRIBUCIÓN POR DEPARTAMENTO'];
        $csvData[] = ['Departamento', 'Cantidad'];
        foreach ($data['department_distribution'] as $dept => $count) {
            $csvData[] = [$dept, $count];
        }
    }
    
    return [
        'success' => true,
        'format' => 'csv',
        'filename' => $filename . '.csv',
        'data' => $csvData,
        'download_ready' => true
    ];
}

function exportToJSON($report, $filename) {
    return [
        'success' => true,
        'format' => 'json',
        'filename' => $filename . '.json',
        'data' => $report,
        'download_ready' => true
    ];
}

function exportToXML($report, $filename) {
    $xml = new SimpleXMLElement('<report/>');
    arrayToXML($report, $xml);
    
    return [
        'success' => true,
        'format' => 'xml',
        'filename' => $filename . '.xml',
        'data' => $xml->asXML(),
        'download_ready' => true
    ];
}

function arrayToXML($array, &$xml) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if (is_numeric($key)) {
                $key = 'item';
            }
            $subnode = $xml->addChild($key);
            arrayToXML($value, $subnode);
        } else {
            $xml->addChild($key, htmlspecialchars($value));
        }
    }
}
?>