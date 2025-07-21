<?php
// Cargar autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

class DatabaseMongoDB {
    private $mongo_client;
    private $database;
    private $atlas_uri;
    private $database_name;
    
    public function __construct() {
        try {
            $this->atlas_uri = $this->getAtlasURI();
            $this->database_name = $this->getDatabaseName();
            
            // Crear cliente MongoDB real
            $this->mongo_client = new MongoDB\Client($this->atlas_uri);
            $this->database = $this->mongo_client->selectDatabase($this->database_name);
            
            // Test básico de conexión
            $this->testConnection();
            
        } catch (Exception $e) {
            error_log("Error de conexión MongoDB: " . $e->getMessage());
            throw new Exception("No se puede conectar a MongoDB: " . $e->getMessage());
        }
    }
    
    private function getAtlasURI() {
        $uri = getenv('MONGODB_URI');
        if ($uri) {
            return $uri;
        }
        return "mongodb://localhost:27017";
    }
    
    private function getDatabaseName() {
        $database = getenv('MONGODB_DATABASE');
        if ($database) {
            return $database;
        }
        return "proyecto_infraestructura";
    }
    
    private function testConnection() {
        // Test real de conexión
        $this->database->listCollections();
        return true;
    }
    
    public function getPuertos() {
        return new MongoCollectionReal($this->database, 'puertos');
    }
    
    public function getAeropuertos() {
        return new MongoCollectionReal($this->database, 'aeropuertos');
    }
    
    public function getFerroviarias() {
        return new MongoCollectionReal($this->database, 'ferroviarias');
    }
    
    public function findMany($collection, $filter = []) {
        $coll = $this->database->selectCollection($collection);
        $cursor = $coll->find($filter);
        return iterator_to_array($cursor);
    }
    
    public function findOne($collection, $filter = []) {
        $coll = $this->database->selectCollection($collection);
        $result = $coll->findOne($filter);
        return $result ? $result : null;
    }
    
    public function insertOne($collection, $document) {
        $coll = $this->database->selectCollection($collection);
        $result = $coll->insertOne($document);
        return $result->getInsertedId();
    }
    
    public function insertMany($collection, $documents) {
        $coll = $this->database->selectCollection($collection);
        $result = $coll->insertMany($documents);
        return $result->getInsertedIds();
    }
    
    public function updateOne($collection, $filter, $update) {
        $coll = $this->database->selectCollection($collection);
        $result = $coll->updateOne($filter, ['$set' => $update]);
        return $result->getModifiedCount() > 0;
    }
    
    public function deleteOne($collection, $filter) {
        $coll = $this->database->selectCollection($collection);
        $result = $coll->deleteOne($filter);
        return $result->getDeletedCount() > 0;
    }
    
    // Método para obtener estadísticas
    public function getStats() {
        $stats = [];
        $collections = ['puertos', 'aeropuertos', 'ferroviarias'];
        
        foreach ($collections as $collName) {
            $coll = $this->database->selectCollection($collName);
            $stats[$collName] = $coll->countDocuments();
        }
        
        return $stats;
    }
}

class MongoCollectionReal {
    private $collection;
    private $collection_name;
    
    public function __construct($database, $collection_name) {
        $this->collection = $database->selectCollection($collection_name);
        $this->collection_name = $collection_name;
    }
    
    public function find($filter = [], $options = []) {
        $cursor = $this->collection->find($filter, $options);
        return iterator_to_array($cursor);
    }
    
    public function findOne($filter = [], $options = []) {
        $result = $this->collection->findOne($filter, $options);
        return $result ? $result : null;
    }
    
    public function insertOne($document) {
        $result = $this->collection->insertOne($document);
        return $result->getInsertedId();
    }
    
    public function insertMany($documents) {
        $result = $this->collection->insertMany($documents);
        return $result->getInsertedIds();
    }
    
    public function updateOne($filter, $update, $options = []) {
        $result = $this->collection->updateOne($filter, ['$set' => $update], $options);
        return $result->getModifiedCount() > 0;
    }
    
    public function deleteOne($filter, $options = []) {
        $result = $this->collection->deleteOne($filter, $options);
        return $result->getDeletedCount() > 0;
    }
    
    public function countDocuments($filter = [], $options = []) {
        return $this->collection->countDocuments($filter, $options);
    }
    
    public function aggregate($pipeline, $options = []) {
        $cursor = $this->collection->aggregate($pipeline, $options);
        return iterator_to_array($cursor);
    }
}
?>