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
  * prepare statement and fill parameter array from request
  */
  public static function prepare($stmt, &$parms = array()) {
    if ($parms) {
      $parms = static::fillStmtParms($stmt, $parms);
      $parms = static::cleanStmtParms($stmt, $parms);
    }
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
  * create where clause for prepared statement
  */
  public static function createWhereStmt($fields, $andOr = 'AND', $withWhere = false) {

    $stmt = '';

    // allow "parameter skiping"
    if (!$andOr) {
      $andOr = 'AND';
    }

    foreach ($fields as $fieldName) {
      // skip empty field names
      if (!$fieldName) {
        continue;
      }
      $stmt .= ($stmt ? " $andOr " : '') . $fieldName . '=:' . $fieldName;
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
    default:
      throw new Exception('Unknown Db::action: ' . $action . '.');
    }

    if ($where) $stmt .= ' WHERE ' . $where;

    return $stmt;

  } // end of create prepared statement



  /**
  * Get database structure.
  * May be not driver independend???
  */
  public static function getDbStruc($dbDriver, $dbName) {

    $dbStruc = array();

    $pstmt = self::prepare('SELECT table_name FROM information_schema.tables WHERE information_schema.tables.table_schema=:dbName');
    $pstmt->execute(array('dbName' => $dbName));
    $tables = $pstmt->fetchAll(PDO::FETCH_ASSOC);
    $pstmt->closeCursor();

    foreach ($tables as $table) {

      $pstmt = self::prepare('SELECT * FROM information_schema.columns ' .
                             ' WHERE information_schema.columns.table_schema=:dbName AND ' .
                                   ' information_schema.columns.table_name=:tableName');
      $pstmt->execute(array('dbName' => $dbName, 'tableName' => $table['table_name']));
      $columns = $pstmt->fetchAll(PDO::FETCH_ASSOC);
      $pstmt->closeCursor();

      $table['columns'] = $columns;
      $dbStruc[] = $table;

    }  // loop over table names

    return $dbStruc;

  }  // eo get db structure


}   // end of class

?>
