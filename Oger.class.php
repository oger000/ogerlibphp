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


  /**
  * Report a debug message to a file.
  * @param $msg Debug message.
  * @param $fileName File to write to. Must be writable for calling user.
  */
  public static function debugFile($msg, $fileName = "debug.localonly") {
    $msg = "\n" . date("c") . ":\n{$msg}";
    @file_put_contents($fileName, "{$msg}\n", FILE_APPEND);
  }  // eo debug to file



  /**
  * Create natural sort entry for an id
  * Expand every numeric part by prefixing with zeros to a fixed length.
  * Negative sign and decimal chars are detected as NON-number chars
  * TODO move/copy to Oger class
  */
  public static function natSortId($id, $numlength = 10, $maxlength = null) {
    preg_match_all("/(\d+)/", $id, $matches);
    $parts = preg_split("/(\d+)/", $id);

    $natId = "";
    foreach ($matches[1] as $num) {
      $part = array_shift($parts);
      $natId .= $part . str_pad($num, $numlength, "0", STR_PAD_LEFT);
    }
    $natId .= array_shift($parts);

    if ($maxlength > 0) {
      $natId = substr($natId, 0, $maxlength);
    }

    return $natId;
  }  // eo natural sort






}  // eo class

?>
