<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/



/**
* Handle db related things
*/
class Db {

  private static $conn;

  private static $dbName;
  private static $dbUser;
  private static $dbPass;
  private static $dbAttrib;

  const ACTION_INSERT = 'INSERT';
  const ACTION_UPDATE = 'UPDATE';


  /**
  * Init db class.
  */
  public static function init($dbName, $dbUser, $dbPass, $dbAttrib) {
    self::$dbName = $dbName;
    self::$dbUser = $dbUser;
    self::$dbPass = $dbPass;
    self::$dbAttrib = $dbAttrib;
    self::$conn = null;
  }  // eo init


  /**
  * Get connection
  */
  public static function getConn() {
    if (self::$conn === null) {
      self::$conn = new PDO(self::$dbName, self::$dbUser, self::$dbPass);
      if (self::$dbAttrib['errorMode']) {
        self::$conn->setAttribute(PDO::ATTR_ERRMODE, self::$dbAttrib['errorMode']);
      }
      // TODO maybe the following settings are mysql specific?
      if (self::$dbAttrib['connectionCharset']) {
        self::$conn->exec('SET CHARACTER SET ' . self::$dbAttrib['connectionCharset']);
      }
      if (self::$dbAttrib['connectionTimeZone']) {
        self::$conn->exec('SET time_zone = ' . self::$dbAttrib['connectionTimeZone']);
      }
   }
    return self::$conn;
  }  // eo get connection



  /**
  * Prepare statement with optional where part and more
  */
  public static function prepare($stmt, $where = null, $moreStmt = null) {
    if (is_string($where)) {
      $stmt .= " WHERE $where";
    }
    elseif (is_array($where)) {
      $stmt .= self::createWhereStmt($where, null, true);
    }

    if ($moreStmt) {
      $stmt .= " $moreStmt";
    }

    self::getConn();
    return self::$conn->prepare($stmt);

  }  // eo prepare



  /**
  * Check parameters for statement.
  * Only used for debugging, because the error messages of the pdo-drivers are very sparingly.
  * @adjust: If params should be adjusted. Defaults to false.
  *   - true: params are adjusted silently and no error message is thrown
  *   - false: An error message is thrown if the params does not fit.
  */
  public static function checkStmtParams($stmt, &$params = array(), $returnMsg = false, $adjust = false) {

    // check for required params
    $requiredParams = self::getStmtPlaceHolder($stmt);
    foreach ($requiredParams as $key) {
      // remove leading ':'
      $key = substr($key, 1);
      // check with and without ':'
      if (!array_key_exists($key, $params) && !array_key_exists(':' . $key, $params)) {
        if ($adjust) {
          $params[$key] = ''; // fill with empty string
        }
        else {
          $msg .= "Required key $key not found in params.\n";
        }
      }
    }  // eo foreach required param

    // check for too much params (work on copy)
    $tmp = $params;
    foreach ($tmp as $key => $value) {
      // param must have ':' prefix for this check to match placeholder in statement
      if (substr($key, 0, 1) != ':') {
        $key = ':' . $key;
      }
      if (!array_key_exists($key, $requiredParams)) {
        if ($adjust) {
          unset($params[$key]); // remove array entry
        }
        else {
          $msg .= "No statement placeholder found for param key $key.\n";
        }
      }
    }  // eo foreach given param

    // check for duplicates (key exists with and without ':')
    // I hope php drops one of this silently! If not we can add this later.

    // if errormessage than return or throw exception
    if ($msg) {
      $msg = "Sql prepare statement check failure: $msg in $stmt with given parameters: " . str_replace("\n", '' ,var_export($params, true));
      if ($returnMsg) {
        return $msg;
      }
      else {
        throw new Exception($msg);
      }
    }

  }  // eo check statement params





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
  * create where clause for prepared statement
  */
  public static function createWhereStmt($fields, $andOr = 'AND', $withWhere = false) {

    $stmt = '';

    // allow "parameter skiping"
    if (!$andOr) {
      $andOr = 'AND';
    }


    // try to detect associative arrays and use array_keys instead
    if (OgerFunc::isAssoc($fields)) {
      $fields = array_keys($fields);
    }


    // create where clause
    foreach ($fields as $fieldName) {
      // skip empty field names
      if (!$fieldName) {
        continue;
      }
      $stmt .= ($stmt ? " $andOr " : '') . "`$fieldName`" . '=:' . $fieldName;
    }

    // return nothing if no statement created
    if (!$stmt) {
      return '';
    }

    return ($withWhere ? ' WHERE ' : '') . $stmt;

  } // end of create where for prepared statement


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
        $stmtField .= "`$field`";
        $stmtValue .= ':' . $field;
      }
      $stmt .= "INSERT INTO `$table` ($stmtField) VALUES ($stmtValue)";
      break;
    case self::ACTION_UPDATE:
      $stmt .= "UPDATE `$table` SET ";
      for ($i=0; $i < count($fields); $i++) {
        if ($i > 0) $stmt .= ',';
        $field = $fields[$i];
        $stmt .= "`$field`" . '=:' . $field;
      }
      break;
    default:
      throw new Exception('Unknown Db::action: ' . $action . '.');
    }

    if ($where) $stmt .= ' WHERE ' . $where;

    return $stmt;

  } // end of create prepared statement




}   // end of class

?>
