<?php
/*
#LICENSE BEGIN
#LICENSE END
*/



/**
 * Extended XML handling
 */

class OgerXML {



  /*
   * convert array to xml
   */
  public static function arrayToXml($rootName, $array) {

    $writer = new XMLWriter();
    $writer->openMemory();
    $writer->setIndent(true);
    $writer->setIndentString(' ');

    $writer->startDocument('1.0', 'UTF-8');

    $writer->startElement($rootName);
    self::arrayToXmlWorker($writer, $array);
    $writer->endElement();

    $writer->endDocument();

    return $writer->outputMemory();
  }  // eo convert array to xml


  /*
   * convert an single array item to xml
   */
  private static function arrayToXmlWorker($writer, $array) {

    foreach($array as $key => $value) {

      $tagName = $key;
      if (is_numeric($key)) {
        $tagName = "__ITEM__";
      }

      $writer->startElement($tagName);

      if (is_array($value)) {
        self::arrayToXmlWorker($writer, $value);
      }
      else {
        $writer->text($value);
      }

      $writer->fullEndElement();

    }  // eo array

  } // eo array to xml worker





}  // eo class


?>
