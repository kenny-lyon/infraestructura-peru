<?php
header('Content-Type: application/json');
require 'conexion.php';

$db = new Database();
$type = $_GET['type'] ?? 'all'; // 'puerto', 'aeropuerto', 'ferroviaria', 'all'
$search = $_GET['search'] ?? '';
$stat = $_GET['stat'] ?? '';

$result = [];

if ($search !== '') {
    // Búsqueda por nombre o código
    $searchLower = strtolower($search);
    $colls = [];
    
    if ($type === 'puerto' || $type === 'all') {
        $puertos = $db->getPuertos()->find();
        foreach ($puertos as $doc) {
            $nombre = strtolower($doc['NOMBRE'] ?? '');
            $codigo = strtolower($doc['CODIGO_OACI'] ?? '');
            if (strpos($nombre, $searchLower) !== false || strpos($codigo, $searchLower) !== false) {
                $doc['tipo'] = 'puerto';
                unset($doc['_id']);
                $result[] = $doc;
            }
        }
    }
    
    if ($type === 'aeropuerto' || $type === 'all') {
        $aeropuertos = $db->getAeropuertos()->find();
        foreach ($aeropuertos as $doc) {
            $nombre = strtolower($doc['NOMBRE'] ?? '');
            $codigo = strtolower($doc['CODIGO_OACI'] ?? '');
            if (strpos($nombre, $searchLower) !== false || strpos($codigo, $searchLower) !== false) {
                $doc['tipo'] = 'aeropuerto';
                unset($doc['_id']);
                $result[] = $doc;
            }
        }
    }
    
    if ($type === 'ferroviaria' || $type === 'all') {
        $ferroviarias = $db->getFerroviarias()->find();
        foreach ($ferroviarias as $doc) {
            $nombre = strtolower($doc['NOMBRE'] ?? '');
            $codigo = strtolower($doc['CODIGO_FERROVIARIO'] ?? '');
            if (strpos($nombre, $searchLower) !== false || strpos($codigo, $searchLower) !== false) {
                $doc['tipo'] = 'ferroviaria';
                unset($doc['_id']);
                $result[] = $doc;
            }
        }
    }
    
    // Limitar a 30 resultados
    $result = array_slice($result, 0, 30);
}

if ($stat !== '') {
    switch ($stat) {
        case 'departamentos':
            $departamentos = [];
            $puertos = $db->getPuertos()->find();
            $aeropuertos = $db->getAeropuertos()->find();
            $ferroviarias = $db->getFerroviarias()->find();
            
            foreach ($puertos as $p) {
                $dept = $p['DEPARTAMENTO'] ?? $p['LOCALIDAD'] ?? '';
                if ($dept) $departamentos[$dept] = ($departamentos[$dept] ?? 0) + 1;
            }
            foreach ($aeropuertos as $a) {
                $dept = $a['DEPARTAMENTO'] ?? '';
                if ($dept) $departamentos[$dept] = ($departamentos[$dept] ?? 0) + 1;
            }
            foreach ($ferroviarias as $f) {
                $dept = $f['DEPARTAMENTO'] ?? '';
                if ($dept) $departamentos[$dept] = ($departamentos[$dept] ?? 0) + 1;
            }
            
            $result = [];
            foreach ($departamentos as $dept => $count) {
                $result[] = ['departamento' => $dept, 'total' => $count];
            }
            break;
            
        case 'tipos':
            $result = [
                ['tipo' => 'Puertos', 'total' => count($db->getPuertos()->find())],
                ['tipo' => 'Aeropuertos', 'total' => count($db->getAeropuertos()->find())],
                ['tipo' => 'Ferroviarias', 'total' => count($db->getFerroviarias()->find())]
            ];
            break;
            
        case 'estados':
            $estados = [];
            $puertos = $db->getPuertos()->find();
            $aeropuertos = $db->getAeropuertos()->find();
            $ferroviarias = $db->getFerroviarias()->find();
            
            foreach (array_merge($puertos, $aeropuertos, $ferroviarias) as $item) {
                $estado = $item['ESTADO'] ?? 'Sin datos';
                $estados[$estado] = ($estados[$estado] ?? 0) + 1;
            }
            
            $result = [];
            foreach ($estados as $estado => $count) {
                $result[] = ['estado' => $estado, 'total' => $count];
            }
            break;
            
        case 'administradores':
            $administradores = [];
            $puertos = $db->getPuertos()->find();
            $aeropuertos = $db->getAeropuertos()->find();
            $ferroviarias = $db->getFerroviarias()->find();
            
            foreach (array_merge($puertos, $aeropuertos, $ferroviarias) as $item) {
                $admin = $item['ADMINISTRADOR'] ?? 'Sin datos';
                $administradores[$admin] = ($administradores[$admin] ?? 0) + 1;
            }
            
            // Tomar solo los top 10
            arsort($administradores);
            $administradores = array_slice($administradores, 0, 10, true);
            
            $result = [];
            foreach ($administradores as $admin => $count) {
                $result[] = ['administrador' => $admin, 'total' => $count];
            }
            break;
            
        case 'coordenadas':
            $conCoords = 0;
            $sinCoords = 0;
            
            $puertos = $db->getPuertos()->find();
            $aeropuertos = $db->getAeropuertos()->find();
            $ferroviarias = $db->getFerroviarias()->find();
            
            foreach (array_merge($puertos, $aeropuertos, $ferroviarias) as $item) {
                if (!empty($item['LATITUD']) && !empty($item['LONGITUD'])) {
                    $conCoords++;
                } else {
                    $sinCoords++;
                }
            }
            
            $result = [
                ['categoria' => 'Con coordenadas', 'total' => $conCoords],
                ['categoria' => 'Sin coordenadas', 'total' => $sinCoords]
            ];
            break;
    }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>