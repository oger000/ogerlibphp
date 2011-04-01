<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/



//utf8_decode()


/**
* Extends pdf library.
* Should work for FPDF and TCPDF.
*/
require_once('lib/fpdf/fpdf.php');
class OgerPdf extends FPDF {
//require_once('lib/tcpdf/tcpdf.php');
//class OgerPdf extends TCPDF {

  /**
  * Constructor.
  */
  public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4',           // FPDF
                              $unicode = true, $encoding = 'UTF-8', $diskcace = false) {  // additional for TCPDF

    parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskspace);

  }  // eo constructor


  /**
  * Use template
  * @tpl: Template
  * @params: assocoiative array with variableName => value pairs.
  */
  public function useTpl($tpl, $params = array()) {

    $tpl = str_replace("\r\n", "\n", $tpl);
    $tpl = str_replace("\r", "\n", $tpl);

    $lines = explode("\n", $tpl);
    foreach ($lines as $line) {

      // replace params
      foreach ($params as $key => $value) {
        $tpl = str_replace("{$key}", $value, $line);
      }

      list ($code, $line) = explode(':', $line, 2);
      list ($opts, $text) = explode('#', $line, 2);

      $opts = str_replace(' ', '', $opts);
      $opts = str_replace('~', ' ', $opts);

      switch ($code) {
      case 'FONT':
        $this->tplFont($font);
        break;
      case 'CELLAT':
        list ($pos, $cell, $font) = explode(':', $opts);
        $this->tplSetXY($pos);
        $this->tplSetFont($font);
        $this->tplCell($cell, $text);
        break;
      } // eo code

    }  // eo line loop

  }  // eo use template



  /**
  * Clip cell at given width
  */
  public function ClippedCell($width, $height, $text, $border = 0, $ln = 0, $align = '', $fill = 0, $link = null) {

    while (strlen($text) > 0 && $this->GetStringWidth($text) > $width) {
      $text = substr(0, -1, $text);
    }
    $this->Cell($width, $height, $text, $border, $ln, $align, $fill, $link);

  }  // eo clipped cell


  ########## TEMPLATE BEGIN ##########

  /**
  * Prepare opts from template
  */
  public function tplPrepOpts($opts) {

    foreach ($opts as $key => &$value) {
      if (substr($value, 0, 1) == "=") {
        $value = OgerFunc::evalMath(substr($value, 1));
      }
    }

    return $opts;
  }  // eo prepare tpl opts


  /**
  * Set X and Y coordinate from template notation
  */
  public function tplSetXY($opts) {

    list ($x, $y) = $this->tplPrepOpts(explode(',', $opts));
Dev::debug("x=$x, y=$y");
    if ($x !== '' && $x !== null) { $this->setX($x); }
    if ($y !== '' && $y !== null) { $this->setY($y); }

  }  // eo tpl set xy


  /**
  * Set font from template notation
  */
  public function tplSetFont($opts) {

    list($family, $style, $size) = $this->tplPrepOpts(explode(',', $opts));
    $this->setFont($family, $style, $size);

  }  // eo tpl set font


  /**
  * Output cell from template notation
  */
  public function tplCell($opts, $text) {

    list($width, $height, $border, $ln, $align, $fill, $link) = $this->tplPrepOpts(explode(',', $opts));
    $this->ClippedCell($width, $height, $text, $border, $ln, $align, $fill, $link);

  }  // eo tpl cell output


  ########## TEMPLATE END ##########








}  // end of class

?>
