<?php

class CRM_HRCore_HookListener_ObjectBased_ObjectBasedListener extends CRM_HRCore_HookListener_BaseListener {

  protected $objectClass;
  protected $object;

  public function __construct(&$object) {
    $this->object = &$object;
  }

  protected function canHandle() {
    return $this->object instanceof $this->objectClass;
  }
}
