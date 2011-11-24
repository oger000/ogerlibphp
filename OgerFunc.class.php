<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/




/**
* some helpfull functions
*/
class OgerFunc {

  /**
  * untabify string with tabstop width
  */
  public static function untabify($string, $tabWidth = 8) {

    $parts = explode("\t", $string);

    $result = array_shift($parts);
    while (count($parts)) {
      // the previous part is followed by at least one blank
      $result .= ' ';
      // insert blanks till the next tabstop
      while (strlen($result) % $tabWidth) {
        $result .= ' ';
      }
      // append next string part
      $result .= array_shift($parts);
    }  // loop over parts

    return $result;

  }  // end of untabify string


  /**
  * Check if array is associative.
  * That means if it has non numeric keys.
  */
  public static function isAssoc($array) {
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
  * Evaluate an arithmetic expressoin from string.
  * Only base arithmetic works because of security reasons.
  */
  public static function evalMath($str) {
    $str = preg_replace('/[^0-9\. \+\-\*\/\(\)]+/', '', $str);
    return eval('return ' . $str . ';');
  }




}  // eo class

?>
