<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🚂 Análisis de Datos Ferroviarios</h2>";

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $excelFile = 'ferroviarias/Infraestructura_ferroviaria_RedFerroviaria_2022-2024.xlsx';
    
    if (!file_exists($excelFile)) {
        echo "<p style='color: red;'>❌ No se encontró el archivo: $excelFile</p>";
        exit;
    }
    
    echo "<p>📂 Leyendo archivo: $excelFile</p>";
    
    $spreadsheet = IOFactory::load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Obtener datos
    $data = $worksheet->toArray();
    
    echo "<p>📊 Total de filas: " . count($data) . "</p>";
    
    if (count($data) > 0) {
        $headers = $data[0];
        echo "<p>📋 Total de columnas: " . count($headers) . "</p>";
        
        echo "<h3>🏷️ Columnas encontradas:</h3>";
        echo "<ul>";
        foreach ($headers as $i => $header) {
            echo "<li>" . ($i + 1) . ". <strong>$header</strong></li>";
        }
        echo "</ul>";
        
        echo "<h3>🔍 Primeras 3 filas de datos:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        foreach ($headers as $header) {
            echo "<th style='padding: 8px;'>$header</th>";
        }
        echo "</tr>";
        
        for ($i = 1; $i <= min(3, count($data) - 1); $i++) {
            echo "<tr>";
            foreach ($headers as $j => $header) {
                $value = $data[$i][$j] ?? '';
                echo "<td style='padding: 8px;'>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Crear CSV
        echo "<p>🔄 Creando archivo CSV...</p>";
        
        if (!is_dir('data')) {
            mkdir('data', 0777, true);
        }
        
        $csvFile = 'data/ferroviarias_limpio.csv';
        $handle = fopen($csvFile, 'w');
        
        if ($handle) {
            // Escribir encabezados
            fputcsv($handle, $headers);
            
            // Escribir datos (saltando la primera fila que son los encabezados)
            $count = 0;
            for ($i = 1; $i < count($data); $i++) {
                $row = $data[$i];
                
                // Filtrar filas vacías
                $hasData = false;
                foreach ($row as $cell) {
                    if (!empty($cell)) {
                        $hasData = true;
                        break;
                    }
                }
                
                if ($hasData) {
                    fputcsv($handle, $row);
                    $count++;
                }
            }
            
            fclose($handle);
            echo "<p style='color: green;'>✅ CSV creado: $csvFile</p>";
            echo "<p>📈 Registros procesados: $count</p>";
            
            // Mostrar estadísticas
            echo "<h3>📊 Estadísticas:</h3>";
            
            // Analizar columnas específicas
            if (in_array('ELECTRIFICACION', $headers)) {
                $electrificacion = array_column(array_slice($data, 1), array_search('ELECTRIFICACION', $headers));
                $electrificacion_count = array_count_values(array_filter($electrificacion));
                echo "<p><strong>Electrificación:</strong> " . print_r($electrificacion_count, true) . "</p>";
            }
            
            if (in_array('ESTADO_CONSERVACION', $headers)) {
                $estado = array_column(array_slice($data, 1), array_search('ESTADO_CONSERVACION', $headers));
                $estado_count = array_count_values(array_filter($estado));
                echo "<p><strong>Estado de Conservación:</strong> " . print_r($estado_count, true) . "</p>";
            }
            
            if (in_array('TITULARIDAD', $headers)) {
                $titularidad = array_column(array_slice($data, 1), array_search('TITULARIDAD', $headers));
                $titularidad_count = array_count_values(array_filter($titularidad));
                echo "<p><strong>Titularidad:</strong> " . print_r($titularidad_count, true) . "</p>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ Error creando archivo CSV</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p style='color: green;'>🎉 Análisis completado!</p>";
echo "<p>🔄 Siguiente paso: <a href='cargar_ferroviarias.php'>Cargar a MongoDB</a></p>";
?>