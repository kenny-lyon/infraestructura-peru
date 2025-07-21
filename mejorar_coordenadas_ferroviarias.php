<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>📍 Mejorar Coordenadas Ferroviarias</h2>";

// Coordenadas más precisas para ferrocarriles específicos
$coordenadas_ferrocarriles = [
    'FERROCARRIL DEL CENTRO' => [
        'CALLAO - LA OROYA' => [
            'CALLAO- DV. CAJAMARQUILLA' => [-12.0532, -77.1044], // Callao puerto
            'DV. CAJAMARQUILLA - CHOSICA' => [-11.9444, -76.7081], // Chosica
            'CHOSICA - RICARDO PALMA' => [-11.9270, -76.6454], // Ricardo Palma
            'RICARDO PALMA - MATUCANA' => [-11.8428, -76.3973], // Matucana
            'MATUCANA - LA OROYA' => [-11.5214, -75.8995], // La Oroya
        ],
        'LA OROYA - HUANCAYO' => [
            'LA OROYA - JAUJA' => [-11.7752, -75.4736], // Jauja
            'JAUJA - HUANCAYO' => [-12.0678, -75.2102], // Huancayo
        ],
        'HUANCAYO - HUANCAVELICA' => [
            'HUANCAYO - IZCUCHACA' => [-12.1833, -74.9833], // Izcuchaca
            'IZCUCHACA - HUANCAVELICA' => [-12.7878, -74.9736], // Huancavelica
        ]
    ],
    'FERROCARRIL DEL SUR' => [
        'CUSCO - AREQUIPA' => [
            'CUSCO - SICUANI' => [-13.9333, -71.2333], // Sicuani
            'SICUANI - JULIACA' => [-15.4992, -70.1427], // Juliaca
            'JULIACA - AREQUIPA' => [-16.4090, -71.5375], // Arequipa
        ]
    ],
    'FERROCARRIL TACNA-ARICA' => [
        'TACNA - ARICA' => [
            'TACNA - FRONTERA' => [-18.0147, -70.2533], // Tacna
            'FRONTERA - ARICA' => [-18.4783, -70.3126], // Arica (Chile)
        ]
    ]
];

// Coordenadas específicas para ferrocarriles mineros
$coordenadas_mineras = [
    'LIMA' => [
        'FERROCARRIL CAJAMARQUILLA - SANTA CLARA' => [-11.9167, -76.7833], // Cajamarquilla
        'FERROCARRIL SHOUGANG' => [-12.5833, -76.0833], // Marcona
    ],
    'JUNIN' => [
        'FERROCARRIL CARIPA - CONDORCOCHA' => [-11.0000, -76.0000], // Zona minera Junín
        'FERROCARRIL MILPO' => [-10.7500, -76.7500], // Cerro de Pasco
    ],
    'ANCASH' => [
        'FERROCARRIL ANTAMINA' => [-9.5500, -77.0500], // Antamina
        'FERROCARRIL PIERINA' => [-9.3500, -77.6500], // Pierina
    ],
    'CUSCO' => [
        'FERROCARRIL MACHU PICCHU' => [-13.1631, -72.5450], // Machu Picchu
        'FERROCARRIL OLLANTAYTAMBO' => [-13.2594, -72.2656], // Ollantaytambo
    ],
    'AREQUIPA' => [
        'FERROCARRIL CERRO VERDE' => [-16.5500, -71.6000], // Cerro Verde
        'FERROCARRIL TOQUEPALA' => [-17.2667, -70.6000], // Toquepala
    ],
    'MOQUEGUA' => [
        'FERROCARRIL CUAJONE' => [-17.0000, -70.7500], // Cuajone
        'FERROCARRIL TOQUEPALA' => [-17.2667, -70.6000], // Toquepala
    ],
    'TACNA' => [
        'FERROCARRIL TACNA-ARICA' => [-18.0147, -70.2533], // Tacna
    ],
    'PUNO' => [
        'FERROCARRIL PUNO-CUSCO' => [-15.8402, -70.0219], // Puno ciudad (no en el lago)
    ]
];

try {
    require 'conexion.php';
    
    $db = new Database();
    $ferroviarias = $db->getFerroviarias();
    
    echo "<p>🔄 Mejorando coordenadas ferroviarias...</p>";
    
    $actualizados = 0;
    $docs = $ferroviarias->find();
    
    foreach ($docs as $doc) {
        $nombre = $doc['NOMBRE'] ?? '';
        $tramo = $doc['TRAMO'] ?? '';
        $subtramo = $doc['SUBTRAMO'] ?? '';
        $departamento = $doc['DEPARTAMENTO'] ?? '';
        
        $nueva_lat = null;
        $nueva_lng = null;
        
        // Buscar coordenadas específicas por ferrocarril y tramo
        if (isset($coordenadas_ferrocarriles[$nombre][$tramo][$subtramo])) {
            $coords = $coordenadas_ferrocarriles[$nombre][$tramo][$subtramo];
            $nueva_lat = $coords[0];
            $nueva_lng = $coords[1];
        }
        // Buscar coordenadas por departamento y nombre
        elseif (isset($coordenadas_mineras[$departamento])) {
            foreach ($coordenadas_mineras[$departamento] as $ferrocarril => $coords) {
                if (strpos($nombre, substr($ferrocarril, 12)) !== false) { // Quitar "FERROCARRIL "
                    $nueva_lat = $coords[0] + (rand(-50, 50) / 10000); // Pequeña variación
                    $nueva_lng = $coords[1] + (rand(-50, 50) / 10000);
                    break;
                }
            }
        }
        
        // Si encontramos coordenadas mejores, actualizar
        if ($nueva_lat && $nueva_lng) {
            try {
                $result = $ferroviarias->updateOne(
                    ['_id' => $doc['_id']],
                    ['$set' => [
                        'LATITUD' => $nueva_lat,
                        'LONGITUD' => $nueva_lng
                    ]]
                );
                
                if ($result->getModifiedCount() > 0) {
                    $actualizados++;
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error actualizando {$nombre}: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<p style='color: green;'>✅ Coordenadas mejoradas: $actualizados documentos</p>";
    
    // Verificar algunos casos problemáticos
    echo "<h3>🔍 Verificación de casos problemáticos:</h3>";
    
    // Buscar ferroviarias en Puno
    $puno_ferroviarias = $ferroviarias->find(['DEPARTAMENTO' => 'PUNO']);
    echo "<h4>Ferroviarias en Puno:</h4>";
    foreach ($puno_ferroviarias as $doc) {
        $lat = $doc['LATITUD'] ?? 0;
        $lng = $doc['LONGITUD'] ?? 0;
        echo "<p>🚂 {$doc['NOMBRE']} - LAT: $lat, LNG: $lng";
        
        // Verificar si está en el lago (aproximadamente)
        if ($lat > -16 && $lat < -15 && $lng > -70.5 && $lng < -69.5) {
            echo " ⚠️ <span style='color: orange;'>Posible ubicación en lago</span>";
        }
        echo "</p>";
    }
    
    echo "<p style='color: green;'>🎉 ¡Coordenadas mejoradas!</p>";
    echo "<p>🔄 Ahora prueba el dashboard: <a href='dashboard.html'>Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>