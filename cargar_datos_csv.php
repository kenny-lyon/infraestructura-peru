<?php
function cargarDatosCSV($archivo) {
    $datos = [];
    if (($handle = fopen($archivo, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row = [];
            for ($i = 0; $i < count($headers); $i++) {
                $row[$headers[$i]] = isset($data[$i]) ? $data[$i] : '';
            }
            $datos[] = $row;
        }
        fclose($handle);
    }
    return $datos;
}

function procesarPuertos() {
    $datos = cargarDatosCSV(__DIR__ . '/data/puertos_limpio.csv');
    $puertos = [];
    
    foreach ($datos as $index => $puerto) {
        if (!empty($puerto['LATITUD']) && !empty($puerto['LONGITUD'])) {
            $puertos[] = [
                '_id' => (string)($index + 1),
                'NOMBRE' => $puerto['NOMBRE_TERMINAL'] ?? '',
                'LOCALIDAD' => $puerto['LOCALIDAD'] ?? '',
                'LATITUD' => (float)$puerto['LATITUD'],
                'LONGITUD' => (float)$puerto['LONGITUD'],
                'TIPO' => $puerto['TIPO_TERMINAL'] ?? '',
                'ESTADO' => $puerto['ESTADO'] ?? '',
                'ADMINISTRADOR' => $puerto['ADMINISTRADOR'] ?? '',
                'USO' => $puerto['USO'] ?? '',
                'TRAFICO' => $puerto['TRAFICO'] ?? '',
                'ACTIVIDAD' => $puerto['ACTIVIDAD'] ?? '',
                'AMBITO' => $puerto['AMBITO'] ?? '',
                'TITULARIDAD' => $puerto['TITULARIDAD'] ?? ''
            ];
        }
    }
    
    return $puertos;
}

function procesarAeropuertos() {
    $datos = cargarDatosCSV(__DIR__ . '/data/aeropuertos_limpio.csv');
    $aeropuertos = [];
    
    foreach ($datos as $index => $aeropuerto) {
        if (!empty($aeropuerto['LATITUD']) && !empty($aeropuerto['LONGITUD'])) {
            $aeropuertos[] = [
                '_id' => (string)($index + 1),
                'NOMBRE' => $aeropuerto['NOMBRE'] ?? '',
                'TIPO_AERODROMO' => $aeropuerto['TIPO_AERODROMO'] ?? '',
                'CODIGO_OACI' => $aeropuerto['CODIGO_OACI'] ?? '',
                'LATITUD' => (float)$aeropuerto['LATITUD'],
                'LONGITUD' => (float)$aeropuerto['LONGITUD'],
                'ESTADO' => $aeropuerto['ESTADO'] ?? '',
                'ADMINISTRADOR' => $aeropuerto['ADMINISTRADOR'] ?? '',
                'ESCALA' => $aeropuerto['ESCALA'] ?? '',
                'JERARQUIA' => $aeropuerto['JERARQUIA'] ?? '',
                'TITULARIDAD' => $aeropuerto['TITULARIDAD'] ?? '',
                'DEPARTAMENTO' => $aeropuerto['DEPARTAMENTO'] ?? '',
                'PROVINCIA' => $aeropuerto['PROVINCIA'] ?? '',
                'DISTRITO' => $aeropuerto['DISTRITO'] ?? ''
            ];
        }
    }
    
    return $aeropuertos;
}

function procesarFerroviarias() {
    $datos = cargarDatosCSV(__DIR__ . '/data/ferroviarias_limpio_utf8.csv');
    $ferroviarias = [];
    
    // Coordenadas aproximadas por departamento para ferroviarias
    $coordenadasDepartamento = [
        'LIMA' => ['lat' => -12.047, 'lng' => -77.048],
        'JUNIN' => ['lat' => -12.066, 'lng' => -75.204],
        'AREQUIPA' => ['lat' => -16.409, 'lng' => -71.537],
        'PUNO' => ['lat' => -15.839, 'lng' => -70.023],
        'CUSCO' => ['lat' => -13.518, 'lng' => -71.967],
        'HUANCAVELICA' => ['lat' => -12.783, 'lng' => -74.973],
        'PASCO' => ['lat' => -10.688, 'lng' => -76.256],
        'HUANUCO' => ['lat' => -9.295, 'lng' => -75.999],
        'ANCASH' => ['lat' => -9.527, 'lng' => -77.527],
        'LA LIBERTAD' => ['lat' => -8.112, 'lng' => -79.029],
        'LAMBAYEQUE' => ['lat' => -6.771, 'lng' => -79.837],
        'PIURA' => ['lat' => -5.195, 'lng' => -80.628],
        'CAJAMARCA' => ['lat' => -7.163, 'lng' => -78.513],
        'AMAZONAS' => ['lat' => -6.201, 'lng' => -77.856],
        'LORETO' => ['lat' => -3.749, 'lng' => -73.253],
        'SAN MARTIN' => ['lat' => -6.034, 'lng' => -77.281],
        'UCAYALI' => ['lat' => -8.379, 'lng' => -74.554],
        'MADRE DE DIOS' => ['lat' => -12.593, 'lng' => -69.189],
        'APURIMAC' => ['lat' => -13.634, 'lng' => -72.881],
        'AYACUCHO' => ['lat' => -13.155, 'lng' => -74.204],
        'HUANCAVELICA' => ['lat' => -12.783, 'lng' => -74.973],
        'ICA' => ['lat' => -14.068, 'lng' => -75.729],
        'MOQUEGUA' => ['lat' => -17.193, 'lng' => -70.936],
        'TACNA' => ['lat' => -18.013, 'lng' => -70.253],
        'TUMBES' => ['lat' => -3.570, 'lng' => -80.459]
    ];
    
    foreach ($datos as $index => $ferroviaria) {
        if (!empty($ferroviaria['LONGITUD'])) {
            $departamento = strtoupper($ferroviaria['DEPARTAMENTO'] ?? '');
            $coords = $coordenadasDepartamento[$departamento] ?? ['lat' => -12.047, 'lng' => -77.048];
            
            // Generar coordenadas geográficas aproximadas basadas en el departamento
            // El campo LONGITUD del CSV es distancia en km, no coordenada geográfica
            $longitud = $coords['lng'] + (rand(-100, 100) / 1000); // Variación en longitud
            $latitud = $coords['lat'] + (rand(-100, 100) / 1000); // Variación en latitud
            
            $ferroviarias[] = [
                '_id' => (string)($index + 1),
                'NOMBRE' => $ferroviaria['NOMBRE'] ?? '',
                'CODIGO_FERROVIARIO' => $ferroviaria['CODIGO_FERROVIARIO'] ?? '',
                'DEPARTAMENTO' => $ferroviaria['DEPARTAMENTO'] ?? '',
                'TRAMO' => $ferroviaria['TRAMO'] ?? '',
                'SUBTRAMO' => $ferroviaria['SUBTRAMO'] ?? '',
                'LONGITUD' => $longitud, // Coordenada geográfica generada
                'LATITUD' => $latitud, // Coordenada geográfica generada
                'LONGITUD_KM' => (float)$ferroviaria['LONGITUD'], // Distancia real del tramo en km
                'ANCHO' => $ferroviaria['ANCHO'] ?? '',
                'ELECTRIFICACION' => $ferroviaria['ELECTRIFICACION'] ?? '',
                'ESTADO' => $ferroviaria['ESTADO'] ?? '',
                'TITULARIDAD' => $ferroviaria['TITULARIDAD'] ?? '',
                'ADMINISTRADOR' => $ferroviaria['ADMINISTRADOR'] ?? '',
                'ES_CONCES' => $ferroviaria['ES_CONCES'] ?? '',
                'OBSERVACIONES' => $ferroviaria['OBSERVACIONES'] ?? ''
            ];
        }
    }
    
    return $ferroviarias;
}
?>