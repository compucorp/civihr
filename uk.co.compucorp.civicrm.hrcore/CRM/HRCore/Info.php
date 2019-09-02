<?php

class CRM_HRCore_Info {
  /**
   * Get version value from info.xml. This method uses caching so it reads
   * xml file only once during CiviCRM session.
   *
   * @return string
   */
  public static function getVersion() {

    $version = Civi::cache('HRCore_Info')->get('version');

    if (empty($version)) {
      $info = CRM_Extension_Info::loadFromFile(__DIR__ . '/../../info.xml');
      $version = $info->version;
      Civi::cache('HRCore_Info')->set('version', $version);
    }

    return $version;
  }
}
