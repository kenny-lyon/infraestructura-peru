<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🗺️ Agregar Coordenadas a Ferroviarias</h2>";

// Coordenadas por departamento (capitales)
$coordenadas_departamentos = [
    'LIMA' => [-12.0464, -77.0428],
    'JUNIN' => [-12.0678, -75.2102],
    'CUSCO' => [-13.5319, -71.9675],
    'AREQUIPA' => [-16.4090, -71.5375],
    'TACNA' => [-18.0147, -70.2533],
    'HUANCAVELICA' => [-12.7878, -74.9736],
    'ANCASH' => [-9.5290, -77.5286],
    'PASCO' => [-10.6929, -76.2681],
    'HUANUCO' => [-9.9306, -76.2422],
    'AYACUCHO' => [-13.1586, -74.2236],
    'APURIMAC' => [-13.6339, -72.8814],
    'MOQUEGUA' => [-17.1948, -70.9356],
    'CAJAMARCA' => [-7.1611, -78.5136],
    'PIURA' => [-5.1945, -80.6328],
    'LORETO' => [-3.7437, -73.2516],
    'CALLAO' => [-12.0621, -77.1417],
    'VENTANILLA' => [-11.8700, -77.1500],
    'CHIMBOTE' => [-9.0853, -78.5783],
    'TRUJILLO' => [-8.1090, -79.0215],
    'CHICLAYO' => [-6.7714, -79.8374],
    'IQUITOS' => [-3.7437, -73.2516],
    'PUCALLPA' => [-8.3791, -74.5539],
    'HUARAZ' => [-9.5290, -77.5286],
    'ICA' => [-14.0678, -75.7286],
    'TUMBES' => [-3.5703, -80.4511]
];

try {
    require 'conexion.php';
    
    $db = new Database();
    $ferroviarias = $db->getFerroviarias();
    
    echo "<p>🔄 Procesando ferroviarias...</p>";
    
    // Obtener todas las ferroviarias
    $docs = $ferroviarias->find();
    $actualizados = 0;
    $errores = 0;
    
    foreach ($docs as $doc) {
        $departamento = strtoupper($doc['DEPARTAMENTO'] ?? '');
        
        if (isset($coordenadas_departamentos[$departamento])) {
            $coords = $coordenadas_departamentos[$departamento];
            
            // Agregar un poco de variación aleatoria para evitar superposición
            $lat = $coords[0] + (rand(-100, 100) / 10000); // ±0.01 grados
            $lng = $coords[1] + (rand(-100, 100) / 10000);
            
            try {
                $result = $ferroviarias->updateOne(
                    ['_id' => $doc['_id']],
                    ['$set' => [
                        'LATITUD' => $lat,
                        'LONGITUD' => $lng
                    ]]
                );
                
                if ($result->getModifiedCount() > 0) {
                    $actualizados++;
                }
                
            } catch (Exception $e) {
                $errores++;
                echo "<p style='color: red;'>Error actualizando {$doc['NOMBRE']}: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Departamento no encontrado: $departamento</p>";
        }
    }
    
    echo "<p style='color: green;'>✅ Actualizados: $actualizados documentos</p>";
    echo "<p style='color: red;'>❌ Errores: $errores documentos</p>";
    
    // Verificar resultado
    $conCoordenadas = $ferroviarias->countDocuments([
        'LATITUD' => ['$exists' => true, '$ne' => ''],
        'LONGITUD' => ['$exists' => true, '$ne' => '']
    ]);
    
    echo "<p>📊 Ferroviarias con coordenadas: $conCoordenadas</p>";
    
    // Mostrar muestra
    echo "<h3>🔍 Muestra de coordenadas agregadas:</h3>";
    $muestra = $ferroviarias->find([
        'LATITUD' => ['$exists' => true],
        'LONGITUD' => ['$exists' => true]
    ], ['limit' => 5]);
    
    echo "<table border='1'>";
    echo "<tr><th>Nombre</th><th>Departamento</th><th>Latitud</th><th>Longitud</th></tr>";
    foreach ($muestra as $doc) {
        $nombre = $doc['NOMBRE'] ?? 'Sin nombre';
        $depto = $doc['DEPARTAMENTO'] ?? 'Sin departamento';
        $lat = $doc['LATITUD'] ?? 'Sin latitud';
        $lng = $doc['LONGITUD'] ?? 'Sin longitud';
        echo "<tr><td>$nombre</td><td>$depto</td><td>$lat</td><td>$lng</td></tr>";
    }
    echo "</table>";
    
    if ($actualizados > 0) {
        echo "<p style='color: green;'>🎉 ¡Coordenadas agregadas exitosamente!</p>";
        echo "<p>🔄 Ahora prueba el dashboard: <a href='dashboard.html'>Dashboard</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>