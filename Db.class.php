<?PHP

/**
* Handle db related things
*/
class Db {

  private static $conn;

  const ACTION_INSERT = 'INSERT';
  const ACTION_UPDATE = 'UPDATE';

  /**
  * get connection
  **/
  public static function getConn() {
    if (self::$conn === null) {
      self::$conn = new PDO(Config::$dbName, Config::$dbUser, Config::$dbPass);
      if (Config::$dbErrorMode) {
        self::$conn->setAttribute(PDO::ATTR_ERRMODE, Config::$dbErrorMode);
      }
      // TODO maybe the following settings are mysql specific?
      if (Config::$dbConnectionCharset) {
        self::$conn->exec('SET CHARACTER SET ' . Config::$dbConnectionCharset);
      }
      if (Config::$dbConnectionTimeZone) {
        self::$conn->exec('SET time_zone = ' . Config::$dbConnectionTimeZone);
      }
   }
    return self::$conn;
  }

  /**
  * prepare statement and fill parameter array from request
  */
  public static function prepare($stmt, &$parms = array()) {
    $parms = static::fillStmtParms($stmt, $parms);
    $parms = static::cleanStmtParms($stmt, $parms);
    self::getConn();
    return self::$conn->prepare($stmt);
  }


  /**
  * get parameters of sql statement
  * currently named parameters only
  */
  // TODO handle positional parameters too ???
  private static function getStmtPlaceholder($stmt) {
    preg_match_all('/(:\w+)/', $stmt, $matches);
    return $matches[1];
  }

  /**
  * process query
  **/
  public static function query($stmt) {
    self::getConn();
    return self::$conn->query($stmt);
  }


  /**
  * get named sql parameters (by default from post data)
  */
  public static function fillStmtParms($stmt, $values = null) {
    // fill values array from post variables by default
    if ($values === null) {
      $values = $_POST;
    }
    // if values are from an object then convert to array
    if (is_object($values)) {
      $values = get_object_vars($values);
    }

    $names = static::getStmtPlaceholder($stmt);
    $result = array();
    // fill parameters where corresponding key exists in values array
    foreach($values as $key => $value) {
      $key = ':' . $key;
      if (array_search($key, $names) !== false) {
        $result[$key] = $value;
      }
    }

    // fill missing parameters with empty strings
    foreach($names as $key) {
      if (!array_key_exists($key, $result)) {
        $result[$key] = '';
      }
    }

    return $result;
  }  // end of fill stmt parameters


  /**
  * remove entries from search values when no corresponding sql parameters exists
  */
  public static function cleanStmtParms($stmt, $values = null) {
    // if values are from an object then convert to array
    if (is_object($values)) {
      $values = get_object_vars($values);
    }

    $names = static::getStmtPlaceholder($stmt);

    // check keys
    foreach($values as $key => $value) {
      $key = ':' . $key;
      if (array_search($key, $names) === false) {
        unset($values[$key]);
      }
    }

    return $values;
  }  // end of cleaning statement parameters


  /**
  * create prepared statement for insert or update
  */
  public static function createStmt($action, $table, $fields, $where) {

    $stmt = '';

    switch ($action) {
    case self::ACTION_INSERT:
      for ($i=0; $i < count($fields); $i++) {
        if ($i > 0) {
          $stmtField .= ',';
          $stmtValue .= ',';
        }
        $field = $fields[$i];
        $stmtField .= $field;
        $stmtValue .= ':' . $field;
      }
      $stmt .= "INSERT INTO $table (" . $stmtField . ') VALUES (' . $stmtValue . ')';
      break;
    case self::ACTION_UPDATE:
      $stmt .= 'UPDATE ' . $table . ' SET ';
      for ($i=0; $i < count($fields); $i++) {
        if ($i > 0) $stmt .= ',';
        $field = $fields[$i];
        $stmt .= $field . '=:' . $field;
      }
      break;
    }

    if ($where) $stmt .= ' WHERE ' . $where;

    return $stmt;

  } // end of create prepared statement


}   // end of class

?>
