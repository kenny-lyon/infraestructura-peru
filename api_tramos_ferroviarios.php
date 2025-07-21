<?php
header('Content-Type: application/json');
require 'conexion.php';

try {
    $db = new Database();
    $ferroviarias = $db->getFerroviarias();
    
    $nombre_ferrocarril = $_GET['nombre'] ?? '';
    $tramo_especifico = $_GET['tramo'] ?? '';
    $codigo_ferroviario = $_GET['codigo'] ?? '';
    
    if (empty($nombre_ferrocarril) && empty($codigo_ferroviario)) {
        echo json_encode(['error' => 'Nombre de ferrocarril o código requerido']);
        exit;
    }
    
    // Construir filtro
    $filtro = ['NOMBRE' => $nombre_ferrocarril];
    
    // Si se especifica un tramo, filtrar solo ese tramo
    if (!empty($tramo_especifico)) {
        $filtro['TRAMO'] = $tramo_especifico;
    }
    
    // Buscar segmentos del ferrocarril (y tramo específico si se especifica)
    $todos_segmentos = $ferroviarias->find();
    $segmentos = [];
    
    foreach ($todos_segmentos as $segmento) {
        $coincide = true;
        
        // Verificar código ferroviario (más preciso)
        if (!empty($codigo_ferroviario)) {
            $codigo_seg = strtolower($segmento['CODIGO_FERROVIARIO'] ?? '');
            $codigo_buscar = strtolower($codigo_ferroviario);
            if ($codigo_seg !== $codigo_buscar) {
                $coincide = false;
            }
        }
        
        // Verificar nombre del ferrocarril (si no hay código o como filtro adicional)
        if ($coincide && !empty($nombre_ferrocarril)) {
            $nombre_seg = strtolower($segmento['NOMBRE'] ?? '');
            $nombre_buscar = strtolower($nombre_ferrocarril);
            if (strpos($nombre_seg, $nombre_buscar) === false) {
                $coincide = false;
            }
        }
        
        // Verificar tramo específico si se proporciona
        if ($coincide && !empty($tramo_especifico)) {
            $tramo_seg = strtolower($segmento['TRAMO'] ?? '');
            $tramo_buscar = strtolower($tramo_especifico);
            if (strpos($tramo_seg, $tramo_buscar) === false) {
                $coincide = false;
            }
        }
        
        if ($coincide) {
            $segmentos[] = $segmento;
        }
    }
    
    $tramo_data = [
        'nombre' => $nombre_ferrocarril,
        'tramo' => $tramo_especifico,
        'puntos' => [],
        'info' => []
    ];
    
    foreach ($segmentos as $segmento) {
        $lat = floatval($segmento['LATITUD'] ?? 0);
        $lng = floatval($segmento['LONGITUD'] ?? 0);
        
        if ($lat != 0 && $lng != 0) {
            $tramo_data['puntos'][] = [$lat, $lng];
            $tramo_data['info'][] = [
                'tramo' => $segmento['TRAMO'] ?? '',
                'subtramo' => $segmento['SUBTRAMO'] ?? '',
                'departamento' => $segmento['DEPARTAMENTO'] ?? '',
                'longitud' => floatval($segmento['LONGITUD'] ?? 0),
                'estado' => $segmento['ESTADO'] ?? '',
                'titularidad' => $segmento['TITULARIDAD'] ?? '',
                'administrador' => $segmento['ADMINISTRADOR'] ?? '',
                'lat' => $lat,
                'lng' => $lng
            ];
        }
    }
    
    // Mejorar el orden de los puntos para crear un tramo más realista
    if (count($tramo_data['puntos']) > 1) {
        // Crear una matriz de puntos con sus índices
        $puntos_con_indice = [];
        foreach ($tramo_data['puntos'] as $index => $punto) {
            $puntos_con_indice[] = [
                'punto' => $punto,
                'info' => $tramo_data['info'][$index],
                'index' => $index
            ];
        }
        
        // Ordenar puntos de manera geográfica lógica
        // Para tramos como Arequipa-Juliaca, ordenar por latitud (de sur a norte)
        usort($puntos_con_indice, function($a, $b) {
            $latA = $a['punto'][0];
            $latB = $b['punto'][0];
            
            // Ordenar por latitud (de sur a norte, menor a mayor)
            return $latA <=> $latB;
        });
        
        // Reconstruir arrays ordenados
        $tramo_data['puntos'] = [];
        $tramo_data['info'] = [];
        
        foreach ($puntos_con_indice as $item) {
            $tramo_data['puntos'][] = $item['punto'];
            $tramo_data['info'][] = $item['info'];
        }
        
        // Si tenemos muchos puntos, tomar solo algunos puntos clave para evitar solapamiento
        if (count($tramo_data['puntos']) > 5) {
            $puntos_seleccionados = [];
            $info_seleccionada = [];
            $total = count($tramo_data['puntos']);
            
            // Tomar primer punto, último punto y algunos intermedios
            $indices_seleccionados = [0, $total - 1]; // Primer y último punto
            
            // Agregar algunos puntos intermedios
            $num_intermedios = min(3, $total - 2);
            for ($i = 1; $i <= $num_intermedios; $i++) {
                $indice = intval(($total - 1) * $i / ($num_intermedios + 1));
                if (!in_array($indice, $indices_seleccionados)) {
                    $indices_seleccionados[] = $indice;
                }
            }
            
            sort($indices_seleccionados);
            
            foreach ($indices_seleccionados as $i) {
                $puntos_seleccionados[] = $tramo_data['puntos'][$i];
                $info_seleccionada[] = $tramo_data['info'][$i];
            }
            
            $tramo_data['puntos'] = $puntos_seleccionados;
            $tramo_data['info'] = $info_seleccionada;
        }
    }
    
    echo json_encode($tramo_data, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>