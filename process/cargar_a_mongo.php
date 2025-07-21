<?php
require 'conexion.php';

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

$db = new Database();

// Cargar puertos
$puertos = csvToArray('puertos_limpio.csv');
$puertosCol = $db->getPuertos();
$puertosCol->drop(); // Limpiar colección antes de insertar
if (!empty($puertos)) {
    $puertosCol->insertMany($puertos);
    echo "Puertos insertados: " . count($puertos) . "\n";
    // Mostrar los primeros 3 documentos insertados
    $primeros = $puertosCol->find([], ['limit' => 3]);
    foreach ($primeros as $doc) {
        print_r($doc);
    }
}

// Cargar aeropuertos
$aeropuertos = csvToArray('aeropuertos_limpio.csv');
$aeropuertosCol = $db->getAeropuertos();
$aeropuertosCol->drop(); // Limpiar colección antes de insertar
if (!empty($aeropuertos)) {
    $aeropuertosCol->insertMany($aeropuertos);
    echo "Aeropuertos insertados: " . count($aeropuertos) . "\n";
}

echo "Carga completada.\n"; 