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

      if (substr($line, 0, 1) == '#' || substr($line, 0, 2) == '//' || !$line) {
        continue;
      }

      // replace params
      foreach ($params as $key => $value) {
        $line = str_replace("{" . $key . "}", $value, $line);
      }

      list ($opts, $text) = explode('#', $line, 2);
      $opts = str_replace(' ', '', $opts);
      $opts = str_replace('~', ' ', $opts);
      $opts = explode(':', $opts);

      $code = array_shift($opts);

      switch ($code) {
      case 'FONT':
        $this->tplSetFont($opts[0]);
        break;
      case 'CELLAT':
        list ($pos, $cell, $font) = $opts;
        $this->tplSetXY($pos);
        $this->tplSetFont($font);
        $this->tplCell($cell, $text);
        break;
      case 'RECT':
        list ($rect, $border, $fill) = $opts;
        $this->tplRect($rect, $border, $fill);
        break;
      } // eo code

    }  // eo line loop

  }  // eo use template



  /**
  * Clip cell at given width
  */
  public function ClippedCell($width, $height, $text, $border = 0, $ln = 0, $align = '', $fill = 0, $link = null) {

    while (strlen($text) > 0 && $this->GetStringWidth($text) > $width) {
      $text = substr($text, 0, -1);
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

    if ($x === '' || $x === null) { $x = $this->getX(); }
    if ($y === '' || $y === null) { $y = $this->getY(); }
    $this->setXY($x, $y);

  }  // eo tpl set xy


  /**
  * Set font from template notation
  */
  public function tplSetFont($opts) {

    list($family, $style, $size) = $this->tplPrepOpts(explode(',', $opts));
    $this->setFont($family, $style, $size);

  }  // eo tpl set font


  /**
  * Output rectangle
  */
  public function tplRect($rect, $border, $fill) {

    list($x, $y, $width, $height, $style) = $this->tplPrepOpts(explode(',', $rect));
    $this->Rect($x, $y, $width, $height, $style);

  }  // eo tpl set font


  /**
  * Output cell from template notation
  */
  public function tplCell($opts, $text) {

    list($width, $height, $borderInfo, $ln, $align, $fillInfo, $link) = $this->tplPrepOpts(explode(',', $opts));

    if ($borderInfo) {
      list ($border, $thick, $color) = $this->tplPrepOpts(explode('|', $borderInfo));
      if ($thick !== '' && $thick !== null) {
        $this->SetLineWidth($thick);
      }
      if ($color !== '' && $color !== null) {
        list ($red, $grenn, $blue) = explode('!', $color);
        $this->SetDrawColor($red, $green, $blue);
      }
    }  // eo border info

    if ($fillInfo) {
      list ($fill, $color) = $this->tplPrepOpts(explode('|', $fillInfo));
      if ($color !== '' && $color !== null) {
        list ($red, $grenn, $blue) = explode('!', $color);
        $this->SetFillColor($red, $green, $blue);
      }
    }  // eo border info

    $this->ClippedCell($width, $height, $text, $border, $ln, $align, $fill, $link);

  }  // eo tpl cell output


  ########## TEMPLATE END ##########








}  // end of class

?>
