<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
include_once 'services/utils_service.php';
include_once __DIR__ . '/services/DatabaseUtils.php';
include_once __DIR__ . '/api/ApiHandler.php';

// Connection to db
$conn = DatabaseUtils::connectToDatabase();

// Create an instance of the DatabaseUtils class
$dbUtils = new DatabaseUtils();

// Create an instance of the ApiHandler class, passing the DatabaseUtils instance
$apiHandler = new ApiHandler($dbUtils);

$currencies = ['USD', 'EUR', 'GBP'];

foreach ($currencies as $currency) {
    $apiHandler->ensureDatabaseAndTablesExist($currency);
    // Fetch data from the external API and update the database
    $apiHandler->fetchDataAndUpdateDatabase($currency);
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <?php foreach ($currencies as $currency) {
        echo "<h2>Exchage rates $currency to ILS</h2>";
        $tableName = "exchange_rates_$currency";

        $results = DatabaseUtils::getDataFromDB($conn, $tableName, 10);
        echo "<h3>currency | exchange rate | Date</h3>";
        echo "<ol>";
        foreach ($results as $result) {
            echo "<li>{$result['currency']} | {$result['exchange_rate']} | {$result['Date_stamp']}</li>";
        }
        echo "</ol>";

        // echo "<script>console.log('index');</script>";

    }
    // Close DB connection
    $conn->close();
    ?>

</body>

</html>