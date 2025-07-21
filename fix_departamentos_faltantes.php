<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Arreglar Departamentos Faltantes</h2>";

try {
    require 'conexion.php';
    
    $db = new Database();
    $ferroviarias = $db->getFerroviarias();
    
    // Coordenadas para los departamentos faltantes
    $coordenadas_faltantes = [
        'PUNO' => [-15.8402, -70.0219],
        'ÁNCASH' => [-9.5290, -77.5286], // También usar para ?NCASH
    ];
    
    echo "<p>🔄 Arreglando departamentos faltantes...</p>";
    
    // Arreglar PUNO
    $puno_docs = $ferroviarias->find(['DEPARTAMENTO' => 'PUNO']);
    $actualizados_puno = 0;
    
    foreach ($puno_docs as $doc) {
        $coords = $coordenadas_faltantes['PUNO'];
        $lat = $coords[0] + (rand(-100, 100) / 10000);
        $lng = $coords[1] + (rand(-100, 100) / 10000);
        
        $result = $ferroviarias->updateOne(
            ['_id' => $doc['_id']],
            ['$set' => [
                'LATITUD' => $lat,
                'LONGITUD' => $lng
            ]]
        );
        
        if ($result->getModifiedCount() > 0) {
            $actualizados_puno++;
        }
    }
    
    // Arreglar ?NCASH (ÁNCASH con encoding problemático)
    $ancash_docs = $ferroviarias->find(['DEPARTAMENTO' => '?NCASH']);
    $actualizados_ancash = 0;
    
    foreach ($ancash_docs as $doc) {
        $coords = $coordenadas_faltantes['ÁNCASH'];
        $lat = $coords[0] + (rand(-100, 100) / 10000);
        $lng = $coords[1] + (rand(-100, 100) / 10000);
        
        $result = $ferroviarias->updateOne(
            ['_id' => $doc['_id']],
            ['$set' => [
                'LATITUD' => $lat,
                'LONGITUD' => $lng,
                'DEPARTAMENTO' => 'ANCASH' // Corregir también el nombre
            ]]
        );
        
        if ($result->getModifiedCount() > 0) {
            $actualizados_ancash++;
        }
    }
    
    echo "<p style='color: green;'>✅ PUNO actualizados: $actualizados_puno</p>";
    echo "<p style='color: green;'>✅ ÁNCASH actualizados: $actualizados_ancash</p>";
    
    // Verificar resultado final
    $totalConCoordenadas = $ferroviarias->countDocuments([
        'LATITUD' => ['$exists' => true, '$ne' => ''],
        'LONGITUD' => ['$exists' => true, '$ne' => '']
    ]);
    
    echo "<p>📊 Total con coordenadas: $totalConCoordenadas / 332</p>";
    
    // Mostrar departamentos únicos
    $pipeline = [
        ['$group' => [
            '_id' => '$DEPARTAMENTO',
            'total' => ['$sum' => 1],
            'con_coordenadas' => ['$sum' => [
                '$cond' => [
                    ['$and' => [
                        ['$ne' => ['$LATITUD', '']],
                        ['$ne' => ['$LONGITUD', '']]
                    ]],
                    1,
                    0
                ]
            ]]
        ]],
        ['$sort' => ['total' => -1]]
    ];
    
    $departamentos = $ferroviarias->aggregate($pipeline);
    
    echo "<h3>📊 Resumen por departamento:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Departamento</th><th>Total</th><th>Con Coordenadas</th></tr>";
    foreach ($departamentos as $dept) {
        $nombre = $dept->_id ?? 'Sin nombre';
        $total = $dept->total ?? 0;
        $coords = $dept->con_coordenadas ?? 0;
        $color = $coords == $total ? 'green' : ($coords > 0 ? 'orange' : 'red');
        echo "<tr style='color: $color;'><td>$nombre</td><td>$total</td><td>$coords</td></tr>";
    }
    echo "</table>";
    
    if ($totalConCoordenadas == 332) {
        echo "<p style='color: green;'>🎉 ¡Todas las ferroviarias tienen coordenadas!</p>";
    }
    
    echo "<p>🔄 Ahora prueba el dashboard: <a href='dashboard.html'>Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>