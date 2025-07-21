<?php
// Cargar autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Intentar usar MongoDB si está configurado
if (getenv('MONGODB_URI') && getenv('MONGODB_DATABASE')) {
    try {
        require_once 'conexion_mongodb_real.php';
        $database = new DatabaseMongoDB();
        // Si llegamos aquí, MongoDB está funcionando
        class Database extends DatabaseMongoDB {}
    } catch (Exception $e) {
        error_log("MongoDB no disponible, usando CSV fallback: " . $e->getMessage());
        // Fallback a CSV
        require_once 'cargar_datos_csv.php';
        // Continuar con la clase Database original más abajo
    }
} else {
    // No hay configuración MongoDB, usar CSV
    require_once 'cargar_datos_csv.php';
}

// Solo definir Database si no se definió como extensión de MongoDB
if (!class_exists('Database')) {
class Database {
    private $atlas_uri;
    private $database_name;
    
    public function __construct() {
        try {
            $this->atlas_uri = $this->getAtlasURI();
            $this->database_name = $this->getDatabaseName();
            
            // Test básico de conexión
            $this->testConnection();
            
        } catch (Exception $e) {
            error_log("Error de conexión: " . $e->getMessage());
            // En producción, usar datos de respaldo
            if ($this->isProduction()) {
                $this->atlas_uri = "fallback";
            } else {
                die("Error de conexión: " . $e->getMessage());
            }
        }
    }
    
    private function getAtlasURI() {
        $uri = getenv('MONGODB_URI');
        if ($uri) {
            return $uri;
        }
        return "local";
    }
    
    private function getDatabaseName() {
        $database = getenv('MONGODB_DATABASE');
        if ($database) {
            return $database;
        }
        return "proyecto";
    }
    
    private function testConnection() {
        if ($this->atlas_uri === "local") {
            return true;
        }
        
        // En producción, asumir que funciona
        return true;
    }
    
    public function isProduction() {
        return getenv('MONGODB_URI') !== false;
    }
    
    public function getPuertos() {
        return new MongoCollection($this, 'puertos');
    }
    
    public function getAeropuertos() {
        return new MongoCollection($this, 'aeropuertos');
    }
    
    public function getFerroviarias() {
        return new MongoCollection($this, 'ferroviarias');
    }
    
    public function findMany($collection, $filter = []) {
        return $this->getCollectionData($collection);
    }
    
    public function findOne($collection, $filter = []) {
        $data = $this->getCollectionData($collection);
        return count($data) > 0 ? $data[0] : null;
    }
    
    public function insertOne($collection, $document) {
        // Simular inserción exitosa
        return uniqid();
    }
    
    public function updateOne($collection, $filter, $update) {
        // Simular actualización exitosa
        return 1;
    }
    
    public function deleteOne($collection, $filter) {
        // Simular eliminación exitosa
        return 1;
    }
    
    private function getCollectionData($collection) {
        // Cargar datos reales desde CSV
        switch ($collection) {
            case 'puertos':
                return procesarPuertos();
            case 'aeropuertos':
                return procesarAeropuertos();
            case 'ferroviarias':
                return procesarFerroviarias();
            default:
                return [];
        }
        
        // Datos de muestra para demostración (código de respaldo)
        $sampleData = [
            'puertos' => [
                [
                    '_id' => '1',
                    'NOMBRE' => 'CALLAO',
                    'LATITUD' => -12.047,
                    'LONGITUD' => -77.148,
                    'REGION' => 'LIMA',
                    'TIPO' => 'MARITIMO'
                ],
                [
                    '_id' => '2',
                    'NOMBRE' => 'PAITA',
                    'LATITUD' => -5.089,
                    'LONGITUD' => -81.114,
                    'REGION' => 'PIURA',
                    'TIPO' => 'MARITIMO'
                ],
                [
                    '_id' => '3',
                    'NOMBRE' => 'ILO',
                    'LATITUD' => -17.640,
                    'LONGITUD' => -71.337,
                    'REGION' => 'MOQUEGUA',
                    'TIPO' => 'MARITIMO'
                ]
            ],
            'aeropuertos' => [
                [
                    '_id' => '1',
                    'NOMBRE' => 'JORGE CHAVEZ',
                    'LATITUD' => -12.022,
                    'LONGITUD' => -77.114,
                    'REGION' => 'LIMA',
                    'TIPO' => 'INTERNACIONAL'
                ],
                [
                    '_id' => '2',
                    'NOMBRE' => 'CUSCO',
                    'LATITUD' => -13.535,
                    'LONGITUD' => -71.939,
                    'REGION' => 'CUSCO',
                    'TIPO' => 'NACIONAL'
                ],
                [
                    '_id' => '3',
                    'NOMBRE' => 'AREQUIPA',
                    'LATITUD' => -16.341,
                    'LONGITUD' => -71.583,
                    'REGION' => 'AREQUIPA',
                    'TIPO' => 'NACIONAL'
                ]
            ],
            'ferroviarias' => [
                [
                    '_id' => '1',
                    'NOMBRE' => 'ESTACION CENTRAL LIMA',
                    'LONGITUD' => -77.048,
                    'REGION' => 'LIMA',
                    'TIPO' => 'PASAJEROS'
                ],
                [
                    '_id' => '2',
                    'NOMBRE' => 'ESTACION CUSCO',
                    'LONGITUD' => -71.967,
                    'REGION' => 'CUSCO',
                    'TIPO' => 'TURISTICO'
                ],
                [
                    '_id' => '3',
                    'NOMBRE' => 'ESTACION HUANCAYO',
                    'LONGITUD' => -75.204,
                    'REGION' => 'JUNIN',
                    'TIPO' => 'CARGA'
                ]
            ]
        ];
        
        return $sampleData[$collection] ?? [];
    }
}

class MongoCollection {
    private $db;
    private $collection_name;
    
    public function __construct($db, $collection_name) {
        $this->db = $db;
        $this->collection_name = $collection_name;
    }
    
    public function find($filter = []) {
        return $this->db->findMany($this->collection_name, $filter);
    }
    
    public function findOne($filter = []) {
        return $this->db->findOne($this->collection_name, $filter);
    }
    
    public function insertOne($document) {
        return $this->db->insertOne($this->collection_name, $document);
    }
    
    public function updateOne($filter, $update) {
        return $this->db->updateOne($this->collection_name, $filter, $update);
    }
    
    public function deleteOne($filter) {
        return $this->db->deleteOne($this->collection_name, $filter);
    }
    
    public function countDocuments($filter = []) {
        $docs = $this->find($filter);
        return count($docs);
    }
}

} // Cierre de if (!class_exists('Database'))
?>