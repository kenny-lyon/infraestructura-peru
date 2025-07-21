<?php
header('Content-Type: application/json');
ob_start();
require 'conexion.php';
ob_end_clean();

$action = $_GET['action'] ?? '';
$db = new Database();

try {
    switch($action) {
        case 'registrar_actividad':
            // Registrar actividad del usuario (simulado)
            $auditoria = [
                'timestamp' => date('Y-m-d H:i:s'),
                'usuario' => $_POST['usuario'] ?? 'usuario_anonimo',
                'accion' => $_POST['accion'] ?? 'view',
                'seccion' => $_POST['seccion'] ?? 'dashboard',
                'detalles' => $_POST['detalles'] ?? '',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'localhost',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'sesion_id' => session_id()
            ];
            
            // Simular inserción exitosa
            $insertId = uniqid();
            echo json_encode(['success' => true, 'id' => $insertId]);
            break;
            
        case 'validar_integridad':
            // Validar integridad de datos
            $puertos = $db->getPuertos()->find();
            $aeropuertos = $db->getAeropuertos()->find();
            $ferroviarias = $db->getFerroviarias()->find();
            
            // Verificar integridad básica
            $sinCoordsPuertos = 0;
            $sinCoordsAeropuertos = 0;
            $sinCoordsLatFerroviarias = 0;
            $duplicados = 0;
            
            foreach ($puertos as $p) {
                if (empty($p['LATITUD']) || empty($p['LONGITUD'])) {
                    $sinCoordsPuertos++;
                }
            }
            
            foreach ($aeropuertos as $a) {
                if (empty($a['LATITUD']) || empty($a['LONGITUD'])) {
                    $sinCoordsAeropuertos++;
                }
            }
            
            foreach ($ferroviarias as $f) {
                if (empty($f['LATITUD'])) {
                    $sinCoordsLatFerroviarias++;
                }
            }
            
            $totalRegistros = count($puertos) + count($aeropuertos) + count($ferroviarias);
            $coordenadasInvalidas = $sinCoordsPuertos + $sinCoordsAeropuertos + $sinCoordsLatFerroviarias;
            $porcentajeCalidad = round((($totalRegistros - $coordenadasInvalidas) / max($totalRegistros, 1)) * 100, 2);
            
            // Estructura compatible con el frontend
            $validaciones = [
                'total_puertos' => count($puertos),
                'total_aeropuertos' => count($aeropuertos), 
                'total_ferroviarias' => count($ferroviarias),
                'total_registros' => $totalRegistros,
                'coordenadas_invalidas' => $coordenadasInvalidas,
                'puertos_sin_coordenadas' => $sinCoordsPuertos,
                'aeropuertos_sin_coordenadas' => $sinCoordsAeropuertos,
                'ferroviarias_sin_latitud' => $sinCoordsLatFerroviarias,
                'duplicados_total' => $duplicados,
                'duplicados_puertos' => 0,
                'duplicados_aeropuertos' => 0,
                'duplicados_ferroviarias' => 0,
                'porcentaje_calidad' => $porcentajeCalidad,
                'registros_sin_estado' => 0,
                'registros_sin_administrador' => 0,
                'departamentos_unicos' => count(array_unique(array_merge(
                    array_column($puertos, 'DEPARTAMENTO'),
                    array_column($aeropuertos, 'DEPARTAMENTO'),
                    array_column($ferroviarias, 'DEPARTAMENTO')
                ))),
                'ultimo_backup' => 'Backup simulado - ' . date('Y-m-d H:i:s'),
                'logs_criticos' => 0
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Validación de integridad completada',
                'timestamp' => date('Y-m-d H:i:s'),
                'validaciones' => $validaciones
            ]);
            break;
            
        case 'backup_datos':
            // Simular backup de datos
            $puertos = $db->getPuertos()->find();
            $aeropuertos = $db->getAeropuertos()->find();
            $ferroviarias = $db->getFerroviarias()->find();
            
            $totalRegistros = count($puertos) + count($aeropuertos) + count($ferroviarias);
            $backupId = 'backup_' . date('Ymd_His') . '_' . uniqid();
            
            // Estructura compatible con el frontend
            $datosRespaldados = [
                count($puertos) . ' Puertos marítimos y fluviales',
                count($aeropuertos) . ' Aeropuertos y aeródromos',
                count($ferroviarias) . ' Estaciones y tramos ferroviarios',
                'Metadatos y configuraciones del sistema',
                'Logs de auditoría y actividad',
                'Índices geoespaciales y relaciones'
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Backup generado exitosamente',
                'backup_id' => $backupId,
                'timestamp' => date('Y-m-d H:i:s'),
                'datos_respaldados' => $datosRespaldados,
                'estadisticas' => [
                    'total_registros' => $totalRegistros,
                    'tamaño_estimado' => round($totalRegistros * 2.5) . ' KB',
                    'tiempo_estimado' => '2.3 segundos',
                    'compresion' => 'gzip (65% reducción)'
                ]
            ]);
            break;
            
        case 'performance_stats':
            // Simular estadísticas de rendimiento
            $inicio = microtime(true);
            
            $puertos = $db->getPuertos()->find();
            $aeropuertos = $db->getAeropuertos()->find();
            $ferroviarias = $db->getFerroviarias()->find();
            
            $tiempoCarga = round((microtime(true) - $inicio) * 1000, 2);
            
            $stats = [
                'tiempo_carga_ms' => $tiempoCarga,
                'memoria_usada' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'memoria_pico' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
                'registros_por_segundo' => round((count($puertos) + count($aeropuertos) + count($ferroviarias)) / max($tiempoCarga / 1000, 0.001)),
                'conexion_tipo' => 'CSV Local (Desarrollo)',
                'version_php' => PHP_VERSION
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Estadísticas de rendimiento obtenidas',
                'data' => $stats
            ]);
            break;
            
        case 'obtener_logs_sistema':
            // Simular logs del sistema
            $logs = [
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'nivel' => 'INFO',
                    'mensaje' => 'Sistema iniciado correctamente',
                    'usuario' => 'sistema'
                ],
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                    'nivel' => 'INFO',
                    'mensaje' => 'Datos cargados desde CSV: ' . (count($db->getPuertos()->find()) + count($db->getAeropuertos()->find()) + count($db->getFerroviarias()->find())) . ' registros',
                    'usuario' => 'sistema'
                ],
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                    'nivel' => 'INFO',
                    'mensaje' => 'Dashboard accedido por usuario',
                    'usuario' => 'usuario_demo'
                ],
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
                    'nivel' => 'INFO',
                    'mensaje' => 'Consulta avanzada ejecutada: búsqueda por departamento',
                    'usuario' => 'usuario_demo'
                ],
                [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'nivel' => 'INFO',
                    'mensaje' => 'Validación de integridad solicitada',
                    'usuario' => 'usuario_demo'
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Logs del sistema obtenidos',
                'data' => $logs
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>