<?php
echo "<h2>🔧 Limpieza de Encoding UTF-8</h2>";

function limpiarEncoding($texto) {
    // Limpiar caracteres problemáticos
    $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8');
    
    // Reemplazos específicos
    $reemplazos = [
        'Est�ndar' => 'Estándar',
        'P�blica' => 'Pública',
        'Privada' => 'Privada',
        'Operativo' => 'Operativo',
        'No electrificada' => 'No electrificada',
        'Electrificada' => 'Electrificada'
    ];
    
    foreach ($reemplazos as $malo => $bueno) {
        $texto = str_replace($malo, $bueno, $texto);
    }
    
    return $texto;
}

try {
    $inputFile = '../ferroviarias/ferroviarias_limpio.csv';
    $outputFile = '../data/ferroviarias_limpio_utf8.csv';
    
    if (!file_exists($inputFile)) {
        echo "<p style='color: red;'>❌ No se encontró el archivo: $inputFile</p>";
        exit;
    }
    
    echo "<p>📂 Procesando archivo: $inputFile</p>";
    
    $input = fopen($inputFile, 'r');
    $output = fopen($outputFile, 'w');
    
    if (!$input || !$output) {
        echo "<p style='color: red;'>❌ Error abriendo archivos</p>";
        exit;
    }
    
    $lineCount = 0;
    
    while (($line = fgets($input)) !== false) {
        // Limpiar encoding de la línea completa
        $cleanLine = limpiarEncoding($line);
        
        // Escribir línea limpia
        fwrite($output, $cleanLine);
        $lineCount++;
    }
    
    fclose($input);
    fclose($output);
    
    echo "<p style='color: green;'>✅ Archivo limpio creado: $outputFile</p>";
    echo "<p>📊 Líneas procesadas: $lineCount</p>";
    
    // Mostrar muestra del archivo limpio
    echo "<h3>🔍 Muestra del archivo limpio:</h3>";
    $lines = file($outputFile, FILE_IGNORE_NEW_LINES);
    if ($lines) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        foreach (array_slice($lines, 0, 5) as $i => $line) {
            $cells = str_getcsv($line);
            echo "<tr>";
            foreach (array_slice($cells, 0, 8) as $cell) {
                echo "<td style='padding: 5px;'>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<p>🔄 Siguiente paso: <a href='cargar_ferroviarias_limpio.php'>Cargar archivo limpio a MongoDB</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>