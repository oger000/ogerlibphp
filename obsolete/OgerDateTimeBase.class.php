<?PHP
/*
#LICENSE BEGIN
**********************************************************************
* OgerArch - Archaeological Database is released under the GNU General Public License (GPL) <http://www.gnu.org/licenses>
* Copyright (C) Gerhard Öttl <gerhard.oettl@ogersoft.at>
**********************************************************************
#LICENSE END
*/




/**
* Base class for date and time extensions
* @time: If no time is given than 'now' is the default.
*        If an empty string or null is given it result in an empty date time value.
*        new DateTime() results in now.
*        new DateTime(null) results in an empty date time.
*        new DateTime('') results in an empty date time.
*        new DateTime(' ') results in an empty date time.
*/
abstract class OgerDateTimeBase extends DateTime {

  protected static $defaultStringFormat = DateTime::ATOM;
  protected static $defaultFormatFormat = DateTime::ATOM;

  protected static $ansiFormat = DateTime::ATOM;

  protected $stringFormat;
  protected $formatFormat;


  /**
  * construct object
  */
  public function __construct($timeStr = 'now', $tz = null) {

    /*
    // allow creation from another OgerDateTime object
    if ($timeStr instanceof self) {
      $tmpObj = new self();
      $tmpObj->setTimestamp($tmpObj->getTimestamp());
      return $tmpObj;
    }
    */

    // set system default timezone if timezone not provided
    if($tz === null)
      $tz = new DateTimeZone(date_default_timezone_get());

    /*
    // set invalid time string silently to empty time
    if (strtotime($timeStr) === false) {
      $timeStr = null;
    }
    */

    // handle empty time - check only year because of timezone issues
    $timeStr = trim($timeStr);
    if (substr($timeStr, 0, 4) == '0000' || !$timeStr) {
      $timeStr = '@0';
    }

    // call parent constructor
    parent::__construct($timeStr, $tz);

    /*
    * do not catch input errors
    try {
      parent::__construct($timeStr, $tz);
    }
    catch (Exception $ex) {
      // on error default to empty time
      parent::__construct('@0', $tz);
    }
    */

  }  // end of constructor


  /**
  * convert object to string
  * Empty OgerDateTimeBase (unix-timestamp=0) reslults in empty string
  */
  public function __toString() {
    if ($this->isEmpty()) { return ''; }
    return parent::format($this->getStringFormat());
  }


  /**
  * set default format for __toString for whole class
  */
  public static function setDefaultStringFormat($format) {
    static::$defaultStringFormat = $format;
  }
  /**
  * get default format for __toString for whole class
  */
  public static function getDefaultStringFormat() {
    return static::$defaultStringFormat;
  }


  /**
  * set default format for format method for whole class
  */
  public static function setDefaultFormatFormat($format) {
    static::$defaultFormatFormat = $format;
  }
  /**
  * get default format for format method for whole class
  */
  public static function getDefaultFormatFormat() {
    return static::$defaultFormatFormat;
  }


  /**
  * set format for __toString for this object
  */
  public function setStringFormat($format) {
    $this->stringFormat = $format;
  }
  /**
  * get actual format for __toString for this object
  */
  public function getStringFormat() {
    if ($this->stringFormat) { return $this->stringFormat; }
    return static::$defaultStringFormat ;
  }


  /**
  * set format for format method for this object
  */
  public function setFormatFormat($format) {
    $this->formatFormat = $format;
  }
  /**
  * get actual format for format method for this object
  */
  public function getFormatFormat() {
    if ($this->formatFormat) { return $this->formatFormat; }
    return static::$defaultFormatFormat ;
  }


  /**
  * format object (empty timestamp as empty string)
  */
  public function format($format = null) {
    if ($this->isEmpty()) { return ''; }
    if (!$format) { $format = $this->getFormatFormat(); }
    return parent::format($format);
  }


  /**
  * formating date in one fixed ansi format
  * Allow overwrite for those who know what they do
  * but dont have setters and getters for now to change in static context
  */
  public function formatAnsi($format = null) {
    if (!$format) { $format = static::$ansiFormat; }
    return $this->format($format);
  }


  /**
  * check if time is empty
  */
  public function isEmpty() {
    return !parent::format('U');
  }


  /**
  * check this timestamp is after passed timestamp
  */
  public function isAfter($other) {
    return $this->getTimestamp() > $other->getTimestamp();
  }

  /**
  * check this timestamp is before passed timestamp
  */
  public function isBefore($other) {
    return $this->getTimestamp() < $other->getTimestamp();
  }


  /**
  * set to midnight
  */
  public function setMidnight() {
    $this->setTime(0, 0);
    return $this;
  }
  /**
  * get midnight time for date
  */
  public function getMidnight() {
    $obj = clone $this;
    $obj->setMidnight();
    return $obj;
  }


}  // end of class

?>
