<?php
echo "<h2>🚂 Análisis de Tramos Ferroviarios</h2>";

try {
    require 'conexion.php';
    
    $db = new Database();
    $ferroviarias = $db->getFerroviarias();
    
    echo "<h3>1. Grupos por Nombre de Ferrocarril</h3>";
    
    // Agrupar por nombre de ferrocarril
    $pipeline = [
        ['$group' => [
            '_id' => '$NOMBRE',
            'total_segmentos' => ['$sum' => 1],
            'longitud_total' => ['$sum' => ['$toDouble' => '$LONGITUD']],
            'tramos' => ['$push' => [
                'tramo' => '$TRAMO',
                'subtramo' => '$SUBTRAMO',
                'longitud' => '$LONGITUD',
                'departamento' => '$DEPARTAMENTO',
                'lat' => '$LATITUD',
                'lng' => '$LONGITUD'
            ]]
        ]],
        ['$sort' => ['total_segmentos' => -1]]
    ];
    
    $ferrocarriles = $ferroviarias->aggregate($pipeline);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Nombre Ferrocarril</th><th>Segmentos</th><th>Longitud Total (km)</th><th>Tramos</th></tr>";
    
    foreach ($ferrocarriles as $fc) {
        $nombre = $fc->_id ?? 'Sin nombre';
        $segmentos = $fc->total_segmentos ?? 0;
        $longitud = round($fc->longitud_total ?? 0, 2);
        
        echo "<tr>";
        echo "<td><strong>$nombre</strong></td>";
        echo "<td>$segmentos</td>";
        echo "<td>$longitud km</td>";
        echo "<td>";
        
        // Mostrar tramos únicos
        $tramos_unicos = [];
        foreach ($fc->tramos as $tramo) {
            $tramo_nombre = $tramo->tramo ?? 'Sin tramo';
            if (!in_array($tramo_nombre, $tramos_unicos)) {
                $tramos_unicos[] = $tramo_nombre;
            }
        }
        
        foreach ($tramos_unicos as $tramo) {
            echo "• $tramo<br>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>2. Análisis del Ferrocarril del Centro (Principal)</h3>";
    
    $centro = $ferroviarias->find(['NOMBRE' => 'FERROCARRIL DEL CENTRO']);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Tramo</th><th>Subtramo</th><th>Departamento</th><th>Longitud</th><th>Coordenadas</th></tr>";
    
    foreach ($centro as $segmento) {
        $tramo = $segmento['TRAMO'] ?? 'Sin tramo';
        $subtramo = $segmento['SUBTRAMO'] ?? 'Sin subtramo';
        $depto = $segmento['DEPARTAMENTO'] ?? 'Sin departamento';
        $longitud = $segmento['LONGITUD'] ?? 0;
        $lat = round($segmento['LATITUD'] ?? 0, 4);
        $lng = round($segmento['LONGITUD'] ?? 0, 4);
        
        echo "<tr>";
        echo "<td>$tramo</td>";
        echo "<td>$subtramo</td>";
        echo "<td>$depto</td>";
        echo "<td>$longitud km</td>";
        echo "<td>$lat, $lng</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>3. Propuesta de Implementación</h3>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px;'>";
    echo "<h4>🎯 Opciones para conectar ferroviarias:</h4>";
    echo "<ol>";
    echo "<li><strong>Agrupar por NOMBRE</strong> - Dibujar líneas conectando segmentos del mismo ferrocarril</li>";
    echo "<li><strong>Ordenar por TRAMO</strong> - Conectar subtramos secuencialmente</li>";
    echo "<li><strong>Crear estaciones</strong> - Puntos en intersecciones importantes</li>";
    echo "<li><strong>Colores por ferrocarril</strong> - Cada línea principal con color diferente</li>";
    echo "</ol>";
    
    echo "<h4>🚂 Ferrocarriles principales a conectar:</h4>";
    echo "<ul>";
    echo "<li><strong>Ferrocarril del Centro</strong> - Lima → Junín → Cusco</li>";
    echo "<li><strong>Ferrocarril del Sur</strong> - Cusco → Arequipa</li>";
    echo "<li><strong>Ferrocarril Tacna-Arica</strong> - Frontera Chile</li>";
    echo "<li><strong>Ferrocarril Huancayo-Huancavelica</strong> - Sierra central</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<p><strong>¿Quieres que implemente las líneas conectadas?</strong></p>";
    echo "<p>Esto hará que las ferroviarias se vean como <strong>líneas reales</strong> en lugar de puntos dispersos.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>