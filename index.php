<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';
require_once 'database.php';
include_once 'services/utils_service.php';
require_once 'models/ExchangeRate.php';
require_once 'api/ApiHandler.php';
require_once 'services/DatabaseUtils.php';

include_once __DIR__ . './services/DatabaseUtils.php';

// Connection to db
$conn = DatabaseUtils::connectToDatabase();

$currencies = ['USD', 'EUR', 'GBP'];

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

    }
    // Close DB connection
    $conn->close();
    ?>

</body>

</html>