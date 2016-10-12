<?php

class CRM_CiviHR {
  /**
   * Get version value from info.xml file.
   *
   * @return string
   */
  public static function getVersion() {
    $info = CRM_Extension_Info::loadFromFile(__DIR__ . '/../info.xml');
    return $info->version;
  }
}
