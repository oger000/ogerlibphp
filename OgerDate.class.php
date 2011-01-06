<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/




/**
* Date part of OgerDateTime class
*/
class OgerDate extends OgerDateTimeBase {

  protected static $defaultStringFormat = 'Y-m-d';
  protected static $defaultFormatFormat = 'Y-m-d';

  protected static $ansiFormat = 'Y-m-d';


  /**
  * construct object
  */
  public function __construct($timeStr = 'now', $tz = null) {
    parent::__construct($timeStr, $tz);
    // remove time part
    $this->setTime(0, 0);
  }  // end of constructor


  /**
  * get year
  */
  public function getYear() {
    return parent::format('Y');
  }


  /**
  * get difference in days
  */
  public function diffDays($dateTime) {
    // round because of daylightsaving gap
    return round(abs(($this->getMidnight()->getTimestamp() - $dateTime->getMidnight()->getTimestamp()) / (60 * 60 * 24)));
  }


}  // end of class

?>
