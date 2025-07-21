<?php
header('Content-Type: application/json');
require 'conexion.php';

$action = $_GET['action'] ?? '';
$db = new Database();

try {
    switch ($action) {
        case 'stats':
            echo json_encode(getGeneralStats($db));
            break;
        case 'activity':
            echo json_encode(getActivityStats($db));
            break;
        case 'departments':
            echo json_encode(getDepartmentStats($db));
            break;
        case 'performance':
            echo json_encode(getPerformanceStats($db));
            break;
        case 'system':
            echo json_encode(getSystemStats($db));
            break;
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function getGeneralStats($db) {
    $startTime = microtime(true);
    
    $puertosData = $db->getPuertos()->find();
    $aeropuertosData = $db->getAeropuertos()->find();
    $ferroviariasData = $db->getFerroviarias()->find();
    
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
    
    // Departamentos únicos
    $deptsPuertos = array_unique(array_column($puertosData, 'DEPARTAMENTO'));
    $deptsAeropuertos = array_unique(array_column($aeropuertosData, 'DEPARTAMENTO'));
    $deptsFerroviarias = array_unique(array_column($ferroviariasData, 'DEPARTAMENTO'));
    $totalDepartamentos = count(array_unique(array_merge($deptsPuertos, $deptsAeropuertos, $deptsFerroviarias)));
    
    $endTime = microtime(true);
    
    return [
        'success' => true,
        'data' => [
            'total_infraestructura' => $puertos + $aeropuertos + $ferroviarias,
            'puertos' => $puertos,
            'aeropuertos' => $aeropuertos,
            'ferroviarias' => $ferroviarias,
            'operativos' => [
                'puertos' => $puertosOperativos,
                'aeropuertos' => $aeropuertosOperativos,
                'ferroviarias' => $ferroviariasOperativas,
                'total' => $puertosOperativos + $aeropuertosOperativos + $ferroviariasOperativas
            ],
            'departamentos_cubiertos' => $totalDepartamentos,
            'porcentaje_operativo' => round((($puertosOperativos + $aeropuertosOperativos + $ferroviariasOperativas) / max(1, $puertos + $aeropuertos + $ferroviarias)) * 100, 1),
            'query_time' => round(($endTime - $startTime) * 1000, 2) . ' ms'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function getActivityStats($db) {
    // Simular datos de actividad (en producción vendrían de logs reales)
    $activities = [
        ['user' => 'Admin', 'action' => 'Creó puerto', 'timestamp' => date('H:i:s'), 'type' => 'create'],
        ['user' => 'Editor', 'action' => 'Editó aeropuerto', 'timestamp' => date('H:i:s', time() - 300), 'type' => 'edit'],
        ['user' => 'Admin', 'action' => 'Eliminó ferroviaria', 'timestamp' => date('H:i:s', time() - 600), 'type' => 'delete'],
        ['user' => 'Viewer', 'action' => 'Consultó dashboard', 'timestamp' => date('H:i:s', time() - 900), 'type' => 'view']
    ];
    
    return [
        'success' => true,
        'data' => $activities,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function getDepartmentStats($db) {
    $puertosData = $db->getPuertos()->find();
    $aeropuertosData = $db->getAeropuertos()->find();
    $ferroviariasData = $db->getFerroviarias()->find();
    
    // Consolidar datos por departamento
    $consolidated = [];
    
    // Contar puertos por departamento
    foreach ($puertosData as $puerto) {
        $dept = $puerto['DEPARTAMENTO'] ?? 'Sin Departamento';
        $consolidated[$dept] = ($consolidated[$dept] ?? 0) + 1;
    }
    
    // Contar aeropuertos por departamento
    foreach ($aeropuertosData as $aeropuerto) {
        $dept = $aeropuerto['DEPARTAMENTO'] ?? 'Sin Departamento';
        $consolidated[$dept] = ($consolidated[$dept] ?? 0) + 1;
    }
    
    // Contar ferroviarias por departamento
    foreach ($ferroviariasData as $ferroviaria) {
        $dept = $ferroviaria['DEPARTAMENTO'] ?? 'Sin Departamento';
        $consolidated[$dept] = ($consolidated[$dept] ?? 0) + 1;
    }
    
    arsort($consolidated);
    $consolidated = array_slice($consolidated, 0, 10, true);
    
    return [
        'success' => true,
        'data' => [
            'departments' => array_keys($consolidated),
            'counts' => array_values($consolidated)
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function getPerformanceStats($db) {
    $startTime = microtime(true);
    $memoryStart = memory_get_usage();
    
    // Realizar algunas consultas para medir rendimiento
    $puertosData = $db->getPuertos()->find();
    $aeropuertosData = $db->getAeropuertos()->find();
    $ferroviariasData = $db->getFerroviarias()->find();
    
    $endTime = microtime(true);
    $memoryEnd = memory_get_usage();
    
    return [
        'success' => true,
        'data' => [
            'response_time' => round(($endTime - $startTime) * 1000, 2),
            'memory_usage' => round(($memoryEnd - $memoryStart) / 1024, 2),
            'peak_memory' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'database_status' => 'Connected',
            'total_collections' => 3,
            'server_load' => function_exists('sys_getloadavg') ? round(sys_getloadavg()[0], 2) : 0.5
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function getSystemStats($db) {
    $diskFree = disk_free_space('/');
    $diskTotal = disk_total_space('/');
    $diskUsage = round((($diskTotal - $diskFree) / $diskTotal) * 100, 1);
    
    return [
        'success' => true,
        'data' => [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'disk_usage' => $diskUsage,
            'disk_free' => round($diskFree / 1024 / 1024 / 1024, 2) . ' GB',
            'server_time' => date('Y-m-d H:i:s'),
            'uptime' => 'Simulado: 2d 14h 32m'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
}
?>