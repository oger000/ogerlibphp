<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/


/**
* Helper routines for extjs.
*/
class Extjs {

  /**
  * Create failure message.
  */
  public static function failureMsg($msg) {
    $msg = str_replace("'", "\'", $msg);
    //$msg = str_replace('"', '\"', $msg);
    return "{ success: false, msg: '" . $msg . "' }";
  }  // failure msg json object


}  // end of class
?>
