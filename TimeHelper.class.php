<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/




/**
* Class for simple date time handling. So simple, that this functions
* do not go into OgerDateTimeBase.
* A collection of static methods.
*/
class TimeHelper {

  public static $dateFormat = 'Y-m-d';
  public static $timeFormat = 'H:i';
  public static $dateTimeFormat = 'c';


  /**
  * Format a unix timestamp to a datetime. Accepts string.
  * False can be used as empty datetime. Null and true are 'now'.
  */
  public static function format($stamp = null, $format = null) {

    if ($stamp === null || $stamp === true) {
      $stamp = time();
    }
    elseif (is_string($stamp)) {
      $stamp = trim($stamp);
      // handle sql-nulldate
      if (substr($stamp, 0, 10) == '0000-00-00') {
        $stamp = '';
      }
    }

    // handle empty strings and false
    if (!$stamp) {
      return '';
    }

    // convert to time and format the result
    return date(($format ?: self::$dateTimeFormat), strtotime($stamp));

  }  // eo format datetime



  /**
  * Format a unix timestamp to a date.
  */
  public static function formatDate($stamp = null, $format = null) {
    return self::format($stamp, ($format ?: self::$dateFormat));
  }  // eo format date


  /**
  * Format a unix timestamp with ansi date format.
  */
  public static function formatAnsiDate($stamp = null) {
    return self::format($stamp, 'Y-m-d');
  }  // eo format ansi date


  /**
  * Format a unix timestamp to a time.
  */
  public static function formatTime($stamp = null, $format = null) {
    return self::format($stamp, ($format ?: self::$timeFormat));
  }  // eo format time



}  // end of class

?>
