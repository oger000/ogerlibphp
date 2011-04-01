<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/



class OgerLogger {

  const LOG_NONE = 1;
  const LOG_FATAL = 2;
  const LOG_ERROR = 3;
  const LOG_WARN = 4;
  const LOG_LOG = 5;
  const LOG_VERBOSE = 6;
  const LOG_DEBUG = 7;

  public $printStack;

  private $level;
  private $logFile;

  private static $defaultLevel = self::LOG_ERROR;
  private static $levelText;


  /*
  * Constructor.
  */
  public function __construct($logFileName, $level = null, $printStack = false) {

    $this->level = self::$defaultLevel;
    $this->setLevel($level);

    $this->printStack = (bool) $printStack;

    // open logfile
    if (($this->logFile = fopen($logFileName, 'ab')) === false) {
      die("Cannot open logfile $logFileName.<BR>\n");
    }

    self::$levelText[self::LOG_NONE] = "None";
    self::$levelText[self::LOG_FATAL] = "Fatal";
    self::$levelText[self::LOG_ERROR] = "Error";
    self::$levelText[self::LOG_WARN] = "Warn";
    self::$levelText[self::LOG_LOG] = "Log";
    self::$levelText[self::LOG_VERBOSE] = "Verbose";
    self::$levelText[self::LOG_DEBUG] = "Debug";

  }  // end of constructor


  /*
  * Set log level
  */
  public function setLevel($level = null) {
    if ($level >= self::LOG_NONE &&
        $level <= self::LOG_DEBUG) {
      $this->level = $level;
    }
  }  // end of setting level


  /*
  * get log level
  */
  public function getLevel() {
    return $this->level;
  }  // end of setting level


  /*
  * write to logfile
  */
  public function write($message, $msgLevel, $printStack = null) {
    if ($msgLevel <= $this->level) {
      if (substr($message, -1) != "\n") {
        $message .= "\n";
      }
      fwrite($this->logFile, date("c") . ' ' . self::$levelText[$msgLevel] . ": " . $message);

      if ($printStack === null) {
        $printStack = $this->printStack;
      }
      if ($printStack) {
        fwrite($this->logFile, "\n*** Backtrace begin\n");
        $ex = new Exception();
        fwrite($this->logFile, $ex->getTraceAsString());
        fwrite($this->logFile, "\n*** Backtrace end\n\n");
      }
    }  // end of appropriate log level
  }  // end of writing log message


  /*
  * write fatal message (convenience function)
  */
  public function fatal($message, $printStack = null) {
    $this->write($message, self::LOG_FATAL, $printStack);
  }

  /*
  * write error message (convenience function)
  */
  public function error($message, $printStack = null) {
    $this->write($message, self::LOG_ERROR, $printStack);
  }

  /*
  * write warn message (convenience function)
  */
  public function warn($message, $printStack = null) {
    $this->write($message, self::LOG_WARN, $printStack);
  }
  /*

  * write log message (convenience function)
  */
  public function log($message, $printStack = null) {
    $this->write($message, self::LOG_LOG, $printStack);
  }

  /*
  * write verbose message (convenience function)
  */
  public function verbose($message, $printStack = null) {
    $this->write($message, self::LOG_VERBOSE, $printStack);
  }

  /*
  * write debug message (convenience function)
  */
  public function debug($message, $printStack = null) {
    $this->write($message, self::LOG_DEBUG, $printStack);
  }



}  // end of class
