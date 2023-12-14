<?php
include_once __DIR__ . '/../config.php';
class DatabaseUtils
{

    function insertExchangeRates($conn, $values, $currency)
    {
        $tableName = "exchange_rates_" . strtoupper($currency);

        // Ensure values is an array
        if (!is_array($values)) {
            $values = [$values];
        }
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $values[$key] = explode(",", $value);
            }
        }

        // Format each value as ('USD', 3.532, '2023-01-03')
        // Assuming $values is an associative array with keys "rates" and "dates"
        $rates = $values["rates"];
        $dates = $values["dates"];

        // Combine rates and dates into an array of arrays
        $combinedValues = array_map(function ($rate, $date) use ($currency) {
            return [$currency, $rate, $date]; // Use $currency variable here
        }, $rates, $dates);

        // Ensure each value is formatted correctly
        $formattedValues = array_map(function ($value) {
            // Ensure $value is an array with exactly 3 elements
            if (!is_array($value) || count($value) !== 3) {
                die("Error: Invalid value format");
            }
            return "('" . implode("', '", $value) . "')";
        }, $combinedValues);

        // Join the formatted values with commas
        $valuesString = implode(", ", $formattedValues);

        $sql = "INSERT IGNORE INTO $tableName (currency, exchange_rate, Date_stamp) VALUES $valuesString";

        if ($conn->query($sql) !== TRUE) {
            die("Error: " . $conn->error);
        }

    }





    function removeDuplicateRecords($conn, $currency)
    {
        $tableName = "exchange_rates_" . strtoupper($currency);

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
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);


        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Create the database if it does not exist
        $dbName = DB_NAME;
        $createDbSql = "CREATE DATABASE IF NOT EXISTS $dbName";

        if ($conn->query($createDbSql) !== TRUE) {
            die("Error creating database: " . $conn->error);
        }

        // Close the connection without the database name
        $conn->close();

        // Reconnect with the database name included
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

    function insertOrUpdateExchangeRates($conn, $rates, $dates, $currency)
    {
        $tableName = "exchange_rates_" . strtoupper($currency);
        $values = array();

        foreach ($rates as $key => $rate) {
            $sqlTimestamp = date("Y-m-d", strtotime($dates[$key]));
            $values[] = "('$currency', $rate, '$sqlTimestamp')";
        }

        foreach ($values as $value) {
            $value = str_replace(['(', ')', "'"], '', $value);
            $valArr = explode(",", $value);
            $date = $valArr[2];
            // var_dump($date);
            // die("");

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