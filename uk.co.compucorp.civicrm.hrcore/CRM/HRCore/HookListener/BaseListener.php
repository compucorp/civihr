<?php

class CRM_HRCore_HookListener_BaseListener {

  protected function isExtensionEnabled($key) {
    $isEnabled = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_Extension',
      $key,
      'is_active',
      'full_name'
    );

    return !empty($isEnabled) ? true : false;
  }
}
