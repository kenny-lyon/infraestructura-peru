<?php
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
?>