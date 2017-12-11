<?php

/**
 * Implement this interface when providing a class to interact with system
 * variables for a CMS.
 */
interface CRM_HRCore_CMSData_Variable_VariableServiceInterface {

  /**
   * Gets a system variable.
   *
   * @param string $key
   *
   * @return mixed
   */
  public function get($key);

  /**
   * Sets a system variable.
   *
   * @param string $key
   * @param mixed $value
   *
   * @return void
   */
  public function set($key, $value);

}
