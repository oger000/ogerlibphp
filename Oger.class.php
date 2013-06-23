<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/




/**
* Oger class.
* To keep in sync with javascript Oger class.
*/
class Oger {

  /**
  * Translate text
  * TODO: implement (for now only a dummy to mark text for translation)
  */
  public static function _($text) {
    return $text;
  }



  /**
  * Restart session without warnings
  * Cookie based sessions give a warning if reopened after output.
  * Long running scripts need session_write_close() + session_start()
  * because in file based session storage the session file is locked and
  * every other requests within this session that opens the session has
  * to wait till the first script is finished.
  * See <http://stackoverflow.com/questions/12315225/reopening-a-session-in-php>
  */
  public static function sessionRestart() {
    // version 1 (for php 5.3.x)
    ini_set('session.use_only_cookies', false);
    ini_set('session.use_cookies', false);
    ini_set('session.use_trans_sid', false);
    ini_set('session.cache_limiter', null);
    session_start();
    // versoin 2 (php >= 5.4.0)
    // suppress ALL warnings at a first try and
    // if fails redo to show warnings
    /*
    @session_start();
    if (session_status() != PHP_SESSION_ACTIVE) {
      session_start();
    }
    */
  }  // eo reopen session



  /**
   * BACKPORT from Oger12 for ogerlibphp12 calls
  * Check if array is associative.
  * An array is assiciative if it has non numeric keys.<BR>
  * <em>ATTENTION:</em> Associative arrays with only numeric keys are
  * treated as NOT associative!!!! This is a general problem
  * also in PHP internal-functions like array_merge, etc.
  * @param $array  Array to be checked.
  * @return True if it is an associative array. False otherwise.
  */
  public static function isAssocArray($array) {
    if (!is_array($array)) {
      return false;
    }
    foreach ($array as $key => $value) {
      if (!is_numeric($key)) {
        return true;
      }
    }
    return false;
  }  // eo assoc check



  /**
  * BACKPORT from Oger12 for ogerlibphp12 calls
  * Pad string (multibyte variant).
  * @param $str Debug message.
  * see: <http://php.net/manual/en/ref.mbstring.php>
  */
  public static function mbStrPad($str, $len, $padStr = " ", $padStyle = STR_PAD_RIGHT, $encoding = "UTF-8") {
    return str_pad($str, strlen($str) - mb_strlen($str, $encoding) + $len, $padStr, $padStyle);
  }  // eo str pad





  /**
  * Report a debug message.
  * @param $msg Debug message.
  */
  public static function debug($msg) {
    trigger_error($msg, E_USER_WARNING);
  }


}  // eo class

?>
