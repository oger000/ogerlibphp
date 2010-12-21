<?PHP


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


}

?>
