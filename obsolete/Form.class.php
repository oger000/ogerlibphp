<?PHP
/*
#LICENSE BEGIN
**********************************************************************
* OgerArch - Archaeological Database is released under the GNU General Public License (GPL) <http://www.gnu.org/licenses>
* Copyright (C) Gerhard Öttl <gerhard.oettl@ogersoft.at>
**********************************************************************
#LICENSE END
*/



// include smarty once here
require_once Config::$smartyProgDir . DIRECTORY_SEPARATOR . 'Smarty.class.php';

/**
* OBSOLETED by use of extjs
* If ever reactivated try to make independend of config class
*/
class Form {

  private static $smarty;

  public static $smartyProgDir;
  public static $smartyTemplateDir;
  public static $smartyCompileDir;
  public static $smartyPluginsDir;

  /**
  * Init form class.
  */
  public static function init($smartyProgDir, $smartyTemplateDir, $smartyCompileDir, $smartyPluginsDir) {
    self::$smartyProgDir = $smartyProgDir;
    self::$smartyTemplateDir = $smartyTemplateDir;
    self::$smartyCompileDir = $smartyCompileDir;
    self::$smartyPluginsDir = $smartyPluginsDir;
  }  // eo init


  /**
  * get smarty instance
  */
  public static function getSmarty() {

    if (!self::$smarty) {
      // init smarty
      self::$smarty = new Smarty();
      self::$smarty->template_dir = self::$smartyTemplateDir;
      self::$smarty->compile_dir = self::$smartyCompileDir;
      foreach (explode(':', self::$smartyPluginsDirs) as $dir) {
        self::$smarty->plugins_dir[] = trim($dir);
      }
    }

    return self::$smarty;
  }


  /**
  * display template
  */
  public static function display($template) {

    self::getSmarty();

    // if exists a ext js file prefere this
    /*
    if (file_exists(self::$smarty->template_dir . '/' . $template . '.ext.js')) {
      self::$smarty->assign('extJsFile', $template . '.ext.js');
      $template = 'index.extjs.tpl';
    }
    */

    self::getSmarty()->display($template);

  }  // end of display template


  /**
  * assign variables
  */
  public static function assign($var1, $var2 = null) {
    self::getSmarty()->assign($var1, $var2);
  }


  /**
  * add errormessage
  */
  public static function addErrorMsg($text, $errorVar = 'errorMsg') {
    //self::getSmarty()->append($errorVar, $text . "<BR>\n");
    self::getSmarty()->assign($errorVar, self::$smarty->getTemplateVars($errorVar) . $text . "<BR>\n");
  }


}

?>
