<?php


abstract class CRM_CiviHRSampleData_DataImporter
{

  /**
   * @var array A variable to cache common import data , such as option values mapping
   * Contact IDs ... etc .
   */
  protected static $data = [];

  /**
   * Import a csv file into the database.
   *
   * @param string $file the absolute path for the target csv file
   */
  public function import($file) {
    $header = null;

    if (($handle = fopen("{$file}", "r")) !== FALSE) {
      while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
        if ($header === null) {
          $header = $row;
          continue;
        }

        $row = array_combine($header, $row);
        $this->insertRecord($row);
      }
      fclose($handle);
    }
  }

  /**
   * Insert data from a row in the csv file
   * into the database.
   *
   * @param array $row Array of key=>value data to be inserted
   *
   */
  protected abstract function insertRecord(array $row);


  /**
   * A wrapper for civicrm API.
   *
   * @param string $entity A valid civicrm API entity
   * @param string $action A valid entity action (e.g : create,delete,get..etc)
   * @param array $params Parameters to be passed to the API method.
   * @return array
   */
  protected function callAPI($entity, $action, $params) {
    return civicrm_api3($entity, $action, $params);
  }

  /**
   * Store data mapping for old and new ID or value.
   *
   * @param string $mappingKey The mapping key ( e.g: contact_mapping, activity_mapping .. etc )
   * @param int|string $oldValue The old ID or value usually come from the csv file
   * @param int|string $newValue The new ID or value after inserting data into the database.
   */
  protected function setDataMapping($mappingKey, $oldValue, $newValue) {
    self::$data[$mappingKey][$oldValue] = $newValue;
  }

  /**
   * Get stored mapping for specific mapping key and value.
   *
   * @param string $mappingKey
   * @param int|string $oldValue
   * @return array
   */
  protected function getDataMapping($mappingKey, $oldValue) {
    return self::$data[$mappingKey][$oldValue];
  }

  /**
   * Get data for specific entity (e.g : Relationship Types) to be
   * used for fixing csv data to a valid API/DB format, Such as
   * Converting relationship type name from the csv file to its Actually
   * DB ID so it could be used in the API.
   *
   * @param string $entity The entity to fetch data for
   * @param string $keyField The name of the field to be fixed
   * @param string $valueField The name of the field to replace the original data.
   * @param array $extra Extra parameters to be passed to the API
   * @return array In [`key`=>`ID|Value`, .. ] format
   */
  protected function getFixData($entity, $keyField, $valueField, $extra = []) {
    $params = array_merge($extra, [
      'sequential' => 1,
      'return' => [$keyField, $valueField],
      'options' => ['limit' => 0],
    ]);
    $data = $this->callAPI($entity, 'get', $params);

    return $this->dataToKeyValue($data, $keyField, $valueField);
  }

  /**
   * Unset an array element based on its key and return its value.
   *
   * @param array $list The target array
   * @param int|string $key The target Key
   * @return mixed the value of the removed element
   */
  protected function unsetArrayElement(&$list, $key) {
    $value = $list[$key];
    unset($list[$key]);

    return $value;
  }

  /**
   * Convert civicrm API response to key=>value format
   * by specifying the key field and value field.
   *
   * @param array $values The API response array
   * @param string $key The returned field name to be used as a key
   * @param string $value The returned field name to be used as a value
   * @return array In [`key`=>`value`, ..] format
   */
  private function dataToKeyValue($values, $key, $value) {
    $result = [];
    foreach($values['values'] as $row) {
      $result[$row[$key]] = $row[$value];
    }

    return $result;
  }

}
