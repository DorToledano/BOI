<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/DatabaseUtils.php';

class ApiHandler {
    private const API_ENDPOINT = 'https://edge.boi.gov.il/FusionEdgeServer/sdmx/v2/data/dataflow/BOI.STATISTICS/EXR/1.0/RER_%s_ILS?startperiod=2023-01-01&endperiod=2024-01-01&format=sdmx-json&data';

    private $dbUtils; // Define the dbUtils property

    public function __construct(DatabaseUtils $dbUtils) {
        $this->dbUtils = $dbUtils;
    }

    public function fetchDataAndUpdateDatabase($currency) {
        $apiEndpoint = sprintf(self::API_ENDPOINT, $currency);
        $apiData = $this->makeApiRequest($apiEndpoint);

        // Update db with new data
        $this->updateDatabase($currency, $apiData);

        return $apiData;
    }

    public function ensureDatabaseAndTablesExist($currency) {
        $conn = $this->dbUtils->connectToDatabase();

        try {
            // Create the database if it doesn't exist
            $this->createDatabaseIfNotExists($conn);

            // Switch to the specified database
            $conn->select_db(DB_NAME);

            // Create exchange rates tables if they don't exist
            $this->createExchangeRatesTablesIfNotExists($conn, $currency);
        } catch (Exception $e) {
            // Handle exceptions (log or display an error message)
            echo 'Error: ' . $e->getMessage();
        } finally {
            // Always close db connection
            $conn->close();
        }
    }

    // private function updateDatabase($currency, $apiData) {
    //     $conn = $this->dbUtils->connectToDatabase();

    //     try {
    //         // Inserting new exchange 
    //         $this->dbUtils->insertExchangeRates($conn, $apiData, $currency);

    //         // Removing duplicates 
    //         $this->dbUtils->removeDuplicateRecords($conn, $currency);
    //     } catch (Exception $e) {
    //         // Handle exceptions (log or display an error message)
    //         echo 'Error updating database: ' . $e->getMessage();
    //     } finally {
    //         // Always close db connection
    //         $conn->close();
    //     }
    // }

    private function updateDatabase($currency, $apiData) {
        $conn = $this->dbUtils->connectToDatabase();
    
        try {
            // Get existing data from the database
            $existingData = $this->dbUtils->getDataFromDB($conn, "exchange_rates_" . strtolower($currency));
    
            // Check if the database is empty or has fewer records than the API data
            if (empty($existingData) || count($existingData) < count($apiData)) {
                // If empty or fewer records, insert all API data or update it
                // $this->dbUtils->insertOrUpdateExchangeRates($conn, $apiData, $currency);
                $this->dbUtils->insertExchangeRates($conn, $apiData, $currency);
                $this->dbUtils->removeDuplicateRecords($conn,$currency);
            }
        } catch (Exception $e) {
            // Handle exceptions (log or display an error message)
            echo 'Error updating database: ' . $e->getMessage();
        } finally {
            // Always close db connection
            $conn->close();
        }
    }
    
    
    

    private function makeApiRequest($apiEndpoint) {
        // Initialize cURL session
        $ch = curl_init($apiEndpoint);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        // Execute cURL session and get JSON response
        $jsonData = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        // Close cURL session
        curl_close($ch);

        // Decode JSON response
        return json_decode($jsonData, true);
    }

    private function createDatabaseIfNotExists($conn) {
        $dbName = DB_NAME;

        $createDbSql = "CREATE DATABASE IF NOT EXISTS $dbName";

        if ($conn->query($createDbSql) !== TRUE) {
            throw new Exception('Error creating database: ' . $conn->error);
        }
    }

    private function createExchangeRatesTablesIfNotExists($conn, $currency) {
        $tableName = "exchange_rates_" . strtolower($currency);

        $createTableSql = "CREATE TABLE IF NOT EXISTS $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            currency VARCHAR(255),
            exchange_rate DECIMAL(10, 2),
            Date_stamp DATE
        )";

        if ($conn->query($createTableSql) !== TRUE) {
            throw new Exception('Error creating table: ' . $conn->error);
        }
    }
}
?>
