<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>⚙️ Procedimientos Almacenados Simulados (Sistema CSV)</h2>";

try {
    require 'conexion.php';
    
    $db = new Database();
    
    // Procedimiento 1: Reporte completo de infraestructura
    echo "<h3>📊 Procedimiento: sp_reporte_infraestructura_completo</h3>";
    
    function sp_reporte_infraestructura_completo($db, $departamento = null) {
        $puertos = $db->getPuertos()->find();
        
        // Simular agregación: agrupar por departamento
        $grupos = [];
        
        foreach ($puertos as $puerto) {
            $dept = $puerto['LOCALIDAD'] ?? 'Sin localidad';
            
            // Filtrar por departamento si se especifica
            if ($departamento && $dept !== $departamento) {
                continue;
            }
            
            if (!isset($grupos[$dept])) {
                $grupos[$dept] = [
                    '_id' => $dept,
                    'total_infraestructura' => 0,
                    'total_toneladas' => 0,
                    'infraestructura_activa' => 0,
                    'detalles' => []
                ];
            }
            
            $grupos[$dept]['total_infraestructura']++;
            $toneladas = 0; // Los puertos no tienen datos de toneladas en este dataset
            $grupos[$dept]['total_toneladas'] += $toneladas;
            
            if (($puerto['ESTADO'] ?? '') === 'Operativo') {
                $grupos[$dept]['infraestructura_activa']++;
            }
            
            $grupos[$dept]['detalles'][] = [
                'nombre' => $puerto['NOMBRE'] ?? 'Sin nombre',
                'estado' => $puerto['ESTADO'] ?? 'Sin estado',
                'toneladas' => '0',
                'administrador' => $puerto['ADMINISTRADOR'] ?? 'Sin administrador'
            ];
        }
        
        // Calcular promedios
        foreach ($grupos as &$grupo) {
            $grupo['promedio_toneladas'] = $grupo['total_infraestructura'] > 0 
                ? $grupo['total_toneladas'] / $grupo['total_infraestructura'] 
                : 0;
        }
        
        // Ordenar por total de infraestructura (descendente)
        uasort($grupos, function($a, $b) {
            return $b['total_infraestructura'] - $a['total_infraestructura'];
        });
        
        return [
            'puertos' => array_values($grupos),
            'timestamp' => new DateTime(),
            'parametros' => ['departamento' => $departamento]
        ];
    }
    
    // Ejecutar procedimiento (usar null para ver todas las localidades)
    $reporte = sp_reporte_infraestructura_completo($db, null);
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>📋 Resultado del Procedimiento</h4>";
    echo "<p><strong>Parámetro:</strong> Todas las localidades</p>";
    echo "<p><strong>Ejecutado:</strong> " . $reporte['timestamp']->format('Y-m-d H:i:s') . "</p>";
    
    if (empty($reporte['puertos'])) {
        echo "<p><strong>No se encontraron datos para la localidad especificada.</strong></p>";
    } else {
        foreach ($reporte['puertos'] as $dept) {
            echo "<div style='border: 1px solid #007bff; padding: 10px; margin: 5px 0;'>";
            echo "<h5>🏢 {$dept['_id']}</h5>";
            echo "<p><strong>Total Infraestructura:</strong> {$dept['total_infraestructura']}</p>";
            echo "<p><strong>Total Toneladas:</strong> " . number_format($dept['total_toneladas']) . "</p>";
            echo "<p><strong>Promedio Toneladas:</strong> " . number_format($dept['promedio_toneladas'], 2) . "</p>";
            echo "<p><strong>Infraestructura Activa:</strong> {$dept['infraestructura_activa']}</p>";
            echo "</div>";
        }
    }
    echo "</div>";
    
    // Procedimiento 2: Análisis de rendimiento por tipo
    echo "<h3>📈 Procedimiento: sp_analisis_rendimiento_tipos</h3>";
    
    function sp_analisis_rendimiento_tipos($db) {
        $collections = [
            'puertos' => $db->getPuertos()->find(),
            'aeropuertos' => $db->getAeropuertos()->find(),
            'ferroviarias' => $db->getFerroviarias()->find()
        ];
        
        $resultado = [];
        
        foreach ($collections as $tipo => $data) {
            $estados = [];
            
            foreach ($data as $item) {
                $estado = $item['ESTADO'] ?? 'Sin estado';
                
                if (!isset($estados[$estado])) {
                    $estados[$estado] = [
                        '_id' => $estado,
                        'cantidad' => 0,
                        'total_toneladas' => 0,
                        'administradores' => [],
                        'departamentos' => [],
                        'tipo_infraestructura' => $tipo
                    ];
                }
                
                $estados[$estado]['cantidad']++;
                $estados[$estado]['total_toneladas'] += 0; // Sin datos de toneladas
                
                $admin = $item['ADMINISTRADOR'] ?? 'Sin administrador';
                if (!in_array($admin, $estados[$estado]['administradores'])) {
                    $estados[$estado]['administradores'][] = $admin;
                }
                
                $dept = $item['DEPARTAMENTO'] ?? $item['LOCALIDAD'] ?? 'Sin ubicación';
                if (!in_array($dept, $estados[$estado]['departamentos'])) {
                    $estados[$estado]['departamentos'][] = $dept;
                }
            }
            
            // Agregar conteos
            foreach ($estados as &$estado) {
                $estado['total_administradores'] = count($estado['administradores']);
                $estado['total_departamentos'] = count($estado['departamentos']);
            }
            
            $resultado[$tipo] = array_values($estados);
        }
        
        return $resultado;
    }
    
    $analisis = sp_analisis_rendimiento_tipos($db);
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>📊 Análisis por Tipo de Infraestructura</h4>";
    
    foreach ($analisis as $tipo => $estados) {
        echo "<h5>🚢 " . strtoupper($tipo) . "</h5>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>Estado</th><th>Cantidad</th><th>Total Toneladas</th><th>Administradores</th><th>Departamentos</th></tr>";
        
        foreach ($estados as $estado) {
            // Verificar que $estado es un array
            if (!is_array($estado)) {
                continue;
            }
            $estadoNombre = $estado['_id'];
            $cantidad = $estado['cantidad'];
            $toneladas = number_format($estado['total_toneladas']);
            $adminCount = $estado['total_administradores'];
            $deptCount = $estado['total_departamentos'];
            
            echo "<tr>";
            echo "<td>$estadoNombre</td>";
            echo "<td>$cantidad</td>";
            echo "<td>$toneladas</td>";
            echo "<td>$adminCount</td>";
            echo "<td>$deptCount</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    echo "</div>";
    
    // Procedimiento 3: Búsqueda geográfica avanzada
    echo "<h3>🗺️ Procedimiento: sp_busqueda_geografica_avanzada</h3>";
    
    function sp_busqueda_geografica_avanzada($db, $lat_centro, $lng_centro, $radio_km = 100) {
        $puertos = $db->getPuertos()->find();
        $resultados = [];
        
        foreach ($puertos as $puerto) {
            $lat = floatval($puerto['LATITUD'] ?? 0);
            $lng = floatval($puerto['LONGITUD'] ?? 0);
            
            // Calcular distancia aproximada (fórmula haversine simplificada)
            $lat_diff = $lat - $lat_centro;
            $lng_diff = $lng - $lng_centro;
            $distancia_aprox = sqrt($lat_diff * $lat_diff + $lng_diff * $lng_diff) * 111; // 111 km por grado aprox
            
            if ($distancia_aprox <= $radio_km) {
                $resultados[] = [
                    'NOMBRE_TERMINAL' => $puerto['NOMBRE'] ?? 'Sin nombre',
                    'DEPARTAMENTO' => $puerto['LOCALIDAD'] ?? 'Sin localidad',
                    'LATITUD' => $puerto['LATITUD'] ?? '0',
                    'LONGITUD' => $puerto['LONGITUD'] ?? '0',
                    'ESTADO' => $puerto['ESTADO'] ?? 'Sin estado',
                    'distancia_km' => round($distancia_aprox, 2)
                ];
            }
        }
        
        // Ordenar por distancia
        usort($resultados, function($a, $b) {
            return $a['distancia_km'] <=> $b['distancia_km'];
        });
        
        return $resultados;
    }
    
    // Ejecutar búsqueda geográfica (centro en Lima)
    $busqueda_geo = sp_busqueda_geografica_avanzada($db, -12.0464, -77.0428, 200);
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🎯 Búsqueda Geográfica</h4>";
    echo "<p><strong>Centro:</strong> Lima (-12.0464, -77.0428)</p>";
    echo "<p><strong>Radio:</strong> 200 km</p>";
    echo "<p><strong>Resultados encontrados:</strong> " . count($busqueda_geo) . "</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>Puerto</th><th>Departamento</th><th>Coordenadas</th><th>Distancia (km)</th><th>Estado</th></tr>";
    
    foreach (array_slice($busqueda_geo, 0, 10) as $puerto) {
        $nombre = $puerto['NOMBRE_TERMINAL'];
        $dept = $puerto['DEPARTAMENTO'];
        $coords = $puerto['LATITUD'] . ', ' . $puerto['LONGITUD'];
        $distancia = $puerto['distancia_km'];
        $estado = $puerto['ESTADO'];
        
        echo "<tr>";
        echo "<td>$nombre</td>";
        echo "<td>$dept</td>";
        echo "<td>$coords</td>";
        echo "<td>$distancia</td>";
        echo "<td>$estado</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</div>";
    
    // Procedimiento 4: Estadísticas de rendimiento con benchmarking
    echo "<h3>⚡ Procedimiento: sp_benchmark_rendimiento</h3>";
    
    function sp_benchmark_rendimiento($db) {
        $inicio = microtime(true);
        
        $puertos = $db->getPuertos()->find();
        $aeropuertos = $db->getAeropuertos()->find();
        $ferroviarias = $db->getFerroviarias()->find();
        
        // Simular consulta compleja
        $resultado = [];
        $all_data = array_merge($puertos, $aeropuertos, $ferroviarias);
        
        foreach ($all_data as $item) {
            $estado = $item['ESTADO'] ?? 'Sin estado';
            $dept = $item['DEPARTAMENTO'] ?? $item['LOCALIDAD'] ?? 'Sin ubicación';
            $admin = $item['ADMINISTRADOR'] ?? 'Sin administrador';
            
            $key = $dept . '_' . $estado . '_' . $admin;
            
            if (!isset($resultado[$key])) {
                $resultado[$key] = [
                    '_id' => [
                        'departamento' => $dept,
                        'estado' => $estado,
                        'administrador' => $admin
                    ],
                    'count' => 0,
                    'total_toneladas' => 0
                ];
            }
            
            $resultado[$key]['count']++;
            $resultado[$key]['total_toneladas'] += 0; // Sin datos de toneladas en este dataset
        }
        
        // Calcular estadísticas adicionales
        foreach ($resultado as &$item) {
            $item['avg_toneladas'] = $item['count'] > 0 ? $item['total_toneladas'] / $item['count'] : 0;
            $item['min_toneladas'] = $item['total_toneladas']; // Simplificado
            $item['max_toneladas'] = $item['total_toneladas']; // Simplificado
        }
        
        // Ordenar por total de toneladas
        uasort($resultado, function($a, $b) {
            return $b['total_toneladas'] <=> $a['total_toneladas'];
        });
        
        $resultado = array_slice(array_values($resultado), 0, 20);
        
        $fin = microtime(true);
        $tiempo_ejecucion = $fin - $inicio;
        
        return [
            'datos' => $resultado,
            'tiempo_ejecucion' => $tiempo_ejecucion,
            'total_resultados' => count($resultado),
            'memoria_usada' => memory_get_usage(true)
        ];
    }
    
    $benchmark = sp_benchmark_rendimiento($db);
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>⚡ Benchmark de Rendimiento</h4>";
    echo "<p><strong>Tiempo de Ejecución:</strong> " . number_format($benchmark['tiempo_ejecucion'] * 1000, 2) . " ms</p>";
    echo "<p><strong>Resultados:</strong> {$benchmark['total_resultados']}</p>";
    echo "<p><strong>Memoria Usada:</strong> " . number_format($benchmark['memoria_usada'] / 1024 / 1024, 2) . " MB</p>";
    
    echo "<h5>📊 Top 5 Resultados:</h5>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th>Departamento</th><th>Estado</th><th>Administrador</th><th>Cantidad</th><th>Total Toneladas</th></tr>";
    
    foreach (array_slice($benchmark['datos'], 0, 5) as $item) {
        echo "<tr>";
        echo "<td>{$item['_id']['departamento']}</td>";
        echo "<td>{$item['_id']['estado']}</td>";
        echo "<td>{$item['_id']['administrador']}</td>";
        echo "<td>{$item['count']}</td>";
        echo "<td>" . number_format($item['total_toneladas']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Resumen de procedimientos
    echo "<h3>📋 Resumen de Procedimientos Implementados</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<ol>";
    echo "<li><strong>sp_reporte_infraestructura_completo</strong> - Análisis completo con agrupaciones por departamento</li>";
    echo "<li><strong>sp_analisis_rendimiento_tipos</strong> - Comparación entre tipos de infraestructura</li>";
    echo "<li><strong>sp_busqueda_geografica_avanzada</strong> - Búsqueda por proximidad geográfica</li>";
    echo "<li><strong>sp_benchmark_rendimiento</strong> - Medición de rendimiento de consultas</li>";
    echo "</ol>";
    echo "<p><strong>Características:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Parámetros de entrada</li>";
    echo "<li>✅ Lógica de negocio compleja</li>";
    echo "<li>✅ Simulación de agregaciones</li>";
    echo "<li>✅ Medición de rendimiento</li>";
    echo "<li>✅ Compatibilidad con sistema CSV</li>";
    echo "<li>✅ Validación de datos</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Error en Procedimientos</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>