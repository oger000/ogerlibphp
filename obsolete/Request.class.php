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
* Additions to the request handling.
*/
class Request {


  /**
  * OBSOLETED - use $_REQUEST by default
  * assign all request variables to the post array
  * OBSOLETED
  */
  /*
  public static function toPost() {
    foreach ($_REQUEST as $key => $value) {
      if (!array_key_exists($key, $_POST)) {
        $_POST[$key] = $value;
      }
    }
  }  // end of assign everything to post array
  */

}  // end of class

?>
