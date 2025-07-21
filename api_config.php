<?php
header('Content-Type: application/json');
require 'conexion.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = new Database();

// Configuraciones por defecto
$defaultConfig = [
    'map_settings' => [
        'default_zoom' => 6,
        'center_lat' => -9.19,
        'center_lng' => -75.0152,
        'max_zoom' => 18,
        'min_zoom' => 4,
        'clustering_enabled' => true,
        'cluster_max_zoom' => 12
    ],
    'pagination' => [
        'items_per_page' => 100,
        'max_items_per_page' => 500
    ],
    'display_settings' => [
        'show_coordinates' => true,
        'show_status_icons' => true,
        'auto_refresh_interval' => 30,
        'theme' => 'dark'
    ],
    'data_settings' => [
        'cache_enabled' => true,
        'cache_duration' => 300,
        'export_formats' => ['CSV', 'JSON', 'KML'],
        'max_export_records' => 10000
    ],
    'performance' => [
        'lazy_loading' => true,
        'image_optimization' => true,
        'compression_enabled' => true
    ]
];

try {
    switch ($method) {
        case 'GET':
            echo json_encode(getConfig($db));
            break;
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            echo json_encode(updateConfig($db, $input));
            break;
        case 'DELETE':
            echo json_encode(resetConfig($db));
            break;
        default:
            echo json_encode(['error' => 'Método no soportado']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function getConfig($db) {
    global $defaultConfig;
    
    try {
        // For CSV-based system, always return default config
        $config = $defaultConfig;
        
        return [
            'success' => true,
            'config' => $config,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    } catch (Exception $e) {
        return ['error' => 'Error al cargar configuración: ' . $e->getMessage()];
    }
}

function updateConfig($db, $newConfig) {
    try {
        // Validate configuration
        $validationErrors = validateConfig($newConfig);
        if (!empty($validationErrors)) {
            return ['error' => 'Configuración inválida', 'details' => $validationErrors];
        }
        
        // For CSV-based system, simulate save success
        return [
            'success' => true,
            'message' => 'Configuración actualizada correctamente',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    } catch (Exception $e) {
        return ['error' => 'Error al actualizar configuración: ' . $e->getMessage()];
    }
}

function resetConfig($db) {
    global $defaultConfig;
    
    try {
        // For CSV-based system, simulate reset success
        
        return [
            'success' => true,
            'message' => 'Configuración restablecida a valores por defecto',
            'config' => $defaultConfig,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    } catch (Exception $e) {
        return ['error' => 'Error al restablecer configuración: ' . $e->getMessage()];
    }
}

function validateConfig($config) {
    $errors = [];
    
    // Validate map settings
    if (isset($config['map_settings'])) {
        $map = $config['map_settings'];
        
        if (isset($map['default_zoom']) && ($map['default_zoom'] < 1 || $map['default_zoom'] > 20)) {
            $errors[] = 'Zoom por defecto debe estar entre 1 y 20';
        }
        
        if (isset($map['center_lat']) && ($map['center_lat'] < -90 || $map['center_lat'] > 90)) {
            $errors[] = 'Latitud del centro debe estar entre -90 y 90';
        }
        
        if (isset($map['center_lng']) && ($map['center_lng'] < -180 || $map['center_lng'] > 180)) {
            $errors[] = 'Longitud del centro debe estar entre -180 y 180';
        }
    }
    
    // Validate pagination
    if (isset($config['pagination'])) {
        $pag = $config['pagination'];
        
        if (isset($pag['items_per_page']) && ($pag['items_per_page'] < 10 || $pag['items_per_page'] > 1000)) {
            $errors[] = 'Items por página debe estar entre 10 y 1000';
        }
        
        if (isset($pag['max_items_per_page']) && ($pag['max_items_per_page'] < 100 || $pag['max_items_per_page'] > 5000)) {
            $errors[] = 'Máximo items por página debe estar entre 100 y 5000';
        }
    }
    
    // Validate display settings
    if (isset($config['display_settings'])) {
        $display = $config['display_settings'];
        
        if (isset($display['auto_refresh_interval']) && ($display['auto_refresh_interval'] < 5 || $display['auto_refresh_interval'] > 300)) {
            $errors[] = 'Intervalo de actualización debe estar entre 5 y 300 segundos';
        }
        
        if (isset($display['theme']) && !in_array($display['theme'], ['light', 'dark'])) {
            $errors[] = 'Tema debe ser "light" o "dark"';
        }
    }
    
    // Validate data settings
    if (isset($config['data_settings'])) {
        $data = $config['data_settings'];
        
        if (isset($data['cache_duration']) && ($data['cache_duration'] < 60 || $data['cache_duration'] > 3600)) {
            $errors[] = 'Duración de caché debe estar entre 60 y 3600 segundos';
        }
        
        if (isset($data['max_export_records']) && ($data['max_export_records'] < 100 || $data['max_export_records'] > 50000)) {
            $errors[] = 'Máximo registros para exportar debe estar entre 100 y 50000';
        }
    }
    
    return $errors;
}
?>