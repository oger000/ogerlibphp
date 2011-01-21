<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/




/**
* Class for simple date time handling. So simple, that this functions
* do not go into OgerDateTimeBase.
*/
class OgerDateTimeHelper {

  /**
  * Set "empty" date strings to blank.
  */
  public static function blankDateStrings($dateStr, $dateFieldNames = array()) {

    if (is_array($dateStr)) {

      $newDateStr = array();
      foreach ($dateStr as $key => $value) {

        // if date field names are given and the key of the date string array does not match
        // than preserve orignal value and continue with next item
        if (count($dateFieldNames) && !in_array($key, $dateFieldNames)) {
          $newDateStr[$key] = $value;
          continue;
        }

        $newDateStr[$key] = self::blankDateString($value);

      }  // eo array loop

      return $newDateStr;

    }  // eo array handling


    return self::blankDateString($dateStr);

  }  // eo set empty strings to blank



  /**
  * Set one "empty" date string to blank.
  */
  public static function blankDateString($dateStr) {

    $dateStr = trim($dateStr);

    // handle sql-nulldate, empty strings, false and null
    if (!$timeStr || substr($timeStr, 0, 10) == '0000-00-00') {
      return '';
    }

    // do NOT check if timestring is valid!
    // TODO maybe set invalid timestrings to blank?
    return $timeStr;

  }  // eo set one empty string to blank



}  // end of class

?>
