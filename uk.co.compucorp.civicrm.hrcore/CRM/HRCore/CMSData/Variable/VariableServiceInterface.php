<?php

interface CRM_HRCore_CMSData_Variable_VariableServiceInterface {

  /**
   * @param string $key
   *
   * @return mixed
   */
  public function get($key);

  /**
   * @param string $key
   * @param mixed $value
   *
   * @return void
   */
  public function set($key, $value);

}
