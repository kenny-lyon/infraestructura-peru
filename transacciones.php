<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔄 Implementación de Transacciones MongoDB</h2>";

try {
    require 'conexion.php';
    
    $db = new Database();
    $client = $db->getClient();
    
    // Iniciar sesión para transacciones
    $session = $client->startSession();
    
    echo "<h3>📋 Transacción: Agregar Puerto con Auditoría</h3>";
    
    // Datos del nuevo puerto
    $nuevoPuerto = [
        'NOMBRE_TERMINAL' => 'PUERTO NUEVO CALLAO',
        'LOCALIDAD' => 'CALLAO',
        'DEPARTAMENTO' => 'LIMA',
        'LATITUD' => -12.0500,
        'LONGITUD' => -77.1200,
        'ESTADO' => 'En construcción',
        'TITULARIDAD' => 'Pública',
        'ADMINISTRADOR' => 'APN',
        'TONELADAS_ANUALES' => 500000,
        'tipo' => 'puerto',
        'fecha_creacion' => new MongoDB\BSON\UTCDateTime(),
        'usuario_creacion' => 'admin_sistema'
    ];
    
    $auditoria = [
        'fecha' => new MongoDB\BSON\UTCDateTime(),
        'usuario' => 'admin_sistema',
        'accion' => 'INSERT',
        'coleccion' => 'puertos',
        'documento' => $nuevoPuerto,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'localhost',
        'descripcion' => 'Inserción de nuevo puerto via sistema'
    ];
    
    $estadisticas = [
        'fecha' => new MongoDB\BSON\UTCDateTime(),
        'total_puertos' => 1,
        'operacion' => 'incrementar'
    ];
    
    // TRANSACCIÓN: Todo o nada
    $session->startTransaction();
    
    try {
        // 1. Insertar el puerto
        $puertos = $db->getPuertos();
        $resultPuerto = $puertos->insertOne($nuevoPuerto, ['session' => $session]);
        
        // 2. Registrar auditoría
        $mongodb = $client->selectDatabase('proyecto');
        $auditoria['documento_id'] = $resultPuerto->getInsertedId();
        $auditoriaCol = $mongodb->selectCollection('auditoria');
        $auditoriaCol->insertOne($auditoria, ['session' => $session]);
        
        // 3. Actualizar estadísticas
        $estadisticasCol = $mongodb->selectCollection('estadisticas');
        $estadisticasCol->updateOne(
            ['_id' => 'contadores'],
            [
                '$inc' => ['total_puertos' => 1],
                '$set' => ['ultima_actualizacion' => new MongoDB\BSON\UTCDateTime()]
            ],
            ['upsert' => true, 'session' => $session]
        );
        
        // 4. Simular validación (podría fallar)
        if ($nuevoPuerto['LATITUD'] < -90 || $nuevoPuerto['LATITUD'] > 90) {
            throw new Exception("Coordenadas inválidas");
        }
        
        // Si todo salió bien, confirmar transacción
        $session->commitTransaction();
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Transacción EXITOSA</h4>";
        echo "<p><strong>Puerto ID:</strong> " . $resultPuerto->getInsertedId() . "</p>";
        echo "<p><strong>Operaciones completadas:</strong></p>";
        echo "<ul>";
        echo "<li>✅ Puerto insertado en colección 'puertos'</li>";
        echo "<li>✅ Auditoría registrada en colección 'auditoria'</li>";
        echo "<li>✅ Estadísticas actualizadas en colección 'estadisticas'</li>";
        echo "<li>✅ Validaciones pasadas correctamente</li>";
        echo "</ul>";
        echo "</div>";
        
    } catch (Exception $e) {
        // Si algo falla, deshacer TODA la transacción
        $session->abortTransaction();
        
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ Transacción FALLIDA</h4>";
        echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Resultado:</strong> TODOS los cambios fueron deshechos</p>";
        echo "</div>";
    }
    
    // Mostrar ejemplo de transacción compleja
    echo "<h3>🔄 Transacción Compleja: Transferir Carga entre Puertos</h3>";
    
    $session->startTransaction();
    
    try {
        // Simular transferencia de carga
        $puertoOrigen = 'PUERTO DEL CALLAO';
        $puertoDestino = 'PUERTO NUEVO CALLAO';
        $cantidadTransferir = 100000;
        
        // 1. Reducir carga del puerto origen
        $resultOrigen = $puertos->updateOne(
            ['NOMBRE_TERMINAL' => $puertoOrigen],
            ['$inc' => ['TONELADAS_ANUALES' => -$cantidadTransferir]],
            ['session' => $session]
        );
        
        // 2. Aumentar carga del puerto destino
        $resultDestino = $puertos->updateOne(
            ['NOMBRE_TERMINAL' => $puertoDestino],
            ['$inc' => ['TONELADAS_ANUALES' => $cantidadTransferir]],
            ['session' => $session]
        );
        
        // 3. Registrar la transferencia
        $transferencia = [
            'fecha' => new MongoDB\BSON\UTCDateTime(),
            'origen' => $puertoOrigen,
            'destino' => $puertoDestino,
            'cantidad' => $cantidadTransferir,
            'usuario' => 'admin_sistema',
            'tipo' => 'transferencia_carga'
        ];
        
        $auditoriaCol->insertOne($transferencia, ['session' => $session]);
        
        // Verificar que ambos puertos existan
        if ($resultOrigen->getModifiedCount() === 0) {
            throw new Exception("Puerto origen no encontrado");
        }
        
        if ($resultDestino->getModifiedCount() === 0) {
            throw new Exception("Puerto destino no encontrado");
        }
        
        $session->commitTransaction();
        
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Transferencia EXITOSA</h4>";
        echo "<p><strong>Origen:</strong> $puertoOrigen (-$cantidadTransferir toneladas)</p>";
        echo "<p><strong>Destino:</strong> $puertoDestino (+$cantidadTransferir toneladas)</p>";
        echo "<p><strong>Auditoría:</strong> Transferencia registrada</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        $session->abortTransaction();
        
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ Transferencia FALLIDA</h4>";
        echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Resultado:</strong> No se modificaron las cargas</p>";
        echo "</div>";
    }
    
    // Mostrar estadísticas de transacciones
    echo "<h3>📊 Estadísticas de Transacciones</h3>";
    
    $auditoriaDocs = $auditoriaCol->find([], ['limit' => 10, 'sort' => ['fecha' => -1]]);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Colección</th><th>Descripción</th></tr>";
    
    foreach ($auditoriaDocs as $doc) {
        $fecha = $doc['fecha']->toDateTime()->format('Y-m-d H:i:s');
        $usuario = $doc['usuario'] ?? 'Sistema';
        $accion = $doc['accion'] ?? 'N/A';
        $coleccion = $doc['coleccion'] ?? 'N/A';
        $descripcion = $doc['descripcion'] ?? 'Sin descripción';
        
        echo "<tr>";
        echo "<td>$fecha</td>";
        echo "<td>$usuario</td>";
        echo "<td>$accion</td>";
        echo "<td>$coleccion</td>";
        echo "<td>$descripcion</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Mostrar contadores actuales
    echo "<h3>📈 Contadores Actuales</h3>";
    
    $estadisticasDoc = $estadisticasCol->findOne(['_id' => 'contadores']);
    
    if ($estadisticasDoc) {
        echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px;'>";
        echo "<p><strong>Total Puertos:</strong> " . ($estadisticasDoc['total_puertos'] ?? 0) . "</p>";
        echo "<p><strong>Última Actualización:</strong> " . ($estadisticasDoc['ultima_actualizacion'] ?? new MongoDB\BSON\UTCDateTime())->toDateTime()->format('Y-m-d H:i:s') . "</p>";
        echo "</div>";
    }
    
    echo "<h3>🎯 Ventajas de las Transacciones</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
    echo "<ul>";
    echo "<li><strong>Atomicidad:</strong> Todo o nada - si una operación falla, todas se deshacen</li>";
    echo "<li><strong>Consistencia:</strong> Los datos siempre quedan en estado válido</li>";
    echo "<li><strong>Aislamiento:</strong> Las transacciones no interfieren entre sí</li>";
    echo "<li><strong>Durabilidad:</strong> Los cambios confirmados son permanentes</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Error del Sistema</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>