<?php
require_once 'vendor/autoload.php';

// Cargar variables de entorno
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$mongoUri = getenv('MONGODB_URI');
$dbName = getenv('MONGODB_DATABASE') ?: 'proyecto_infraestructura';

if (!$mongoUri) {
    die("Error: MONGODB_URI no está configurado\n");
}

try {
    $client = new MongoDB\Client($mongoUri);
    $db = $client->selectDatabase($dbName);
    
    echo "Conectado a Atlas exitosamente\n";
    
    // Función para leer CSV
    function csvToArray($filename) {
        $rows = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            $header = fgetcsv($handle, 0, ',');
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = array_combine($header, $data);
            }
            fclose($handle);
        }
        return $rows;
    }
    
    // Cargar puertos
    echo "Cargando puertos...\n";
    $puertos = csvToArray('data/puertos_limpio.csv');
    if (!empty($puertos)) {
        $puertosCol = $db->selectCollection('puertos');
        $puertosCol->deleteMany([]); // Limpiar colección
        $puertosCol->insertMany($puertos);
        echo "Puertos insertados: " . count($puertos) . "\n";
    } else {
        echo "No se encontraron datos de puertos\n";
    }
    
    // Cargar aeropuertos
    echo "Cargando aeropuertos...\n";
    $aeropuertos = csvToArray('data/aeropuertos_limpio.csv');
    if (!empty($aeropuertos)) {
        $aeropuertosCol = $db->selectCollection('aeropuertos');
        $aeropuertosCol->deleteMany([]); // Limpiar colección
        $aeropuertosCol->insertMany($aeropuertos);
        echo "Aeropuertos insertados: " . count($aeropuertos) . "\n";
    } else {
        echo "No se encontraron datos de aeropuertos\n";
    }
    
    // Cargar ferroviarias
    echo "Cargando ferroviarias...\n";
    $ferroviarias = csvToArray('data/ferroviarias_limpio_utf8.csv');
    if (!empty($ferroviarias)) {
        $ferroviariasCol = $db->selectCollection('ferroviarias');
        $ferroviariasCol->deleteMany([]); // Limpiar colección
        $ferroviariasCol->insertMany($ferroviarias);
        echo "Ferroviarias insertadas: " . count($ferroviarias) . "\n";
    } else {
        echo "No se encontraron datos de ferroviarias\n";
    }
    
    echo "\n✅ Carga completada exitosamente en Atlas!\n";
    
    // Verificar datos
    echo "\nVerificando datos cargados:\n";
    echo "- Puertos: " . $db->selectCollection('puertos')->countDocuments() . "\n";
    echo "- Aeropuertos: " . $db->selectCollection('aeropuertos')->countDocuments() . "\n";
    echo "- Ferroviarias: " . $db->selectCollection('ferroviarias')->countDocuments() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>