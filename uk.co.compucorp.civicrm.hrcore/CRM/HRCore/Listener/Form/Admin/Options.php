<?php

class CRM_HRCore_Listener_Form_Admin_Options extends CRM_HRCore_Listener_AbstractListener {

  protected $objectClass = 'CRM_Admin_Form_Options';

  public function onBuildForm() {
    if (!$this->canHandle()) {
      return;
    }

    $this->object->removeElement('value');
  }
}
