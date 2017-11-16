<?php

class CRM_HRCore_HookListener_ObjectBased_Form_Admin_Options extends CRM_HRCore_HookListener_ObjectBased_ObjectBasedListener {

  protected $objectClass = 'CRM_Admin_Form_Options';

  public function onBuildForm() {
    if (!$this->canHandle()) {
      return;
    }

    $this->object->removeElement('value');
  }
}
