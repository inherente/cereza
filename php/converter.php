<?php
class Converter {
    public static function convertISO8601ToMySQLDATETIME($isoDateTime) {
     // Convert to MySQL DATETIME format
        error_log("Convert " . $isoDateTime . " .");
        $format = 'Y-m-d\TH:i:s.u\Z';
        $dateTime = DateTime::createFromFormat($format, $isoDateTime);
        
     // $dateTime = DateTime::createFromFormat(DateTime::ISO8601, $isoDateTime);
        if ($dateTime === false) {
            throw new Exception('Invalid datetime format: ' . $isoDateTime);
        }
        $mysqlDateTime = $dateTime->format('Y-m-d H:i:s');
        return $mysqlDateTime;
    }

    public static function convertToUTC($datetimeText ) {
     // Create a DateTime object from the input string
        $datetime = new DateTime($datetimeText);
     // Convert the DateTime object to UTC (or desired timezone)
        $datetime->setTimezone(new DateTimeZone('UTC'));
     // Format the DateTime object to MySQL DATETIME format
        $mysqlDatetime = $datetime->format('Y-m-d H:i:s');
     // Output the result
        error_log("before (" . $datetimeText . ") after (" . $mysqlDatetime . ")");
        return $mysqlDatetime; // Output: 
    }
}
?>
