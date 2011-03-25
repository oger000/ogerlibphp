<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/


/*
* Duration helper class to convert input to seconds
* and format seconds for output
*
* @format: H=hours, i=minutes, s=seconds
*/
class Duration {


  protected static $format = 'H:i';  // fixed format for this application

  protected static $delimChar = ":";  // delimiter for duration parts (input)
  protected static $altDelimChar = ".,";  // alternative delimiter for duration parts

  /**
  * return formated duration
  */
  public static function format($seconds, $format = null) {

    if (!$seconds) {
      return '';
    }

    // check for minus
    if ($seconds < 0) {
      $seconds = abs($seconds);
      $minus = true;
    }

    // split into time peaces / units
    $hours = (int) ($seconds / 60 / 60);
    $seconds -= $hours * 60 * 60;
    $minutes = (int) ($seconds / 60);
    $seconds -= $minutes * 60;
    /*
    $days = (int) ($hours / 24)
    $hours -= $days * 24;
    */

    // fill format (overflow not used time parts to smaler units)
    $str = ($format ?: static::$format);
    if (strpos($str, "H") !== false) {
      // only pad to ONE leading zero
      $str = str_replace("H", str_pad($hours, 1, '0', STR_PAD_LEFT), $str);
    } else {
      $minutes += $hours * 60;
    }
    if (strpos($str, "i") !== false) {
      $str = str_replace("i", str_pad($minutes, 2, '0', STR_PAD_LEFT), $str);
    } else {
      $seconds += $minutes * 60;
    }
    if (strpos($str, "s") !== false) {
      $str = str_replace("s", str_pad($seconds, 2, '0', STR_PAD_LEFT), $str);
    }

    if ($minus) {
      $str = "-" . $str;
    }

    return $str;
  }  // end of formated output




  /**
  * Formated duration.
  * Short style without trailing components and without leading zeros.
  */
  public static function formatBare($inStr, $format = null) {

    $str = static::format($inStr, $format);

    // remove trailing parts
    $pattern = '/' . static::$delimChar .  '0+$/';
    while (preg_match($pattern, $str)) {
      $str = preg_replace($pattern, '', $str);
    }

    // remove leading zeros
    $pattern = '/^0+([1-9])/';
    $str = preg_replace($pattern, '$1', $str);

    return $str;

  }  // end of short format


  /**
  * parse input according to format
  * ATTENTION: parsing from left to right (most significant first)
  */
  public static function parse(&$inStrOri, $format = null) {

    // trim and replace alternative delimiters
    $inStr = $inStrOri;
    $inStr = trim($inStr);

    // UNDOCUMENTED HACK: (relays on next hack)
    // if first char is a '=' eval the expression and use the result as hours decimals
    // replace every non arithmetic chars to have *some* security
    // replace european decimal sign
    if (substr($inStr, 0, 1) == '=') {
      $inStr = trim(substr($inStr, 1));
      $inStr = strtr($inStr, ',', '.');
      $inStr = preg_replace('/[^0-9\. \+\-\*\/\(\)]+/', '', $inStr);
      $inStr = '#' . eval('return ' . $inStr . ';');
    }

    // ANOTHER UNDOCUMENTED HACK:
    // if first char is a '#' it are hours (decimals are fractal of hours)
    // replace european decimal sign
    if (substr($inStr, 0, 1) == '#') {
      $inStr = trim(substr($inStr, 1));
      $inStr = strtr($inStr, ',', '.');
      $seconds = $inStr * 60 * 60;
      // reformat original input string
      $inStrOri = self::format($seconds);
      return $seconds;
    }

    // replace alternative delimiters
    $inStr = strtr($inStr, static::$altDelimChar, str_repeat(static::$delimChar, strlen(static::$altDelimChar)));

    // check for valid chars, allow leading minus sign
    if (substr($inStr, 0, 1) == "-") {
      $minus = true;
      $inStr = substr($inStr, 1);
      $inStr = trim($inStr);
    }

    // check for empty field and exit if empty
    if ($inStr == '') {
      return 0;
    }

    for ($i=0; $i < strlen($inStr); $i++) {
      if (strpos("01234567890" . static::$delimChar, substr($inStr, $i, 1)) === false) {
        throw new Exception(L::_("Ungültiges Zeichen in Zeitdauer: $inStrOri."));
      }
    }  // end of check chars

    // fill format multiplier array
    $tmpFormat = ($format ?: static::$format);
    $tmpFormat = str_replace("H", 60 * 60, $tmpFormat);
    $tmpFormat = str_replace("i", 60, $tmpFormat);
    $tmpFormat = str_replace("s", 1, $tmpFormat);
    $multiply = explode(static::$delimChar, $tmpFormat);

    $input = explode(static::$delimChar, $inStr);
    $seconds = 0;
    for ($i = 0; $i < count($input); $i++) {
      $seconds += $input[$i] * $multiply[$i];
    }

    if ($minus) {
      $seconds *= -1;
    }

    // reformat original input string
    $inStrOri = self::format($seconds);

    return $seconds;
  }


}  // end of class

?>
