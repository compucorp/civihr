<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_CiviHRSampleData_BaseImporterTest
 *
 * @group headless
 */
class CRM_CiviHRSampleData_BaseImporterTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface
{

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
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

    $fileHandler = $this->getSplFileObjectMock($rows);

    $importer = new $importerClassName();
    $importer->setSplFileObject($fileHandler);

    if (!empty($mapping)) {
      foreach($mapping as $map) {
        $importer->setDataMapping($map[0], $map[1], $map[1]);
      }
    }

    $importer->import();
  }

  public function apiQuickGet($entity, $key = null, $value = null, $extraParams = []) {
    $defaultParams = [
      'sequential' => 1,
      'options' => ['limit' => 1],
    ];
    $params = array_merge($defaultParams, $extraParams);

    if (!empty($key) && !empty($value)) {
      $params = array_merge($params, [$key => $value]);
    }

    $fetchResult = civicrm_api3($entity, 'get', $params);

    $fetchResult = array_shift($fetchResult['values']);

    if (!empty($key) && !empty($value)) {
      $fetchResult = $fetchResult[$key];
    }

    return $fetchResult;
  }

}
