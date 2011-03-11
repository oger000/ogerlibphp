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
  * Create a response object.
  * @data: An array with data for json object. Defaults to empty array.
  * @success: Boolean flag for the success property.
  */
  public static function responseObj($data = array(), $success = true) {
    $data['success'] = (boolean)$success;
    return json_encode($data);
  }  // eo success object


  /**
  * Create a success object.
  */
  public static function successObj($data = array()) {
    return self::responseObj($data, true);
  }  // eo success object



  /**
  * Create a unsuccess/failure/error object.
  */
  public static function unsuccessObj($data = array()) {
    return self::responseObj($data, false);
  }  // eo unsuccess object



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
    return self::unsuccessObj(array('msg' => $msg));
  }



  /**
  * Create a success message.
  */
  public static function successMsg($msg) {
    return self::successObj(array('msg' => $msg));
  }



  /**
  * Create a success object (without message).
  * DEPRECATED use successObj() instead
  */
  /*
  public static function success() {
    return self::successObj();
  }
  */


}  // end of class
?>
