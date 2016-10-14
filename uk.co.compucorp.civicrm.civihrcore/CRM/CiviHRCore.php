<?php

class CRM_CiviHRCore {
  /**
   * Get version value from info.xml. This method uses caching so it reads
   * xml file only once during CiviCRM session.
   *
   * @return string
   */
  public static function getVersion() {

    $version = CRM_Core_BAO_Cache::getItem('CiviHRCore', 'version');

    if (empty($version)) {
      $info = CRM_Extension_Info::loadFromFile(__DIR__ . '/../info.xml');
      $version = $info->version;
      CRM_Core_BAO_Cache::setItem($version, 'CiviHRCore', 'version');
    }

    return $version;
  }
}
