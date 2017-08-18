<?php

use CRM_Hrjobcontract_Factory_ImportParser as ImportParserFactory;

trait CRM_Hrjobcontract_Import_ParserTrait {

  /**
   * Returns the correct parser class based on the import mode
   * using the CRM_Hrjobcontract_Factory_ImportParser class.
   *
   * @return \CRM_Hrjobcontract_Import_Parser_BaseClass
   */
  public function getParser($importMode, $mapperKeys, $mapperLocTypes = NULL) {
    return ImportParserFactory::create($importMode, $mapperKeys, $mapperLocTypes);
  }
}
