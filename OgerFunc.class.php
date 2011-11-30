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
  * Insert an associative array into another associative array after specified key.
  * Numeric arrays are NOT handled properly!
  * WE DONT CHECK ANYTHING! Values are overwritten and other unexpected results may happen if input is not correct.
  * Same keys in array2 overwrites values of array1.
  * TODO: Check: Maybe this is slow. There is another solution spliting the original
  * array into keys and values, insert the new keys and values via array_splice and
  * create the result array via array_combine. See: <http://www.php.net/manual/en/function.array-splice.php>
  * @array1: Associative array.
  * @searchKey: Key after which array 2 is inserted.
  * @array2: Associative array.
  */
  public static function arrayInsertAfterKey(&$array1, $searchKey, $array2) {
    $array = array();
    $insertDone = false;
    foreach ($array1 as $key1 => $value1) {
      $array[$key1] = $value1;
      if ($key1 == $searchKey) {
        $insertDone = true;
        foreach ($array2 as $key2 => $value2) {
          $array[$key2] = $value2;
        }
      }
    }
    // if we did not find the key append the inserts here
    if (!$insertDone) {
      foreach ($array2 as $key2 => $value2) {
        $array[$key2] = $value2;
      }
    }
    
    $array1 = $array;
    return $array;
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
