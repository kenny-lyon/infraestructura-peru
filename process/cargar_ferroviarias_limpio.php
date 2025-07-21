<?php
require '../conexion.php';

function csvToArray($filename) {
    $rows = [];
    if (($handle = fopen($filename, 'r')) !== false) {
        $header = fgetcsv($handle, 0, ',');
        
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if (count($data) == count($header)) {
                $row = array_combine($header, $data);
                
                // Limpiar y convertir datos
                $cleanRow = [];
                foreach ($row as $key => $value) {
                    $cleanKey = trim($key);
                    $cleanValue = trim($value);
                    
                    // Convertir longitud a número
                    if ($cleanKey === 'LONGITUD' && !empty($cleanValue)) {
                        $cleanValue = floatval(str_replace(',', '.', $cleanValue));
                    }
                    
                    // Limpiar caracteres especiales
                    $cleanValue = mb_convert_encoding($cleanValue, 'UTF-8', 'UTF-8');
                    
                    $cleanRow[$cleanKey] = $cleanValue;
                }
                
                $cleanRow['tipo'] = 'ferroviaria';
                $rows[] = $cleanRow;
            }
        }
        fclose($handle);
    }
    return $rows;
}

echo "<h2>🚂 Carga de Datos Ferroviarios (Limpio) a MongoDB</h2>";

try {
    $db = new Database();
    
    // Usar archivo limpio
    $csvFile = '../data/ferroviarias_limpio_utf8.csv';
    
    if (!file_exists($csvFile)) {
        echo "<p style='color: red;'>❌ No se encontró el archivo: $csvFile</p>";
        echo "<p>🔄 Primero ejecuta: <a href='limpiar_encoding.php'>limpiar_encoding.php</a></p>";
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
    
    // Mostrar estadísticas básicas
    echo "<h3>📊 Estadísticas básicas:</h3>";
    echo "<p>Total de registros: " . $ferroviariasCol->countDocuments() . "</p>";
    
    echo "<p style='color: green;'>🎉 ¡Carga completada exitosamente!</p>";
    echo "<p>🔄 Siguiente paso: <a href='../test_ferroviarias.php'>Probar API ferroviarias</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>