<?php
require '../conexion.php';

function csvToArray($filename) {
    $rows = [];
    if (($handle = fopen($filename, 'r')) !== false) {
        $header = fgetcsv($handle, 0, ',');
        $lineNumber = 2; // Empezar desde la línea 2
        
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if (count($data) == count($header)) {
                $row = array_combine($header, $data);
                
                // Limpiar y convertir datos
                $cleanRow = [];
                foreach ($row as $key => $value) {
                    // Limpiar encoding
                    $cleanKey = trim($key);
                    $cleanValue = trim($value);
                    
                    // Convertir longitud a número
                    if ($cleanKey === 'LONGITUD' && !empty($cleanValue)) {
                        $cleanValue = floatval(str_replace(',', '.', $cleanValue));
                    }
                    
                    // Agregar campo tipo
                    $cleanRow[$cleanKey] = $cleanValue;
                }
                
                $cleanRow['tipo'] = 'ferroviaria';
                $rows[] = $cleanRow;
            }
            $lineNumber++;
        }
        fclose($handle);
    }
    return $rows;
}

echo "<h2>🚂 Carga de Datos Ferroviarios a MongoDB</h2>";

try {
    $db = new Database();
    
    // Cargar datos desde CSV
    $csvFile = '../ferroviarias/ferroviarias_limpio.csv';
    
    if (!file_exists($csvFile)) {
        echo "<p style='color: red;'>❌ No se encontró el archivo: $csvFile</p>";
        exit;
    }
    
    echo "<p>📂 Leyendo archivo: $csvFile</p>";
    $ferroviarias = csvToArray($csvFile);
    
    echo "<p>📊 Registros leídos: " . count($ferroviarias) . "</p>";
    
    if (empty($ferroviarias)) {
        echo "<p style='color: red;'>❌ No se pudieron leer los datos</p>";
        exit;
    }
    
    // Obtener colección ferroviarias
    $ferroviariasCol = $db->getFerroviarias();
    
    // Limpiar colección antes de insertar
    echo "<p>🧹 Limpiando colección ferroviarias...</p>";
    $ferroviariasCol->drop();
    
    // Insertar datos
    echo "<p>📥 Insertando datos...</p>";
    $result = $ferroviariasCol->insertMany($ferroviarias);
    
    echo "<p style='color: green;'>✅ Ferroviarias insertadas: " . $result->getInsertedCount() . "</p>";
    
    // Mostrar estadísticas
    echo "<h3>📊 Estadísticas de los datos cargados:</h3>";
    
    // Contar por departamento
    $pipeline = [
        ['$group' => [
            '_id' => '$DEPARTAMENTO',
            'total' => ['$sum' => 1],
            'longitud_total' => ['$sum' => '$LONGITUD']
        ]],
        ['$sort' => ['total' => -1]]
    ];
    
    $deptos = $ferroviariasCol->aggregate($pipeline);
    echo "<h4>Por Departamento:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Departamento</th><th>Cantidad</th><th>Longitud Total (km)</th></tr>";
    foreach ($deptos as $dept) {
        $longitud = round($dept->longitud_total ?? 0, 2);
        echo "<tr><td>{$dept->_id}</td><td>{$dept->total}</td><td>{$longitud}</td></tr>";
    }
    echo "</table>";
    
    // Contar por electrificación
    $pipeline = [
        ['$group' => [
            '_id' => '$ELECTRIFICACION',
            'total' => ['$sum' => 1]
        ]],
        ['$sort' => ['total' => -1]]
    ];
    
    $electrificacion = $ferroviariasCol->aggregate($pipeline);
    echo "<h4>Por Electrificación:</h4>";
    echo "<ul>";
    foreach ($electrificacion as $item) {
        echo "<li>{$item->_id}: {$item->total}</li>";
    }
    echo "</ul>";
    
    // Contar por estado
    $pipeline = [
        ['$group' => [
            '_id' => '$ESTADO',
            'total' => ['$sum' => 1]
        ]],
        ['$sort' => ['total' => -1]]
    ];
    
    $estado = $ferroviariasCol->aggregate($pipeline);
    echo "<h4>Por Estado:</h4>";
    echo "<ul>";
    foreach ($estado as $item) {
        echo "<li>{$item->_id}: {$item->total}</li>";
    }
    echo "</ul>";
    
    // Contar por titularidad
    $pipeline = [
        ['$group' => [
            '_id' => '$TITULARIDAD',
            'total' => ['$sum' => 1]
        ]],
        ['$sort' => ['total' => -1]]
    ];
    
    $titularidad = $ferroviariasCol->aggregate($pipeline);
    echo "<h4>Por Titularidad:</h4>";
    echo "<ul>";
    foreach ($titularidad as $item) {
        echo "<li>{$item->_id}: {$item->total}</li>";
    }
    echo "</ul>";
    
    // Mostrar primeros registros
    echo "<h3>🔍 Primeros 3 registros insertados:</h3>";
    $primeros = $ferroviariasCol->find([], ['limit' => 3]);
    $count = 1;
    foreach ($primeros as $doc) {
        echo "<h4>Registro $count:</h4>";
        echo "<ul>";
        foreach ($doc as $key => $value) {
            if ($key !== '_id' && $key !== 'tipo') {
                echo "<li><strong>$key:</strong> $value</li>";
            }
        }
        echo "</ul>";
        $count++;
    }
    
    echo "<p style='color: green;'>🎉 ¡Carga completada exitosamente!</p>";
    echo "<p>🔄 Siguiente paso: <a href='../test_ferroviarias.php'>Probar API ferroviarias</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>