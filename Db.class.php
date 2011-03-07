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


    // try to detect associative arrays and use array_keys instead
    foreach ($fields as $key => $value) {
      if (!is_numeric($key)) {
        $fields = array_keys($fields);
        break;
      }
    } // eo detect associative array


    // create where clause
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
  * Get database structure (driver dependend).
  */
  public static function getDbStruc($dbDriver, $dbName) {

    $dbStruc = array();

    if ($dbDriver == 'mysql') {
      // $dbStruc = getDbStrucMysql($dbName);
      $dbStruc = getDbStrucInformationSchema($dbName);
    }
    elseif ($dbDriver == 'sqlite') {
      $dbStruc = getDbStrucMysql($dbName);
    }
    elseif ($dbDriver == 'ansiinformationschema') {
      $dbStruc = getDbStrucInformationSchema($dbName);
    }


    return $dbStruc;

  }  // eo get db struc



  /**
  * Get database structure (for sqlite databases).
  */
  public static function getDbStrucSqlite($dbName) {

    $dbStruc = array();

    /*

    // from http://www.sqlite.org/cvstrac/wiki?p=InformationSchema

    CREATE VIEW INFORMATION_SCHEMA_TABLES AS
      SELECT * FROM (
          SELECT 'main'     AS TABLE_CATALOG,
                 'sqlite'   AS TABLE_SCHEMA,
                 tbl_name   AS TABLE_NAME,
                 CASE WHEN type = 'table' THEN 'BASE TABLE'
                      WHEN type = 'view'  THEN 'VIEW'
                 END        AS TABLE_TYPE,
                 sql        AS TABLE_SOURCE
          FROM   sqlite_master
          WHERE  type IN ('table', 'view')
                 AND tbl_name NOT LIKE 'INFORMATION_SCHEMA_%'
          UNION
          SELECT 'main'     AS TABLE_CATALOG,
                 'sqlite'   AS TABLE_SCHEMA,
                 tbl_name   AS TABLE_NAME,
                 CASE WHEN type = 'table' THEN 'TEMPORARY TABLE'
                      WHEN type = 'view'  THEN 'TEMPORARY VIEW'
                 END        AS TABLE_TYPE,
                 sql        AS TABLE_SOURCE
          FROM   sqlite_temp_master
          WHERE  type IN ('table', 'view')
                 AND tbl_name NOT LIKE 'INFORMATION_SCHEMA_%'
      ) ORDER BY TABLE_TYPE, TABLE_NAME;

    */

    // Note, 12 Jan 2006: I reformatted this page so it was actually possible to read it,
    // but I did not debug the SQL code given. As stated, it does not work; any query on the view
    // gives the error "no such table: sqlite_temp_master". If you don't use temporary tables
    // you can just rip out the second inner SELECT (which then renders the outer SELECT unnecessary):

    /*

    CREATE VIEW INFORMATION_SCHEMA_TABLES AS
        SELECT 'main'     AS TABLE_CATALOG,
               'sqlite'   AS TABLE_SCHEMA,
               tbl_name   AS TABLE_NAME,
               CASE WHEN type = 'table' THEN 'BASE TABLE'
                    WHEN type = 'view'  THEN 'VIEW'
               END        AS TABLE_TYPE,
               sql        AS TABLE_SOURCE
        FROM   sqlite_master
        WHERE  type IN ('table', 'view')
               AND tbl_name NOT LIKE 'INFORMATION_SCHEMA_%'
        ORDER BY TABLE_TYPE, TABLE_NAME;

    }

    */


    return $dbStruc();

  } // eo db struc for mysql databases



  /**
  * Get database structure (for databases with ansi conform information schema).
  */
  public static function getDbStrucAnsiInformationSchema($dbName) {

    $dbStruc = array();

    $pstmt = self::prepare('SELECT TABLE_NAME FROM information_schema.tables WHERE information_schema.tables.table_schema=:dbName');
    $pstmt->execute(array('dbName' => $dbName));
    $tableRecords = $pstmt->fetchAll(PDO::FETCH_ASSOC);
    $pstmt->closeCursor();

    $tables = array();
    foreach ($tableRecords as $tableRecord) {

      $pstmt = self::prepare('SELECT * FROM information_schema.columns ' .
                             ' WHERE information_schema.columns.table_schema=:dbName AND ' .
                                   ' information_schema.columns.table_name=:tableName');
      $pstmt->execute(array('dbName' => $dbName, 'tableName' => $tableRecord['TABLE_NAME']));
      $columnRecords = $pstmt->fetchAll(PDO::FETCH_ASSOC);
      $pstmt->closeCursor();

      $colums = array();
      foreach ($columnRecords as $columnRecord) {
        $columns[$columnRecord['COLUMN_NAME']] = $columnRecord;
      }

      $tableRecord['columns'] = $columns;
      $tables[$tableRecord['TABLE_NAME']] = $tableRecord;

    }  // loop over table names


    $dbStruc[$dbName] = array('driver' => $dbDriver,
                              'name' => $dbName,
                              'tables' => $tables);


    #return $dbStruc;
    return $tables;

  }  // eo get db structure




}   // end of class

?>
