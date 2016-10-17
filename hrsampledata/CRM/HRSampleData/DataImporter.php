<?php


abstract class CRM_HRSampleData_DataImporter
{

  /**
   * @var array A variable to cache common import data , such as option values mapping
   * Contact IDs ... etc .
   */
  protected static $data = [];

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
   * Explain stuff here ....
   * @param array $row
   *
   */
  protected abstract function insertRecord(array $row);


  protected function callAPI($entity, $action, $params) {
    $params = array_merge($params, ['debug' => 1]);
    return civicrm_api3($entity, $action, $params);
  }

  protected function setDataMapping($mappingKey, $oldValue, $newValue) {
    self::$data[$mappingKey][$oldValue] = $newValue;
  }

  protected function getDataMapping($mappingKey, $oldValue) {
    return self::$data[$mappingKey][$oldValue];
  }

  protected function getFixData($entity, $keyField, $valueField, $extra = []) {
    $params = array_merge($extra, [
      'sequential' => 1,
      'return' => [$keyField, $valueField],
      'options' => ['limit' => 0],
    ]);
    $data = $this->callAPI($entity, 'get', $params);

    return $this->dataToKeyValue($data, $keyField, $valueField);
  }

  protected function unsetArrayElement(&$list, $key) {
    $value = $list[$key];
    unset($list[$key]);

    return $value;
  }

  private function dataToKeyValue($values, $key, $value) {
    $result = [];
    foreach($values['values'] as $row) {
      $result[$row[$key]] = $row[$value];
    }

    return $result;
  }

}
