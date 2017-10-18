<?php

/**
 * A Setting is a pair of name and value. A implementation of a Settings manager
 * should be able to store the setting somewhere and, given a setting name,
 * retrieve its value.
 */
interface CRM_HRLeaveAndAbsences_Service_SettingsManager {

  /**
   * Returns the value of the given setting. If the setting doesn't exist, then
   * null will be returned.
   *
   * @param string $setting
   *
   * @return mixed|null
   */
  public function get($setting);

  /**
   * Sets the value of the given settting. The value can be of any type, except
   * objects. The reason is that we want all the implementations to support the
   * same types, and the CiviCRM API doesn't support them.
   *
   * @param string $setting
   * @param mixed $value
   */
  public function set($setting, $value);
}
