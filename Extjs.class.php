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
  * Create an extjs json message with success property.
  * @msg: message text
  * @success: success value. Defaults to true.
  */
  public static function message($msg, $success = true) {

    // escape string delimiter (we hope that json_encode does this for us)
    //$msg = str_replace("'", "\'", $msg);
    ////$msg = str_replace('"', '\"', $msg);

    return json_encode(array('success' => (boolean)$success, 'msg' => $msg));

  }  // create a extjs json message



  /**
  * Create a error message.
  * Is an alias for an unsuccess message.
  */
  public static function errorMsg($msg) {
    return self::unsuccessMsg($msg);
  }



  /**
  * Create an unsuccess message.
  */
  public static function unsuccessMsg($msg) {
    return self::message($msg, false);
  }



  /**
  * Create a success message.
  */
  public static function successMsg($msg) {
    return self::message($msg, true);
  }



  /**
  * Create a success object (without message).
  */
  public static function success() {
    return '{ success: true }';
  }


}  // end of class
?>
