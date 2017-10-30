<?php

use CRM_Hrjobcontract_Factory_ImportParser as ImportParserFactory;
use CRM_Hrjobcontract_Import_Parser_EntitlementUpdate as EntitlementUpdateParser;
use CRM_Hrjobcontract_Import_Parser_Api as ApiParser;
use CRM_Hrjobcontract_Import_Parser as ImportParser;

/**
 * Class CRM_Hrjobcontract_Factory_ImportParseTest
 *
 * @group headless
 */
class CRM_Hrjobcontract_Factory_ImportParserTest extends CRM_Hrjobcontract_Test_BaseHeadlessTest {

  public function testItCreatesAnApiParserWhenTheUpdateModeIsImportContracts() {
    $importMode = ImportParser::IMPORT_CONTRACTS;
    $this->assertInstanceOf(ApiParser::class, $this->returnParserInstance($importMode));
  }

  public function testItCreatesAnApiParserWhenTheUpdateModeIsImportRevisions() {
    $importMode = ImportParser::IMPORT_REVISIONS;
    $this->assertInstanceOf(ApiParser::class, $this->returnParserInstance($importMode));
  }

  public function testItCreatesAnEntitlementUpdateParserWhenTheUpdateModeIsUpdateEntitlements() {
    $importMode = ImportParser::UPDATE_ENTITLEMENTS;
    $this->assertInstanceOf(EntitlementUpdateParser::class, $this->returnParserInstance($importMode));
  }

  private function returnParserInstance($importMode) {
    $mapperKeys = [];

    return ImportParserFactory::create($importMode, $mapperKeys);
  }
}
