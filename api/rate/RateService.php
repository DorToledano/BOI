<?php

header('Access-Control-Allow-Origin: *');  
header('Access-Control-Allow-Methods: GET');  
header('Access-Control-Allow-Headers: Content-Type');

// Preflight OPTIONS Requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once __DIR__ . '/../../services/DatabaseUtils.php';

// Connection to db
$conn = DatabaseUtils::connectToDatabase();

$currency = isset($_GET['currency']) ? $_GET['currency'] : 'USD';
$tableName = sprintf('exchange_rates_%s', $currency);
$limit = isset($_GET['limit']) ? $_GET['limit'] : null;

// Fetch results from the specified table
$results = DatabaseUtils::getDataFromDB($conn, $tableName, $limit);

// Output as JSON
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);

// Close the db connection
$conn->close();

?>