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
* Time part of OgerDateTime class
*/
class OgerTime extends OgerDateTimeBase {

  protected static $defaultStringFormat = 'H:m:s';
  protected static $defaultFormatFormat = 'H:m:s';

  protected static $ansiFormat = 'H:m:s';


  /**
  * construct object
  */
  public function __construct($timeStr = 'now', $tz = null) {
    parent::__construct($timeStr, $tz);
    // remove date part
    $this->setDate(0, 0, 0);
  }  // end of constructor



}  // end of class

?>
