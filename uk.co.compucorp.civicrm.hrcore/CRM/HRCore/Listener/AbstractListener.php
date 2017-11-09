<?php
  abstract class CRM_HRCore_Listener_AbstractListener {

    protected $objectClass;
    protected $object;

    public function __construct(&$object) {
      $this->object = &$object;
    }

    protected function canHandle() {
      return $this->object instanceof $this->objectClass;
    }

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
