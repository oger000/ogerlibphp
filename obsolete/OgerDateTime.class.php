<?PHP
/*
#LICENSE BEGIN
**********************************************************************
* OgerArch - Archaeological Database is released under the GNU General Public License (GPL) <http://www.gnu.org/licenses>
* Copyright (C) Gerhard Öttl <gerhard.oettl@ogersoft.at>
**********************************************************************
#LICENSE END
*/




// TODO clone method

class OgerDateTime extends OgerDateTimeBase {

  protected static $defaultStringFormat = 'Y-m-d H:i:s';
  protected static $defaultFormatFormat = 'Y-m-d H:i:s';

  protected static $defaultDateFormat = 'Y-m-d';
  protected static $defaultTimeFormat = 'H:i:s';

  protected static $ansiFormat = 'Y-m-d H:i:s';

  protected $dateFormat;
  protected $timeFormat;


  /**
  * construct object
  */
  public function __construct($timeStr = 'now', $tz = null) {
    parent::__construct($timeStr, $tz);
  }  // end of constructor


  /**
  * set default date format for class
  */
  public static function setDefaultDateFormat($format) {
    static::$defaultDateFormat = $format;
  }
  /**
  * get default date format for class
  */
  public static function getDefaultDateFormat() {
    return static::$defaultDateFormat;
  }


  /**
  * set default time format for class
  */
  public static function setDefaultTimeFormat($format) {
    static::$defaultTimeFormat = $format;
  }
  /**
  * get default time format for class
  */
  public static function getDefaultTimeFormat() {
    return static::$defaultTimeFormat;
  }


  /**
  * set date format for for object
  */
  public static function setDateFormat($format) {
    $this->dateFormat = $format;
  }
  /**
  * get date format from this object
  */
  public function getDateFormat() {
    if ($this->dateFormat) { return $this->dateFormat; }
    return static::$defaultDateFormat ;
  }


  /**
  * set time format for for object
  */
  public static function setTimeFormat($format) {
    $this->timeFormat = $format;
  }
  /**
  * get time format from this object
  */
  public function getTimeFormat() {
    if ($this->timeFormat) { return $this->timeFormat; }
    return static::$defaultTimeFormat ;
  }


  /**
  * formating date part
  */
  public function formatDate($format = null) {
    if (!$format) { $format = $this->getDateFormat(); }
    return $this->format($format);
  }


  /**
  * formating time part
  */
  public function formatTime($format = null) {
    if (!$format) { $format = $this->getTimeFormat(); }
    return $this->format($format);
  }






  /**
  * formating date (static function)
  */
  /*
  public static function formatDateStatic($timeStr, $format = null) {
    $obj = new self($timeStr);
    return $obj->formatDate($format);
  }
  */


  /**
  * formating time (static function)
  */
  /*
  public function formatTimeStatic($timeStr, $format = null) {
    $obj = new self($timeStr);
    return $obj->formatTime($format);
  }
  */





}  // end of class

?>
