<?php

use CRM_HRLeaveAndAbsences_Service_SettingsManager as SettingsManager;

/**
 * A CRM_HRLeaveAndAbsences_Service_SettingsManager implementation that stores
 * the settings in a array in memory.
 *
 * This main purpose of this class is to be used in tests and avoid caching
 * problems with the Setting API. Also, using in an actual/production
 * environment would not be very useful, as the settings would be lost after
 * every request.
 */
class CRM_HRLeaveAndAbsences_Service_InMemorySettingsManager implements SettingsManager {

  /**
   * @var array
   *   Array to store the settings
   */
  private $settings = [];

  /**
   * {@inheritdoc}
   */
  public function get($setting) {
    return isset($this->settings[$setting]) ? $this->settings[$setting] : null;
  }

  /**
   * {@inheritdoc}
   */
  public function set($setting, $value) {
    $this->settings[$setting] = $value;
  }
}
