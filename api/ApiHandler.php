<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../services/DatabaseUtils.php';

class ApiHandler
{
    private const API_ENDPOINT = 'https://edge.boi.gov.il/FusionEdgeServer/sdmx/v2/data/dataflow/BOI.STATISTICS/EXR/1.0/RER_%s_ILS?startperiod=2023-01-01&endperiod=2024-01-01&format=sdmx-json&data';

    private $dbUtils; 

    public function __construct(DatabaseUtils $dbUtils)
    {
        $this->dbUtils = $dbUtils;
    }

    // public function fetchDataAndUpdateDatabase($currency)
    // {
    //     $apiEndpoint = sprintf(self::API_ENDPOINT, $currency);
    //     $apiData = $this->makeApiRequest($apiEndpoint);

    //     // Update db with new data
    //     $this->updateDatabase($currency, $apiData);

    //     return $apiData;
    // }

    public function fetchDataAndUpdateDatabase($currency)
    {
        // Create an instance of DatabaseUtils
        $dbUtils = new DatabaseUtils();

        // Create an instance of ApiHandler and provide DatabaseUtils instance
        $apiHandler = new ApiHandler($dbUtils);

        $apiEndpoint = sprintf(self::API_ENDPOINT, $currency);
        $apiData = $apiHandler->makeApiRequest($apiEndpoint);

        // Update db with new data
        $apiHandler->updateDatabase($currency, $apiData);

        return $apiData;
    }

    public function ensureDatabaseAndTablesExist($currency)
    {
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

    private function updateDatabase($currency, $apiData)
    {
        $conn = $this->dbUtils->connectToDatabase();

        try {
            // Get data from db
            $existingData = $this->dbUtils->getDataFromDB($conn, "exchange_rates_" . strtoupper($currency));

            $values = $this->formatData($apiData);
            $rates = $values['rates'];
            $dates = $values['dates'];
    
            // Check if the database is empty or has fewer records than the API data
            if (empty($existingData)) {
                $this->dbUtils->insertExchangeRates($conn, $values, $currency);
            } elseif (count($existingData) < count($dates)) {
                $this->dbUtils->insertOrUpdateExchangeRates($conn, $rates,$dates, $currency);
            }            
        } catch (Exception $e) {
            echo 'Error updating database: ' . $e->getMessage();
        } finally {
            $conn->close();
        }
    }

    private function formatData($apiData)
    {
        $rawRatesData = $apiData["data"]["dataSets"][0]["series"]["0:0:0:0:0:0"]["observations"];
        $rawDatesData = $apiData["data"]["structure"]["dimensions"]["observation"][0]["values"];
        $rates = [];
        $dates = [];
        foreach ($rawRatesData as $rawRate) {
            $rates[] = $rawRate[0];
        }
        foreach ($rawDatesData as $rawDate) {
            $dates[] = $rawDate["id"];
        }

        return ['rates' => $rates, 'dates' => $dates];

    }

    public function makeApiRequest($apiEndpoint)
    {
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

    private function createDatabaseIfNotExists($conn)
    {
        $dbName = DB_NAME;

        $createDbSql = "CREATE DATABASE IF NOT EXISTS $dbName";

        if ($conn->query($createDbSql) !== TRUE) {
            throw new Exception('Error creating database: ' . $conn->error);
        }
    }

    private function createExchangeRatesTablesIfNotExists($conn, $currency)
    {
        $tableName = "exchange_rates_" . strtoupper($currency);

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