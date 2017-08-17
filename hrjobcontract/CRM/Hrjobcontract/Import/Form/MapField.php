<?php
class CRM_Hrjobcontract_Import_Form_MapField extends CRM_Hrjobcontract_Import_Form_MapFieldBaseClass {
  protected $_mappingType = 'Import Contracts';

  protected function getParser() {
    if($this->_importMode == CRM_Hrjobcontract_Import_Parser::UPDATE_ENTITLEMENTS) {
      return CRM_Hrjobcontract_Import_Parser_EntitlementUpdate::class;
    }
    else {
      return CRM_Hrjobcontract_Import_Parser_Api::class;
    }
  }
}
