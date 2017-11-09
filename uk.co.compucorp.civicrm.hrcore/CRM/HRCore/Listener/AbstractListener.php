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
  }
