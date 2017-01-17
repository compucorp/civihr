<?php

use CRM_HRSampleData_CSVProcessingVisitor as CSVProcessingVisitor;

abstract class CRM_HRSampleData_CSVImporterVisitor extends CSVProcessingVisitor {

  /**
   * Caches common import data , such as option values mapping
   * Contact IDs ... etc .
   *
   * @var array
   */
  private static $data = [];

  /**
   * {@inheritdoc}
   */
  public function visit(array $row) {
    $this->importRecord($row);
  }

  /**
   * Import (insert) one row from the
   * CSV file into the database
   *
   * @param array $row
   *   CSV file row data
   */
  protected abstract function importRecord(array $row);

  /**
   * Stores data mapping for old and new ID or value.
   *
   * @param string $mappingKey
   *   The mapping key ( e.g: contact_mapping, activity_mapping .. etc )
   * @param int|string $oldValue
   *   The old ID or value usually come from the csv file
   * @param int|string $newValue
   *   The new ID or value after inserting data into the database.
   */
  public function setDataMapping($mappingKey, $oldValue, $newValue) {
    self::$data[$mappingKey][$oldValue] = $newValue;
  }

  /**
   * Gets stored mapping for specific mapping key and value.
   *
   * @param string $mappingKey
   * @param int|string $oldValue
   *
   * @return int|string
   */
  public function getDataMapping($mappingKey, $oldValue) {
    return self::$data[$mappingKey][$oldValue];
  }

  /**
   * Gets data for specific entity (e.g : Relationship Types) to be
   * used for fixing csv data to a valid API/DB format, such as
   * Converting relationship type name from the csv file to its actual
   * DB ID so it could be used in the API.
   *
   * @param string $entity
   *   The entity to fetch data for
   * @param string $keyField
   *   The name of the field to be fixed
   * @param string $valueField
   *   The name of the field to replace the original data.
   * @param array $extra
   *   Extra parameters to be passed to the API
   *
   * @return array In [`key`=>`ID|Value`, .. ] format
   */
  protected function getFixData($entity, $keyField, $valueField, $extra = []) {
    $params = array_merge($extra, [
      'sequential' => 1,
      'return' => [$keyField, $valueField],
      'options' => ['limit' => 0],
    ]);
    $data = $this->callAPI($entity, 'get', $params);

    return $this->dataToKeyValue($data['values'], $keyField, $valueField);
  }

  /**
   * Unset an array element based on its key and return its value.
   *
   * @param array $list
   *   The target array
   * @param int|string $key
   *   The target Key
   *
   * @return mixed
   *   The value of the removed element
   */
  protected function unsetArrayElement(&$list, $key) {
    $value = $list[$key];
    unset($list[$key]);

    return $value;
  }

  /**
   * Converts an Array with multiple keys to key => value format
   * by specifying the key field and value field.
   * So for example if you have the following array :
   * $targetArray = [
   * 0=> ['id' => 1, 'name' => 'john' , 'country' => 'italy', 'gender' => 'male' ],
   * 1=> ['id' => 2, 'name' => 'xing' , 'country' => 'china', 'gender' => 'male' ],
   * ]
   * then you can call this method with dataToKeyValue($targetArray, 'name', 'id')
   * to get this response :
   * ['john' => 1, 'xing' => 2 ]
   * or dataToKeyValue($response, 'country', 'name') to get :
   * ['italy' => 'john' , 'china' => 'xing']
   *
   * @param array $values
   *   The API response array
   * @param string $key
   *   The returned field name to be used as a key
   * @param string $value
   *   The returned field name to be used as a value
   *
   * @return array
   *   Array In [`key`=>`value`, ..] format
   */
  private function dataToKeyValue($values, $key, $value) {
    $result = [];
    foreach($values as $row) {
      $result[$row[$key]] = $row[$value];
    }

    return $result;
  }

}
