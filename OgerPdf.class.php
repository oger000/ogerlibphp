<?PHP
/*
#LICENSE BEGIN
#LICENSE END
*/


/**
* Extends tcpdf library.
* NOT FULLY TESTED WITH FPDF
* Should work for FPDF too, but not all features are supported:
* - Maxheight of MultiCell
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

    while (strlen($text) > 0 && parent::GetStringWidth($text) > $width) {
      $text = substr($text, 0, -1);
    }
    parent::Cell($width, $height, $text, $border, $ln, $align, $fill, $link);

  }  // eo clipped cell


  ########## TEMPLATE BEGIN ##########


  /**
  * Use template
  * @tpl: Template
  * @params: assocoiative array with variableName => value pairs.
  */
  public function tplUse($tpl, $values = array(), $blockName = '') {

    // if a block name is given than only this block is used from the template
    if ($blockName) {
      $tpl = $this->tplGetBlock($tpl, $blockName);
    }

    // unify newlines
    $tpl = str_replace("\r", "\n", $tpl);

    $lines = explode("\n", $tpl);
    for ($i=0; $i < count($lines); $i++) {

      $line = $lines[$i];

      // commandpart and text are separated by #
      list($cmd, $text) = explode("#", $line, 2);
      // command parts and parseOpts are separated by "::"
      // Multiple parts are possible (may be also separated by "::")
      list($cmd, $moreLines) = explode("::", $cmd, 2);

      // read continous lines
      while ($moreLines-- > 0 && ++$i < count($lines)) {
        $text .= "\n" . $lines[$i];
      }

      // command and opts are separated with one or more spaces
      $cmd = trim($cmd);
      list($cmd, $opts) = explode(' ', $cmd, 2);
      $opts = trim($opts);

      // ignore empty commands
      if (!$cmd) {
        continue;
      }

      // ignore comments "//"
      if (substr($cmd, 0, 2) == '//') {
        continue;
      }

      // recognize and ignore blocks here
      if (substr($cmd, 0, 1) == '{') {
        while (++$i < count($lines)) {
          if (substr(trim($lines[$i]), 0, 1) == "}") {
            break;
          }
        }
        continue;
      }  // blocks


      // prepare text and substitute variables
      // MEMO: if preg_match_all is to slow we can try exploding at "{" etc
      preg_match_all('/(\{.*?\})/ms', $text, $varDefs);
      foreach ($varDefs[1] as $varDef) {  // loop over first matching braces
        $varName = trim(substr(substr($varDef, 1), 0, -1));  // remove {}
        list($varName, $format) = explode(" ", $varName, 2);
        $varName = trim($varName);
        $format = trim($format);
        if (array_key_exists($varName, $values)) {
          $value = $values[$varName];
          if ($format) {
            list($formatType, $format) = explode(':', $format, 2);
            $formatType = trim($formatType);
            switch ($formatType) {
            case "datetime":
              $value = date($format, strtotime($value));
              break;
            }  // eo formattye
          }
          $text = str_replace($varDef, $value, $text);
        }
      }


      // substitute variables in options too
      /* OBSOLETE:variables are only substituted in text
      preg_match_all('/(\{.*?\})/ms', $opts, $varDefs);
      foreach ($varDefs[1] as $varDef) {  // loop over first matching braces
        $varName = trim(substr(substr($varDef, 1), 0, -1));  // remove {}
        if (array_key_exists($varName, $values)) {
          $opts = str_replace($varDef, $values[$varName], $opts);
        }
      }
      */


      // execute command
      $this->tplExecuteCmd($cmd, $opts, $text, $i);

    }  // line loop

  }  // eo use template


  /**
  * Execute template command
  * @cmd: Command name.
  * @opts: Unparsed options string.
  * @text: Text.
  * @checkOnly: True to do a checkonly run without executing the command.
  */
  public function tplExecuteCmd($cmd, $opts, $text, $line) {

    $opts = $this->tplParseOpts($opts);

    // for fpdf we have to decode utf8 explicitly here
    if (get_parent_class($this) == 'FPDF') {
      $text = utf8_decode($text);
    }

    switch ($cmd) {
    case '//':
    case '#':        // # schould never happen - but anyway
      break;
    case 'INIT':
      $this->tplInitPdf($opts);
      break;
    case 'FONT':
      $this->tplSetFont($opts[0]);
      break;
    case 'LINEDEF':
      $this->tplSetLineDef($opts[0]);
      break;
    case 'DRAWCOL':
      $this->tplSetDrawCol($opts[0]);
      break;
    case 'FILLCOL':
      $this->tplSetFillCol($opts[0]);
      break;
    case 'RECT':
      list ($rect, $lineDef, $fill) = $opts;
      $this->tplRect($rect, $lineDef, $fill);
      break;
    case 'CELL':
      list ($cell, $font) = $opts;
      $this->tplSetFont($font);
      $this->tplCell($cell, $text);
      break;
    case 'CELLAT':
      list ($pos, $cell, $font) = $opts;
      $this->tplSetXY($pos);
      $this->tplSetFont($font);
      $this->tplCell($cell, $text);
      break;
    case 'MCELL':
      list ($cell, $font) = $opts;
      $this->tplSetFont($font);
      $this->tplMultiCell($cell, $text);
      break;
    case 'MCELLAT':
      list ($pos, $cell, $font) = $opts;
      $this->tplSetXY($pos);
      $this->tplSetFont($font);
      $this->tplMultiCell($cell, $text);
      break;
    default:
      throw new Exception("OgerPdf::tplExecuteCmd: Unknown command: $cmd in line $line.\n");
    } // eo cmd switch

  }  // eo execute template command



  /**
  * Parse opts from template
  */
  public function tplParseOpts(&$opts, $inBlock = false) {

    // if not in option-block than this is the initial call
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

    // script should reach this point only at top level of recursion
    // otherwise if the last option-block is not closed with ']'
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

    preg_match_all('/^\s*\{(.*?$)(.*?)^\s*\}/ms', $tpl, $matches);

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

    if ($x === '' || $x === null) { $x = parent::GetX(); }
    if ($y === '' || $y === null) { $y = parent::GetY(); }
    parent::SetXY($x, $y);

  }  // eo tpl set xy



  /**
  * Init pdf
  * The parameters are the same as in __construct, but for now only the first three ones
  */
  public function tplInitPdf($opts) {
    $orientation = $opts[0][0];
    $unit = $opts[0][1];
    $format = $opts[0][2];
    if ($orientation) {
      parent::setPageOrientation($orientation);
    }
    if ($unit) {
      parent::setPageUnit($unit);
    }
    if ($format) {
      parent::setPageFormat($format, $orientation);
    }
  }  // eo tpl init pdf



  /**
  * Set template font
  */
  public function tplSetFont($opts) {
    list($family, $style, $size) = $opts;
    parent::SetFont($family, $style, $size);
  }  // eo tpl set font



  /**
  * Set template line definition
  */
  public function tplSetLineDef($lineDef) {

    list($thick, $color) = $lineDef;
    if ($thick !== '' && $thick !== null) {
      parent::SetLineWidth($thick);
    }
    $this->tplSetDrawColor($color);
  }  // eo set line def



  /**
  * Set template draw color
  */
  public function tplSetDrawColor($color) {

    if (!$color) {
      return;
    }

    list($red, $green, $blue) = $color;
    parent::SetDrawColor($red, $green, $blue);
  }  // eo set draw color



  /**
  * Set template fill color
  */
  public function tplSetFillColor($color) {

    if (!$color) {
      return;
    }

    list($red, $green, $blue) = $color;
    parent::SetFillColor($red, $green, $blue);
  }  // eo set fill color



  /**
  * Output rectangle
  */
  public function tplRect($rect, $lineDef, $fill) {

    $this->tplSetLineDef($lineDef);
    $this->tplSetFillColor($fill);

    list ($x, $y, $width, $height, $style) = $rect;
    parent::Rect($x, $y, $width, $height, $style);

  }  // eo tpl set font



  /**
  * Output tmplate cell
  */
  public function tplCell($opts, $text) {

    list($width, $height, $borderDef, $ln, $align, $fillDef, $link) = $opts;

    if ($borderDef) {
      if (!is_array($borderDef)) {
        $borderDef = array($borderDef);
      }
      list ($border, $lineDef) = $borderDef;
      $this->tplSetLineDef($lineDef);
    }

    if ($fillDef) {
      if (!is_array($fillDef)) {
        $fillDef = array($fillDef);
      }
      list ($fill, $color) = $fillDef;
      $this->tplSetFillColor($color);
    }

    $this->ClippedCell($width, $height, $text, $border, $ln, $align, $fill, $link);

  }  // eo tpl cell output



  /**
  * Output template multicell
  */
  public function tplMultiCell($opts, $text) {

    list($width, $height, $borderDef, $align, $fillDef,
         $ln, $x, $y, $resetH, $stretch, $isHtml, $autoPadding, $maxHeight, $vAlign, $fitCell) = $opts;

    if ($borderDef) {
      if (!is_array($borderDef)) {
        $borderDef = array($borderDef);
      }
      list ($border, $lineDef) = $borderDef;
      $this->tplSetLineDef($lineDef);
    }

    if ($fillDef) {
      if (!is_array($fillDef)) {
        $fillDef = array($fillDef);
      }
      list ($fill, $color) = $fillDef;
      $this->tplSetFillColor($color);
    }

    parent::MultiCell($width, $height, $text, $border, $align, $fill,
                      $ln, $x, $y, $resetH, $stretch, $isHtml, $autoPadding, $maxHeight, $vAlign, $fitCell);

  }  // eo tpl multi cell

  ########## TEMPLATE END ##########








}  // end of class

?>
