<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/




/**
* some helpfull functions for csv
*/
class OgerCsv {

  public static $fieldSeparator = ';';



  /**
  * Preapre one field for export
  * Helper function: Format OgerCsv::prepField
  * Fields are enclosed (delimited) by double apostroph
    // change:
    // - double apostroph to singel apostroph
    // - \n and \rto text representation
  */
  public static function prepFieldOut($value) {

    // do not prepare empty fields
    if (!$value) {
      return '';
    }

    // double apostroph to a double apostroph to make reverse converting "relatively" easy
    $value = str_replace('"', "''", $value);

    // change "\n" to text representation
    $value = str_replace("\n", '\n', $value);
    $value = str_replace("\r", '\n', $value);

    return '"' . $value . '"';

  }  // eo prepare field


  /**
  * Prepare full row from value array
  * Add delimiter also to last field.
  */
  public static function prepRowOut($values, $addNewLine = true) {

    $row = '';

    if ($values === null) {
      $values = array();
    }

    // for primitives (string, int, etc)
    if (!is_array($values)) {
      $values = (array)$values;
    }

    foreach ($values as $value) {
      $row .= static::prepFieldOut($value) . static::$fieldSeparator;
    }

    if ($addNewLine) {
      $row .= "\n";
    }

    return $row;
  }  // eo repare row



}  // eo class

?>
