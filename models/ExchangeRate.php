<?php
include_once 'services/DatabaseUtils.php';

class ExchangeRate {
    public static function insertRates($conn, $observations, $dates, $currency) {
        $values = array();

        foreach ($observations as $key => $observation) {
            $rate = (float)$observation[0];
            $sqlTimestamp = date("Y-m-d", strtotime($dates[$key]));

            $values[] = "('$currency', $rate, '$sqlTimestamp')";
        }

        if (!empty($values)) {
            $tableName = "exchange_rates_$currency";
            $sql = "INSERT INTO $tableName (currency, exchange_rate, Date_stamp) VALUES " . implode(",", $values);

            if ($conn->query($sql) !== TRUE) {
                die("Error: " . $conn->error);
            }
        }

        // Create an instance of DatabaseUtils
        $dbUtils = new DatabaseUtils();

        // Call the non-static method on the instance
        $dbUtils->removeDuplicateRecords($conn, $currency);
    }
}
?>
