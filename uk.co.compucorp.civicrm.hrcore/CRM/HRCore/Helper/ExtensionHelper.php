<?php

/**
 * Helper class for convenience methods related to CiviCRM extensions
 */
class CRM_HRCore_Helper_ExtensionHelper {

  /**
   * Checks if an extension is installed or enabled
   *
   * @param string $key
   *   Extension unique key
   *
   * @return bool
   */
  public static function isExtensionEnabled($key) {
    $isEnabled = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_Extension',
      $key,
      'is_active',
      'full_name'
    );

    return !empty($isEnabled) ? TRUE : FALSE;
  }

}
