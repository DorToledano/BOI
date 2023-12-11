<?php
include_once __DIR__ . '/../config.php';
class DatabaseUtils
{

    function insertExchangeRates($conn, $values, $currency)
    {
        $tableName = "exchange_rates_" . strtolower($currency); 

        $sql = "INSERT IGNORE INTO $tableName (currency, exchange_rate, Date_stamp) VALUES " . implode(",", $values);

        if ($conn->query($sql) !== TRUE) {
            die("Error: " . $conn->error);
        }
    }

    function removeDuplicateRecords($conn, $currency)
    {
        $tableName = "exchange_rates_" . strtolower($currency);

        $sql = "DELETE e1 FROM $tableName e1
            JOIN $tableName e2 
            WHERE e1.id > e2.id 
            AND e1.currency = e2.currency 
            AND e1.Date_stamp = e2.Date_stamp";

        if ($conn->query($sql) !== TRUE) {
            die("Error: " . $conn->error);
        }
    }

    public static function connectToDatabase()
    {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }


    public static function getDataFromDB($conn, $tableName, $limit = null)
    {
        $limitClause = $limit !== null ? "LIMIT $limit" : "";

        $sql = "SELECT * FROM $tableName ORDER BY Date_stamp DESC $limitClause";
        $result = $conn->query($sql);

        if (!$result) {
            die("Error: " . $conn->error);
        }

        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        return $results;
    }

    function insertOrUpdateExchangeRates($conn, $values, $currency)
{
    $tableName = "exchange_rates_" . strtolower($currency);

    foreach ($values as $value) {
        $date = $value['Date_stamp'];

        // Check if the record already exists
        $existingRecord = $this->getExchangeRateByDate($conn, $tableName, $date);

        if (!$existingRecord) {
            // Insert new record
            $this->insertExchangeRates($conn, [$value], $currency);
        }
    }
}


    function updateExchangeRate($conn, $tableName, $data, $date)
    {
        $sql = "UPDATE $tableName SET
                currency = '{$data['currency']}',
                exchange_rate = {$data['exchange_rate']}
                WHERE Date_stamp = '$date'";

        if ($conn->query($sql) !== TRUE) {
            die("Error updating record: " . $conn->error);
        }
    }

    function getExchangeRateByDate($conn, $tableName, $date)
    {
        $sql = "SELECT * FROM $tableName WHERE Date_stamp = '$date'";
        $result = $conn->query($sql);

        if (!$result) {
            die("Error: " . $conn->error);
        }

        return $result->fetch_assoc();
    }
}
?>