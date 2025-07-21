<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla, solo devolver JSON

try {
    require 'conexion.php';
} catch (Exception $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$db = new Database();

function getCollection($tipo, $db) {
    if ($tipo === 'puerto') return $db->getPuertos();
    if ($tipo === 'aeropuerto') return $db->getAeropuertos();
    if ($tipo === 'ferroviaria') return $db->getFerroviarias();
    return null;
}

function getId($data) {
    if (isset($data['_id'])) return $data['_id'];
    if (isset($data['id'])) return $data['id'];
    return null;
}

if ($method === 'GET') {
    $tipo = $_GET['tipo'] ?? '';
    $col = getCollection($tipo, $db);
    if (!$col) {
        echo json_encode(['error' => 'Tipo inválido']);
        exit;
    }
    $docs = $col->find();
    // Asegurar que todos los documentos tengan un ID string
    foreach ($docs as &$doc) {
        if (!isset($doc['_id'])) {
            $doc['_id'] = uniqid();
        }
        
        // Convertir ObjectId a string
        if (is_object($doc['_id'])) {
            if (method_exists($doc['_id'], '__toString')) {
                $doc['_id'] = (string)$doc['_id'];
            } elseif (method_exists($doc['_id'], 'toArray')) {
                $arrayId = $doc['_id']->toArray();
                $doc['_id'] = $arrayId['$oid'] ?? (string)$doc['_id'];
            } else {
                $doc['_id'] = (string)$doc['_id'];
            }
        } elseif (is_array($doc['_id']) && isset($doc['_id']['$oid'])) {
            $doc['_id'] = $doc['_id']['$oid'];
        } else {
            $doc['_id'] = (string)$doc['_id'];
        }
    }
    echo json_encode($docs);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$tipo = $input['tipo'] ?? ($_GET['tipo'] ?? '');
$col = getCollection($tipo, $db);
if (!$col) {
    echo json_encode(['error' => 'Tipo inválido']);
    exit;
}

if ($method === 'POST') {
    unset($input['tipo']);
    // Validación básica
    if (empty($input)) {
        echo json_encode(['error' => 'Datos vacíos']);
        exit;
    }
    
    // No establecer _id manualmente, MongoDB lo generará automáticamente
    unset($input['_id']);
    
    // Todos los campos son opcionales - sin validación de campos requeridos
    
    // Inserción real
    $result = $col->insertOne($input);
    if ($result) {
        echo json_encode(['success' => true, 'id' => (string)$result]);
    } else {
        echo json_encode(['error' => 'Error al crear registro']);
    }
    exit;
}

if ($method === 'PUT') {
    $id = $input['_id'] ?? '';
    if (empty($id)) {
        echo json_encode(['error' => 'ID requerido']);
        exit;
    }
    
    unset($input['tipo'], $input['_id']);
    
    // Convertir string ID a ObjectId si es necesario
    try {
        $filter = ['_id' => new MongoDB\BSON\ObjectId($id)];
    } catch (Exception $e) {
        echo json_encode(['error' => 'ID inválido: ' . $e->getMessage()]);
        exit;
    }
    
    // Actualización real
    $result = $col->updateOne($filter, $input);
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al actualizar registro']);
    }
    exit;
}

if ($method === 'DELETE') {
    $id = $input['_id'] ?? '';
    if (empty($id)) {
        echo json_encode(['error' => 'ID requerido']);
        exit;
    }
    
    // Convertir string ID a ObjectId si es necesario
    try {
        $filter = ['_id' => new MongoDB\BSON\ObjectId($id)];
    } catch (Exception $e) {
        echo json_encode(['error' => 'ID inválido para eliminar: ' . $e->getMessage()]);
        exit;
    }
    
    // Eliminación real
    $result = $col->deleteOne($filter);
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al eliminar registro']);
    }
    exit;
}

echo json_encode(['error' => 'Método no soportado']);
?>