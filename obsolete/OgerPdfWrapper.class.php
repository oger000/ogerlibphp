<?PHP
/*
#LICENSE BEGIN
**********************************************************************
* OgerArch - Archaeological Database is released under the GNU General Public License (GPL) <http://www.gnu.org/licenses>
* Copyright (C) Gerhard Öttl <gerhard.oettl@ogersoft.at>
**********************************************************************
#LICENSE END
*/




/**
* Wrap pdf functions. Extends functionality and add template handling.
*/
class OgerPdfWrapper {

  public $pdf;


  /**
  * Constructor.
  * @type: One of FPDF, TCPDF (for now only first char is significant)
  *   or a precreated pdf object that accepts the fpdf syntax.
  *   That means an instance of FPDF, TCPDF [TODO or OgerPdf].
  * @include: Path to include the requested pdf class.
  */
  public function __construct($type, $include = null,
                              $pdfOrientation = 'P', $pdfUnit = 'mm', $pdfFormat = 'A4',           // FPDF
                              $pdfUnicode = true, $pdfEncoding = 'UTF-8', $pdfDiskspace = false) {  // additional for TCPDF

    // preconstructed
    if (is_object($type)) {
      $this->pdf = $type;
      return;
    }

    switch ($type) {
    case 'FPDF':
      if (!$include) {
        $include = 'lib/fpdf/fpdf.php';
      }
      require_once($include);
      $this->pdf = new FPDF($pdfOrientation, $pdfUnit, $pdfFormat);
      break;
    case 'TCPDF':
      if (!$include) {
        $include = 'lib/tcpdf/tcpdf.php';
      }
      require_once($include);
      $this->pdf = new TCPDF($pdfOrientation, $pdfUnit, $pdfFormat, $pdfUnicode, $pdfEncoding, $pdfDiskspace);
      break;
    }

  }  // eo constructor


  /**
  * Oupt cell at position
  */
  public function CellAt($x, $y, ) {



}  // end of class

?>
