<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRSampleData_BaseImporterTest
 *
 * @group headless
 */
class CRM_HRSampleData_BaseCSVProcessorTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface
{
  protected $rows;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->install('uk.co.compucorp.civicrm.hrleaveandabsences')
      ->install('org.civicrm.hrjobcontract')
      ->install('com.civicrm.hrjobroles')
      ->install('org.civicrm.hrrecruitment')
      ->install('org.civicrm.hremergency')
      ->install('org.civicrm.hrdemog')
      ->install('org.civicrm.hrbank')
      ->install('uk.co.compucorp.civicrm.tasksassignments')
      ->install('org.civicrm.hrcase')
      ->apply();
  }

  public function runProcessor($className, $rows, $mapping = []) {

    $fileHandler = $this->getSplFileObjectMock($rows);
    $csvProcessor = new CRM_HRSampleData_CSVProcessor($fileHandler);

    $entityProcessor = new $className();

    if (!empty($mapping)) {
      foreach($mapping as $map) {
        $entityProcessor->setDataMapping($map[0], $map[1], $map[1]);
      }
    }

    $csvProcessor->process($entityProcessor);
  }

  protected function apiGet($entity, $extraParams = []) {
    $defaultParams = [
      'sequential' => 1,
      'options' => ['limit' => 1],
    ];
    $params = array_merge($defaultParams, $extraParams);

    $fetchResult = civicrm_api3($entity, 'get', $params);

    $fetchResult = array_shift($fetchResult['values']);

    return $fetchResult;
  }

  /**
   * @param array $rows
   *   An array of rows to be imported. The first rows contains the fields names,
   *   and the second row contains the field values
   * @param array $entity
   *   The entity array, as returned by the API
   * @param array $fieldsToIgnore
   *   An array with names of fields that will be not checked
   */
  protected function assertEntityEqualsToRows($rows, $entity, $fieldsToIgnore = []) {
    foreach($rows[0] as $index => $fieldName) {
      if(in_array($fieldName, $fieldsToIgnore)) {
        continue;
      }

      $this->assertEquals(
        $rows[1][$index],
        $entity[$fieldName],
        "The value of {$fieldName} was expected to be {$this->rows[1][$index]}, but it is {$entity[$fieldName]}"
      );
    }
  }

  private function getSplFileObjectMock($rows) {
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

}
