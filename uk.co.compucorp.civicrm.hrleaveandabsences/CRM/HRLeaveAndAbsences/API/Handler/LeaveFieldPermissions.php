<?php

abstract class CRM_HRLeaveAndAbsences_API_Handler_LeaveFieldPermissions {

  /**
   * Gets the restricted fields that needs to have their values replaced in
   * the result set.
   *
   * @return array
   */
  abstract protected function getRestrictedFields();

  /**
   * Function to get the new field value if the field is among the restricted
   * fields list.
   *
   * @param string $field
   * @param mixed $value
   * @param mixed $dataIdentifier
   *
   * @return mixed
   */
  abstract protected function getNewFieldValue($field, $value, $dataIdentifier);

  /**
   * The identifier field for each result row set, This field if specified will
   * have its value passed to the getNewFieldValue function when called. It allows
   * the extending class to use the value to make some decisions regarding the value to be
   * replaced for the current restricted field.
   *
   * @return string
   */
  abstract protected function getDataRowIdentifierKey();

  /**
   * The level of the identifier field for each result row set.
   * The level depends on the position of the field in the nested array
   * result row set.
   *
   * @return int
   */
  abstract protected function getDataRowIdentifierLevel();

  /**
   * Used to temporarily store the current value of the identifier field for a result
   * row set.
   *
   * @var string
   */
  protected $currentDataRowIdentifierValue = '';

  /**
   * @var bool
   */
  protected $removeDataRowIdentifier = FALSE;

  /**
   * Processes the result set and replaces field values as necessary.
   *
   * @param array $results
   */
  public function process(&$results) {
    $this->filterFields($results['values']);
  }

  /**
   * Filters the fields and replaces values for restricted fields as necessary.
   *
   * @param array $data
   * @param int $level
   */
  protected function filterFields(&$data, $level = 0) {
    foreach($data as $field => &$value) {
      if($this->getDataRowIdentifierKey() && $this->getDataRowIdentifierLevel() && $level == 0) {
        $rowData = [$field => $value];
        $this->setCurrentDataRowIdentifierValue($rowData);
      }

      if ($this->removeDataRowIdentifier && $field === $this->getDataRowIdentifierKey() && $level === $this->getDataRowIdentifierLevel()) {
        unset($data[$field]);
      }

      if(array_key_exists($field, $this->getRestrictedFields()) && $level == $this->getRestrictedFields()[$field]['level']) {
        $oldValue = $value;
        $data[$field] = $this->getNewFieldValue($field, $oldValue, $this->getCurrentDataRowIdentifierValue());
      }

      if(is_array($value)) {
        $this->filterFields($value, $level + 1);
      }
    }
  }

  /**
   * Sets the Identifier field value for the current result row set.
   * It resets the value for the previous result row set first
   * before attempting to store the value for the current row.
   *
   * @param array $data
   * @param int $level
   */
  private function setCurrentDataRowIdentifierValue($data, $level = 0) {
    if ($level == 0) {
      $this->currentDataRowIdentifierValue = '';
    }

    foreach($data as $field => $value) {
      if($this->currentDataRowIdentifierValue) {
        break;
      }

      if($field == $this->getDataRowIdentifierKey() && $level == $this->getDataRowIdentifierLevel()) {
        $this->currentDataRowIdentifierValue = $value;
        break;
      }

      if(is_array($value)) {
        $this->setCurrentDataRowIdentifierValue($value, $level + 1);
      }
    }
  }

  /**
   * Gets the Identifier field value for the current result row set
   *
   * @return string
   */
  private function getCurrentDataRowIdentifierValue() {
    return $this->currentDataRowIdentifierValue;
  }
}
