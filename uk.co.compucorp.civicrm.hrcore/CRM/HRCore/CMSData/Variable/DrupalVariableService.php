<?php

use CRM_HRCore_CMSData_Variable_VariableServiceInterface as VariableAdapterInterface;

class CRM_HRCore_CMSData_Variable_DrupalVariableService implements VariableAdapterInterface {

  /**
   * @inheritdoc
   */
  public function get($key) {
    return variable_get($key);
  }

  /**
   * @inheritdoc
   */
  public function set($key, $value) {
    variable_set($key, $value);
  }

}
