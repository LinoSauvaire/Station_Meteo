// config.php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'sonde1'); //j'ai cru que c'était un par sonde d'ou le nom choisi my bad
define('DB_PASS', 'meteo');
define('DB_NAME', 'weather_station');

// database.php
<?php
class Database {
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEP>
        }
            catch(PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}

// init.php - Script d'initialisation de la base de données
<?php
require_once 'config.php';
require_once 'database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "CREATE TABLE IF NOT EXISTS weather_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        station_id VARCHAR(50) NOT NULL,
        temperature FLOAT NOT NULL,
        pressure FLOAT NOT NULL,
        humidity FLOAT NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $db->exec($query);
    echo "Table créée avec succès.\n";

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}

//api.php
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'config.php';
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"));
        
    // Vérification des données requises
        if (
            !isset($data->temperature) ||
            !isset($data->pressure) ||
            !isset($data->humidity)
        ) {
            http_response_code(400);
            echo json_encode(array("message" => "Données incomplètes."));
            exit();
        }
        
    // Insertion dans la nouvelle structure
        $query = "INSERT INTO readings
                (id_sonde, temperature, humidity, pression)
                VALUES
                (1, :temperature, :humidity, :pression)";
        
        $stmt = $db->prepare($query);
        
        $success = $stmt->execute(array(
            "temperature" => $data->temperature,
            "humidity" => $data->humidity,
            "pression" => $data->pressure
        ));
        
        if($success) {
            http_response_code(201);
            echo json_encode(array("message" => "Données enregistrées avec succès."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Impossible d'enregistrer les données."));
        }
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Erreur: " . $e->getMessage()));
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
    // Vérifier le paramètre mode (latest ou last10)
        $mode = isset($_GET['mode']) ? $_GET['mode'] : 'latest';
        
        if ($mode === 'latest') {
        // Récupérer uniquement la dernière mesure
            $query = "SELECT r.id_sonde, s.nom_sonde, r.reading_time, 
                             r.temperature, r.humidity, r.pression
                      FROM readings r
                      JOIN sondes s ON r.id_sonde = s.id_sonde
                      ORDER BY r.reading_time DESC
                      LIMIT 1";
        } else {
        // Utiliser la vue pour les 10 dernières mesures
            $query = "SELECT * FROM latest_readings";
        }

        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $data = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        
        http_response_code(200);
        echo json_encode($data);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Erreur: " . $e->getMessage()));
    }
} 
else {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée"));
}
