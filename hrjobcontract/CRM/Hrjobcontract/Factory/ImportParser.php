<?php

use CRM_Hrjobcontract_Import_Parser_EntitlementUpdate as EntitlementUpdateParser;
use CRM_Hrjobcontract_Import_Parser_Api as ApiParser;

/**
 * Creates Hrjobcontract_Import_Parser_BaseClass instances
 */
class CRM_Hrjobcontract_Factory_ImportParser {

  /**
   * This method returns the correct Parser instance needed for HRJobcontract
   * import process depending on the import mode.
   *
   * @param int $importMode
   * @param array $mapperKeys
   * @param array|null $mapperLocTypes
   *
   * @return \CRM_Hrjobcontract_Import_Parser_BaseClass
   */
  public static function create($importMode, $mapperKeys, $mapperLocTypes = NULL) {
    if ($importMode == CRM_Hrjobcontract_Import_Parser::UPDATE_ENTITLEMENTS) {
      return new EntitlementUpdateParser($mapperKeys, $mapperLocTypes);
    }

    return new ApiParser($mapperKeys, $mapperLocTypes);
  }
}
