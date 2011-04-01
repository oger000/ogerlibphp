<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/



require_once('lib/fpdf/fpdf.php');


/**
* Extends pdf library.
* Should work for FPDF and TCPDF.
*/
class OgerPdf extends FPDF {

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

    $tpl = str_replace("\r\n", "\n");
    $tpl = str_replace("\r", "\n");

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
  public ClippedCell($width, $height, $text, $border = 0, $ln = 0, $align = '', $fill = 0, $link = null);

    while (strlen($text) > 0 && $this->GetStringWidth($text) > $width) {
      $text = substr(0, -1, $text);
    }
    $this->Cell($width, $height, $text, $border, $ln, $align, $fill, $link);

  }  // eo clipped cell


  ########## TEMPLATE BEGIN ##########

  /**
  * Set X and Y coordinate from template notation
  */
  public function tplSetXY($opts) {

    list ($x, $y) = explode(',', $opts);

    if ($x !== '' || $x === null) { $this->setX($x); }
    if ($y !== '' || $y === null) { $this->setY($y); }

  }  // eo tpl set xy


  /**
  * Set font from template notation
  */
  public function tplSetFont($opts) {

    list($family, $style, $size) = explode(',', $opts);
    $this->setFont($family, $style, $size);

  }  // eo tpl set font


  /**
  * Output cell from template notation
  */
  public function tplCell($opts, $text) {

    list($width, $height, $border, $ln, $align, $fill, $link) = explode(',', $opts);
    $this->ClippedCell($width, $height, $text, $border, $ln, $align, $fill, $link);

  }  // eo tpl cell output


  ########## TEMPLATE END ##########








}  // end of class

?>
