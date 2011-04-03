<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/


/**
* Extends pdf library.
* Should work for FPDF and TCPDF.
*/
/*
require_once('lib/fpdf/fpdf.php');
class OgerPdf extends FPDF {
*/
require_once('lib/tcpdf/tcpdf.php');
class OgerPdf extends TCPDF {

  /**
  * Constructor.
  */
  public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4',           // FPDF
                              $unicode = true, $encoding = 'UTF-8', $diskcace = false) {  // additional parameters for TCPDF

    parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskspace);

  }  // eo constructor




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
  * Use template
  * @tpl: Template
  * @params: assocoiative array with variableName => value pairs.
  */
  public function tplUse($tpl, $params = array()) {

    $tpl = str_replace("\r\n", "\n", $tpl);
    $tpl = str_replace("\r", "\n", $tpl);

    // remove blocks
    $tpl = preg_replace('/^\{.*?^\}/ms', '', $tpl);

    $lines = explode("\n", $tpl);
    foreach ($lines as $line) {

      $line = ltrim($line);
      if (substr($line, 0, 1) == '#' || substr($line, 0, 2) == '//' || !$line) {
        continue;
      }

      // replace params
      foreach ($params as $key => $value) {
        $line = str_replace("{" . $key . "}", $value, $line);
      }

      list ($code, $opts, $text) = explode(':', $line, 3);
      $opts = $this->tplParseOpts($opts);

      if (get_parent_class($this) == 'FPDF') {
        $text = utf8_decode($text);
      }

      switch (trim($code)) {
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
  * Parse opts from template
  */
  public function tplParseOpts(&$opts, $inBlock = false) {

    // if not in block than this is the inial call
    // and we have to prepare the opts string
    if (!$inBlock) {
      $opts = str_replace(' ', '', $opts);
      $opts = str_replace('~', ' ', $opts);
    }

    $optBlock = array();
    $value = '';
    while (strlen($opts) > 0) {
      $char = substr($opts, 0, 1);
      $opts = substr($opts, 1);
      switch ($char) {
      case ',':
      case ']':
        if (substr($value, 0, 1) == "=") {
          $value = OgerFunc::evalMath(substr($value, 1));
        }
        $optBlock[] = $value;
        $value = '';
        break;
      case '[':
        $optBlock[] = $this->tplParseOpts($opts, true);
        break;
      default:
        $value .= $char;
      }

      // end of block
      if ($char == ']') {
        // if closing char is followed by a comma
        // than remove this to avoid undesired empty extraoption
        if (substr($opts, 0, 1) == ',') {
          $opts = substr($opts, 1);
        }
        return $optBlock;
      }

    }  // eo char loop

    // script should only reach this point only at top level of recursion
    // otherwise if the last block is not closed with ']'
    // Try to correct silently by adding current value (or an empty one)
    if ($inBlock) {
      if (substr($value, 0, 1) == "=") {
        $value = OgerFunc::evalMath(substr($value, 1));
      }
      $optBlock[] = $value;
    }

    return $optBlock;

  }  // eo parse tpl opts


  /**
  * Get marked blocks from template
  */
  public function tplGetBlocks($tpl) {

    preg_match_all('/^\{(.*?$)(.*?)^\}/ms', $tpl, $matches);

    $blocks = array();
    for ($i = 0; $i < count($matches[1]) ; $i++) {
      $blocks[trim($matches[1][$i])] = trim($matches[2][$i]);
    }

    return $blocks;
  }  // get marked blocks


  /**
  * Get named block from template
  */
  public function tplGetBlock($tpl, $name) {
    $blocks = $this->tplGetBlocks($tpl);
    return $blocks[$name];
  }  // get named block

  /**
  * Set X and Y coordinate from template notation
  */
  public function tplSetXY($opts) {

    list ($x, $y) = $opts;

    if ($x === '' || $x === null) { $x = $this->getX(); }
    if ($y === '' || $y === null) { $y = $this->getY(); }
    $this->setXY($x, $y);

  }  // eo tpl set xy


  /**
  * Set font from template notation
  */
  public function tplSetFont($opts) {

    list($family, $style, $size) = $opts;
    $this->setFont($family, $style, $size);

  }  // eo tpl set font


  /**
  * Output rectangle
  */
  public function tplRect($rect, $border, $fill) {

    list($x, $y, $width, $height, $style) = $rect;
    $this->Rect($x, $y, $width, $height, $style);

  }  // eo tpl set font


  /**
  * Output cell from template notation
  */
  public function tplCell($opts, $text) {

    list($width, $height, $borderInfo, $ln, $align, $fillInfo, $link) = $opts;

    if ($borderInfo) {
      list ($border, $thick, $color) = $borderInfo;
      if ($thick !== '' && $thick !== null) {
        $this->SetLineWidth($thick);
      }
      if ($color !== '' && $color !== null) {
        list ($red, $grenn, $blue) = explode('!', $color);
        $this->SetDrawColor($red, $green, $blue);
      }
    }  // eo border info

    if ($fillInfo) {
      list ($fill, $color) = $fillInfo;
      if ($color !== '' && $color !== null) {
        list ($red, $green, $blue) = $color;
        $this->SetFillColor($red, $green, $blue);
      }
    }  // eo border info

    $this->ClippedCell($width, $height, $text, $border, $ln, $align, $fill, $link);

  }  // eo tpl cell output


  ########## TEMPLATE END ##########








}  // end of class

?>
