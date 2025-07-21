<?php
echo "<h2>🚀 Preparar Proyecto para Producción</h2>";

// Crear versión estática del proyecto
try {
    require 'conexion.php';
    
    $db = new Database();
    
    // Exportar datos a JSON
    echo "<h3>📊 Exportando datos a JSON...</h3>";
    
    // Exportar puertos
    $puertos = $db->getPuertos()->find();
    foreach ($puertos as &$puerto) {
        $puerto['_id'] = (string)$puerto['_id'];
    }
    
    // Exportar aeropuertos
    $aeropuertos = $db->getAeropuertos()->find();
    foreach ($aeropuertos as &$aeropuerto) {
        $aeropuerto['_id'] = (string)$aeropuerto['_id'];
    }
    
    // Exportar ferroviarias
    $ferroviarias = $db->getFerroviarias()->find();
    foreach ($ferroviarias as &$ferroviaria) {
        $ferroviaria['_id'] = (string)$ferroviaria['_id'];
    }
    
    // Crear estructura de datos completa
    $datosCompletos = [
        'puertos' => $puertos,
        'aeropuertos' => $aeropuertos,
        'ferroviarias' => $ferroviarias,
        'timestamp' => date('Y-m-d H:i:s'),
        'total_registros' => count($puertos) + count($aeropuertos) + count($ferroviarias)
    ];
    
    // Guardar como JSON
    file_put_contents('datos_produccion.json', json_encode($datosCompletos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo "<p>✅ Datos exportados a <strong>datos_produccion.json</strong></p>";
    echo "<p>📊 Total registros: " . $datosCompletos['total_registros'] . "</p>";
    
    // Crear API estática
    $apiEstatica = '<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$datos = json_decode(file_get_contents("datos_produccion.json"), true);

$tipo = $_GET["tipo"] ?? "all";

switch($tipo) {
    case "puertos":
        echo json_encode($datos["puertos"]);
        break;
    case "aeropuertos":
        echo json_encode($datos["aeropuertos"]);
        break;
    case "ferroviarias":
        echo json_encode($datos["ferroviarias"]);
        break;
    default:
        // API compatible con la original
        $todos = [];
        foreach($datos["puertos"] as $puerto) {
            $puerto["tipo"] = "puerto";
            $todos[] = $puerto;
        }
        foreach($datos["aeropuertos"] as $aeropuerto) {
            $aeropuerto["tipo"] = "aeropuerto";
            $todos[] = $aeropuerto;
        }
        foreach($datos["ferroviarias"] as $ferroviaria) {
            $ferroviaria["tipo"] = "ferroviaria";
            $todos[] = $ferroviaria;
        }
        echo json_encode($todos);
}
?>';
    
    file_put_contents('api_produccion.php', $apiEstatica);
    echo "<p>✅ API estática creada: <strong>api_produccion.php</strong></p>";
    
    // Crear versión del dashboard para producción
    $dashboardOriginal = file_get_contents('dashboard.html');
    
    // Reemplazar API endpoints
    $dashboardProduccion = str_replace(
        "fetch('api_puntos.php')",
        "fetch('api_produccion.php')",
        $dashboardOriginal
    );
    
    // Deshabilitar funcionalidades que requieren MongoDB
    $dashboardProduccion = str_replace(
        "fetch('sistema_auditoria.php",
        "// fetch('sistema_auditoria.php",
        $dashboardProduccion
    );
    
    $dashboardProduccion = str_replace(
        "fetch('transacciones_simuladas.php')",
        "// Funcionalidades de BD deshabilitadas en producción\n        document.getElementById('resultadoAvanzado').innerHTML = '<div style=\"background: #fff3cd; padding: 15px; border-radius: 5px;\"><h4>⚠️ Funcionalidad Deshabilitada</h4><p>Las funcionalidades avanzadas de BD están deshabilitadas en la versión de producción.</p><p>Esta es una demostración estática de los datos.</p></div>';",
        $dashboardProduccion
    );
    
    file_put_contents('dashboard_produccion.html', $dashboardProduccion);
    echo "<p>✅ Dashboard de producción creado: <strong>dashboard_produccion.html</strong></p>";
    
    // Crear archivo de configuración para diferentes entornos
    $config = [
        'desarrollo' => [
            'api_endpoint' => 'api_puntos.php',
            'mongodb_enabled' => true,
            'funcionalidades_avanzadas' => true
        ],
        'produccion' => [
            'api_endpoint' => 'api_produccion.php',
            'mongodb_enabled' => false,
            'funcionalidades_avanzadas' => false
        ]
    ];
    
    file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));
    echo "<p>✅ Configuración creada: <strong>config.json</strong></p>";
    
    echo "<h3>📦 Archivos para Producción</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<h4>✅ Archivos Listos para Subir:</h4>";
    echo "<ul>";
    echo "<li><strong>dashboard_produccion.html</strong> - Dashboard principal</li>";
    echo "<li><strong>api_produccion.php</strong> - API estática</li>";
    echo "<li><strong>datos_produccion.json</strong> - Datos exportados</li>";
    echo "<li><strong>config.json</strong> - Configuración</li>";
    echo "</ul>";
    
    echo "<h4>🌐 Opciones de Hosting:</h4>";
    echo "<ul>";
    echo "<li><strong>Netlify/Vercel:</strong> Subir solo HTML/CSS/JS</li>";
    echo "<li><strong>GitHub Pages:</strong> Hosting gratuito</li>";
    echo "<li><strong>Heroku:</strong> Para PHP + archivos estáticos</li>";
    echo "<li><strong>Shared Hosting:</strong> Cualquier hosting con PHP</li>";
    echo "</ul>";
    
    echo "<h4>🚀 Próximos Pasos:</h4>";
    echo "<ol>";
    echo "<li>Crear cuenta en plataforma de hosting</li>";
    echo "<li>Subir archivos de producción</li>";
    echo "<li>Configurar dominio (opcional)</li>";
    echo "<li>Probar funcionalidad</li>";
    echo "</ol>";
    echo "</div>";
    
    // Crear script de deployment
    $deployScript = '#!/bin/bash
# Script de deployment para producción

echo "🚀 Desplegando a producción..."

# Crear carpeta de deployment
mkdir -p deployment

# Copiar archivos esenciales
cp dashboard_produccion.html deployment/index.html
cp api_produccion.php deployment/
cp datos_produccion.json deployment/
cp config.json deployment/

# Crear .htaccess para Apache
echo "RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [QSA,L]" > deployment/.htaccess

echo "✅ Archivos listos en carpeta deployment/"
echo "📦 Subir contenido de deployment/ a tu hosting"
';
    
    file_put_contents('deploy.sh', $deployScript);
    echo "<p>✅ Script de deployment creado: <strong>deploy.sh</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>