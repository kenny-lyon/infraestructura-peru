<?php
header('Content-Type: application/json');
require 'conexion.php';

$db = new Database();

$puertos = $db->getPuertos()->find();
$aeropuertos = $db->getAeropuertos()->find();
$ferroviarias = $db->getFerroviarias()->find();

// Normalizar y agregar campo 'tipo'
$puntos = [];
foreach ($puertos as $p) {
    $p['tipo'] = 'puerto';
    $p['id'] = (string)$p['_id'];
    unset($p['_id']);
    $puntos[] = $p;
}
foreach ($aeropuertos as $a) {
    $a['tipo'] = 'aeropuerto';
    $a['id'] = (string)$a['_id'];
    unset($a['_id']);
    $puntos[] = $a;
}
foreach ($ferroviarias as $f) {
    $f['tipo'] = 'ferroviaria';
    $f['id'] = (string)$f['_id'];
    unset($f['_id']);
    $puntos[] = $f;
}

echo json_encode($puntos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); 