<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/DatabaseUtils.php';

class ApiHandler {
    private const API_ENDPOINT = 'https://edge.boi.gov.il/FusionEdgeServer/sdmx/v2/data/dataflow/BOI.STATISTICS/EXR/1.0/RER_%s_ILS?startperiod=2023-01-01&endperiod=2024-01-01&format=sdmx-json&data';

    public static function fetchDataAndUpdateDatabase($currency) {
        $apiEndpoint = sprintf(self::API_ENDPOINT, $currency);
        $apiData = self::makeApiRequest($apiEndpoint);

        // Update db with new data
        self::updateDatabase($currency, $apiData);

        return $apiData;
    }

    private static function updateDatabase($currency, $apiData) {
        $conn = DatabaseUtils::connectToDatabase();
        $dbUtils = new DatabaseUtils();

        // Inserting new exchange 
        $dbUtils->insertExchangeRates($conn, $apiData, $currency);

        // Removing duplicates 
        $dbUtils->removeDuplicateRecords($conn);

        // Closing db connection
        $conn->close();
    }

    private static function makeApiRequest($apiEndpoint) {
        // Initialize cURL session
        $ch = curl_init($apiEndpoint);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        // Execute cURL session and get JSON response
        $jsonData = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            die('Curl error: ' . curl_error($ch));
        }

        // Close cURL session
        curl_close($ch);

        // Decode JSON response
        return json_decode($jsonData, true);
    }
}
?>
