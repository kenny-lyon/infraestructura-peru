<?php
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

echo "🚂 === ANALIZANDO ESTRUCTURA FERROVIARIA ===\n";

try {
    // Leer archivo Excel
    $excelFile = '../ferroviarias/Infraestructura_ferroviaria_RedFerroviaria_2022-2024.xlsx';
    
    if (!file_exists($excelFile)) {
        echo "❌ No se encontró el archivo: $excelFile\n";
        exit;
    }
    
    echo "📂 Leyendo archivo: $excelFile\n";
    
    $spreadsheet = IOFactory::load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Obtener datos
    $data = $worksheet->toArray();
    
    echo "📊 Total de filas: " . count($data) . "\n";
    
    if (count($data) > 0) {
        $headers = $data[0];
        echo "📋 Total de columnas: " . count($headers) . "\n";
        
        echo "\n🏷️ Columnas encontradas:\n";
        foreach ($headers as $i => $header) {
            echo "  " . ($i + 1) . ". $header\n";
        }
        
        echo "\n🔍 Primeras 3 filas de datos:\n";
        for ($i = 1; $i <= min(3, count($data) - 1); $i++) {
            echo "Fila $i:\n";
            foreach ($headers as $j => $header) {
                $value = $data[$i][$j] ?? '';
                echo "  $header: $value\n";
            }
            echo "\n";
        }
        
        // Crear CSV
        echo "🔄 Creando archivo CSV...\n";
        
        $csvFile = '../data/ferroviarias_limpio.csv';
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
            echo "✅ CSV creado: $csvFile\n";
            echo "📈 Registros procesados: $count\n";
            
            // Mostrar muestra del CSV
            echo "\n🔍 Muestra del CSV creado:\n";
            $csvData = array_map('str_getcsv', file($csvFile));
            for ($i = 0; $i < min(3, count($csvData)); $i++) {
                echo "Fila $i: " . implode(' | ', array_slice($csvData[$i], 0, 5)) . "\n";
            }
            
        } else {
            echo "❌ Error creando archivo CSV\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎉 Análisis completado!\n";
?>