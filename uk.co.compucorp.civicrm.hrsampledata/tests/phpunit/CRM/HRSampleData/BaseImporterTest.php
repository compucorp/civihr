<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRSampleData_BaseImporterTest
 *
 * @group headless
 */
class CRM_HRSampleData_BaseImporterTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface
{
  protected $rows;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->install('org.civicrm.hrjobcontract')
      ->install('org.civicrm.hrabsence')
      ->install('com.civicrm.hrjobroles')
      ->install('org.civicrm.hrrecruitment')
      ->install('org.civicrm.hremergency')
      ->install('org.civicrm.hrdemog')
      ->install('org.civicrm.hrbank')
      ->apply();
  }

  public function getSplFileObjectMock($rows) {
    $calls = 0;
    $rowsCount = count($rows);

    $splObjectMock = $this->getMock('SplFileObject', [], ['php://memory']);

    $splObjectMock
      ->method('fgetcsv')
      ->will($this->returnCallback(function() use (&$calls, $rows) {
        return $rows[$calls++];
      } ));

    $splObjectMock
      ->method('eof')
      ->will($this->returnCallback(function() use (&$calls, $rowsCount) {
        return ($calls < $rowsCount) ? false : true;
      } ));

    return $splObjectMock;
  }

  public function runImporter($importerClassName, $rows, $mapping = []) {

    $importer = new $importerClassName();

    if (!empty($mapping)) {
      foreach($mapping as $map) {
        $importer->setDataMapping($map[0], $map[1], $map[1]);
      }
    }

    $fileHandler = $this->getSplFileObjectMock($rows);
    $importer->import($fileHandler);
  }

  public function apiGet($entity, $extraParams = []) {
    $defaultParams = [
      'sequential' => 1,
      'options' => ['limit' => 1],
    ];
    $params = array_merge($defaultParams, $extraParams);

    $fetchResult = civicrm_api3($entity, 'get', $params);

    $fetchResult = array_shift($fetchResult['values']);

    return $fetchResult;
  }

}
